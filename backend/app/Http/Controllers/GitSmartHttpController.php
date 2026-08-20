<?php

namespace App\Http\Controllers;

use App\Enums\RepositoryVisibility;
use App\Exceptions\GitAuthenticationException;
use App\Exceptions\GitTransportException;
use App\Models\Repository;
use App\Models\User;
use App\Services\Git\GitCredentialAuthenticator;
use App\Services\Git\GitSmartHttpService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class GitSmartHttpController extends Controller
{
    public function __construct(
        private readonly GitSmartHttpService $git,
        private readonly GitCredentialAuthenticator $credentials,
    ) {}

    /**
     * Advertise references for clone, fetch, or push.
     */
    public function infoRefs(
        Request $request,
        string $username,
        string $repository,
    ): Response {
        $service = match ($request->query('service')) {
            'git-upload-pack' => 'upload-pack',
            'git-receive-pack' => 'receive-pack',

            default => abort(
                Response::HTTP_BAD_REQUEST,
                'Unsupported Git service.',
            ),
        };

        $model = $this->resolveRepository(
            username: $username,
            repositoryName: $repository,
        );

        if ($service === 'upload-pack') {
            $this->authorizeRead(
                request: $request,
                repository: $model,
            );
        } else {
            $this->authorizeWrite(
                request: $request,
                repository: $model,
            );
        }

        try {
            $body = $this->git->advertise(
                repository: $model,
                service: $service,
            );
        } catch (GitTransportException $exception) {
            return $this->transportErrorResponse(
                $exception,
            );
        }

        return response(
            content: $body,
            status: Response::HTTP_OK,
            headers: $this->gitHeaders(
                $this->git->advertisementContentType(
                    $service,
                ),
            ),
        );
    }

    /**
     * Process clone and fetch requests.
     */
    public function uploadPack(
        Request $request,
        string $username,
        string $repository,
    ): Response {
        $model = $this->resolveRepository(
            username: $username,
            repositoryName: $repository,
        );

        $this->authorizeRead(
            request: $request,
            repository: $model,
        );

        $this->validateContentType(
            request: $request,
            expected: $this->git->requestContentType(
                'upload-pack',
            ),
        );

        try {
            $body = $this->git->rpc(
                repository: $model,
                service: 'upload-pack',
                requestBody: $request->getContent(),
            );
        } catch (GitTransportException $exception) {
            return $this->transportErrorResponse(
                $exception,
            );
        }

        return response(
            content: $body,
            status: Response::HTTP_OK,
            headers: $this->gitHeaders(
                $this->git->resultContentType(
                    'upload-pack',
                ),
            ),
        );
    }

    /**
     * Process push requests.
     */
    public function receivePack(
        Request $request,
        string $username,
        string $repository,
    ): Response {
        $model = $this->resolveRepository(
            username: $username,
            repositoryName: $repository,
        );

        $this->authorizeWrite(
            request: $request,
            repository: $model,
        );

        $this->validateContentType(
            request: $request,
            expected: $this->git->requestContentType(
                'receive-pack',
            ),
        );

        try {
            $body = $this->git->rpc(
                repository: $model,
                service: 'receive-pack',
                requestBody: $request->getContent(),
            );
        } catch (GitTransportException $exception) {
            return $this->transportErrorResponse(
                $exception,
            );
        }

        return response(
            content: $body,
            status: Response::HTTP_OK,
            headers: $this->gitHeaders(
                $this->git->resultContentType(
                    'receive-pack',
                ),
            ),
        );
    }

    /**
     * Resolve repository metadata from the logical owner/name namespace.
     *
     * Logical namespace values are used only for database lookup.
     * Physical repository paths are derived later from the repository ID.
     */
    private function resolveRepository(
        string $username,
        string $repositoryName,
    ): Repository {
        $username = mb_strtolower(
            trim($username),
        );

        $repositoryName = mb_strtolower(
            trim($repositoryName),
        );

        return Repository::query()
            ->with('owner')
            ->whereHas(
                'owner',
                static fn ($query) => $query->where(
                    'username',
                    $username,
                ),
            )
            ->where(
                'name',
                $repositoryName,
            )
            ->firstOrFail();
    }

    /**
     * Authorize clone and fetch operations.
     */
    private function authorizeRead(
        Request $request,
        Repository $repository,
    ): void {
        if (
            $repository->visibility
            === RepositoryVisibility::PublicAccess
        ) {
            return;
        }

        $user = $this->authenticate(
            $request,
        );

        abort_unless(
            $repository->owner_id === $user->id,
            Response::HTTP_FORBIDDEN,
        );
    }

    /**
     * Authorize push operations.
     */
    private function authorizeWrite(
        Request $request,
        Repository $repository,
    ): void {
        $user = $this->authenticate(
            $request,
        );

        abort_unless(
            $repository->owner_id === $user->id,
            Response::HTTP_FORBIDDEN,
        );
    }

    /**
     * Authenticate Git Smart HTTP Basic credentials.
     */
    private function authenticate(
        Request $request,
    ): User {
        try {
            $user = $this->credentials->authenticate(
                $request,
            );
        } catch (GitAuthenticationException) {
            abort(
                Response::HTTP_UNAUTHORIZED,
                'Authentication required.',
                [
                    'WWW-Authenticate' => (
                        'Basic realm="Git Hosting Platform", charset="UTF-8"'
                    ),
                ],
            );
        }

        Auth::setUser(
            $user,
        );

        $request->setUserResolver(
            static fn (): User => $user,
        );

        return $user;
    }

    /**
     * Require the Git protocol request media type.
     */
    private function validateContentType(
        Request $request,
        string $expected,
    ): void {
        $actual = mb_strtolower(
            trim(
                explode(
                    ';',
                    (string) $request->header(
                        'Content-Type',
                    ),
                    2,
                )[0],
            ),
        );

        abort_unless(
            hash_equals(
                $expected,
                $actual,
            ),
            Response::HTTP_UNSUPPORTED_MEDIA_TYPE,
        );
    }

    /**
     * Convert controlled transport failures into safe HTTP responses.
     */
    private function transportErrorResponse(
        GitTransportException $exception,
    ): Response {
        $status = match ($exception->processExitCode) {
            Response::HTTP_REQUEST_ENTITY_TOO_LARGE => (
                Response::HTTP_REQUEST_ENTITY_TOO_LARGE
            ),

            124 => Response::HTTP_GATEWAY_TIMEOUT,

            default => Response::HTTP_INTERNAL_SERVER_ERROR,
        };

        report(
            $exception,
        );

        $message = match ($status) {
            Response::HTTP_REQUEST_ENTITY_TOO_LARGE => (
                'Git request body exceeds the configured limit.'
            ),

            Response::HTTP_GATEWAY_TIMEOUT => (
                'Git transport operation timed out.'
            ),

            default => (
                'Git transport operation failed.'
            ),
        };

        return response(
            content: $message,
            status: $status,
            headers: [
                'Content-Type' => 'text/plain; charset=UTF-8',
                'Cache-Control' => 'no-cache, max-age=0, must-revalidate',
                'Pragma' => 'no-cache',
                'Expires' => 'Fri, 01 Jan 1980 00:00:00 GMT',
                'X-Content-Type-Options' => 'nosniff',
            ],
        );
    }

    /**
     * Return standard non-cacheable Git protocol headers.
     *
     * @return array<string, string>
     */
    private function gitHeaders(
        string $contentType,
    ): array {
        return [
            'Content-Type' => $contentType,
            'Cache-Control' => 'no-cache, max-age=0, must-revalidate',
            'Pragma' => 'no-cache',
            'Expires' => 'Fri, 01 Jan 1980 00:00:00 GMT',
            'X-Content-Type-Options' => 'nosniff',
        ];
    }
}