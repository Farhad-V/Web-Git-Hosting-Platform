<?php

namespace App\Http\Controllers;

use App\Enums\RepositoryStatus;
use App\Enums\RepositoryVisibility;
use App\Exceptions\GitAuthenticationException;
use App\Models\Repository;
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
            slug: $repository,
        );

        if ($service === 'upload-pack') {
            $this->authorizeRead($request, $model);
        } else {
            $this->authorizeWrite($request, $model);
        }

        $body = $this->git->advertise(
            repository: $model,
            service: $service,
        );

        return response(
            content: $body,
            status: Response::HTTP_OK,
            headers: $this->gitHeaders(
                $this->git->advertisementContentType($service),
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
            slug: $repository,
        );

        $this->authorizeRead($request, $model);

        $this->validateContentType(
            request: $request,
            expected: $this->git->requestContentType(
                'upload-pack',
            ),
        );

        $body = $this->git->rpc(
            repository: $model,
            service: 'upload-pack',
            requestBody: $request->getContent(),
        );

        return response(
            content: $body,
            status: Response::HTTP_OK,
            headers: $this->gitHeaders(
                $this->git->resultContentType('upload-pack'),
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
            slug: $repository,
        );

        $this->authorizeWrite($request, $model);

        $this->validateContentType(
            request: $request,
            expected: $this->git->requestContentType(
                'receive-pack',
            ),
        );

        $body = $this->git->rpc(
            repository: $model,
            service: 'receive-pack',
            requestBody: $request->getContent(),
        );

        return response(
            content: $body,
            status: Response::HTTP_OK,
            headers: $this->gitHeaders(
                $this->git->resultContentType('receive-pack'),
            ),
        );
    }

    /**
     * Resolve an accessible repository by owner namespace and slug.
     */
    private function resolveRepository(
        string $username,
        string $slug,
    ): Repository {
        return Repository::query()
            ->with('owner')
            ->whereHas(
                'owner',
                fn ($query) => $query->where(
                    'username',
                    mb_strtolower($username),
                ),
            )
            ->where('slug', mb_strtolower($slug))
            ->whereIn('status', [
                RepositoryStatus::Active,
                RepositoryStatus::Archived,
            ])
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

        $user = $this->authenticate($request);

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
        $user = $request->user()
            ?? $this->authenticate($request);

        abort_unless(
            $repository->owner_id === $user->id,
            Response::HTTP_FORBIDDEN,
        );

        abort_unless(
            $repository->status === RepositoryStatus::Active,
            Response::HTTP_FORBIDDEN,
            'Archived repositories are read-only.',
        );
    }

    /**
     * Authenticate supplied Git Basic credentials.
     */
    private function authenticate(Request $request)
    {
        try {
            $user = $this->credentials->authenticate($request);
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

        Auth::setUser($user);

        $request->setUserResolver(
            static fn () => $user,
        );

        return $user;
    }

    /**
     * Require the exact Git protocol request content type.
     */
    private function validateContentType(
        Request $request,
        string $expected,
    ): void {
        $actual = mb_strtolower(
            trim(
                explode(
                    ';',
                    (string) $request->header('Content-Type'),
                    2,
                )[0],
            ),
        );

        abort_unless(
            hash_equals($expected, $actual),
            Response::HTTP_UNSUPPORTED_MEDIA_TYPE,
        );
    }

    /**
     * Return standard non-cacheable Git protocol headers.
     *
     * @return array<string, string>
     */
    private function gitHeaders(string $contentType): array
    {
        return [
            'Content-Type' => $contentType,
            'Cache-Control' => 'no-cache, max-age=0, must-revalidate',
            'Pragma' => 'no-cache',
            'Expires' => 'Fri, 01 Jan 1980 00:00:00 GMT',
            'X-Content-Type-Options' => 'nosniff',
        ];
    }
}
