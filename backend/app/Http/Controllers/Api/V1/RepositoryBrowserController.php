<?php

namespace App\Http\Controllers\Api\V1;

use App\Exceptions\RepositoryBrowserException;
use App\Exceptions\RepositoryStorageException;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\BrowseRepositoryRequest;
use App\Models\Repository;
use App\Services\Git\RepositoryBrowserService;
use App\Services\Git\RepositoryStorageService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Gate;
use Symfony\Component\HttpFoundation\Response;

class RepositoryBrowserController extends Controller
{
    public function __construct(
        private readonly RepositoryBrowserService $browser,
        private readonly RepositoryStorageService $storage,
    ) {}

    /**
     * List repository branches.
     */
    public function branches(
        Repository $repository,
    ): JsonResponse {
        Gate::authorize(
            'view',
            $repository,
        );

        try {
            return response()->json([
                'data' => $this->browser->branches(
                    $repository,
                ),
                'default_branch' => $this->storage->defaultBranch(
                    $repository,
                ),
            ]);
        } catch (RepositoryBrowserException $exception) {
            return $this->browserErrorResponse(
                $exception,
            );
        } catch (RepositoryStorageException $exception) {
            return $this->storageErrorResponse(
                $exception,
            );
        }
    }

    /**
     * List commits reachable from a branch.
     */
    public function commits(
        BrowseRepositoryRequest $request,
        Repository $repository,
    ): JsonResponse {
        Gate::authorize(
            'view',
            $repository,
        );

        $validated = $request->validated();

        try {
            $branch = $this->resolveBranch(
                repository: $repository,
                requestedBranch: $validated['branch'] ?? null,
            );

            if ($branch === null) {
                return $this->emptyRepositoryResponse();
            }

            return response()->json([
                'data' => $this->browser->commits(
                    repository: $repository,
                    branch: $branch,
                    limit: $validated['limit'] ?? 50,
                ),
                'branch' => $branch,
            ]);
        } catch (RepositoryBrowserException $exception) {
            return $this->browserErrorResponse(
                $exception,
            );
        } catch (RepositoryStorageException $exception) {
            return $this->storageErrorResponse(
                $exception,
            );
        }
    }

    /**
     * Return one repository commit and its Git-native metadata.
     */
    public function commit(
        Repository $repository,
        string $commit,
    ): JsonResponse {
        Gate::authorize(
            'view',
            $repository,
        );

        try {
            return response()->json([
                'commit' => $this->browser->commit(
                    repository: $repository,
                    commit: $commit,
                ),
            ]);
        } catch (RepositoryBrowserException $exception) {
            return $this->browserErrorResponse(
                $exception,
            );
        } catch (RepositoryStorageException $exception) {
            return $this->storageErrorResponse(
                $exception,
            );
        }
    }

    /**
     * List immediate entries from a repository directory.
     */
    public function tree(
        BrowseRepositoryRequest $request,
        Repository $repository,
    ): JsonResponse {
        Gate::authorize(
            'view',
            $repository,
        );

        $validated = $request->validated();

        try {
            $branch = $this->resolveBranch(
                repository: $repository,
                requestedBranch: $validated['branch'] ?? null,
            );

            if ($branch === null) {
                return $this->emptyRepositoryResponse();
            }

            $directory = $validated['path'] ?? '';

            return response()->json([
                'data' => $this->browser->tree(
                    repository: $repository,
                    branch: $branch,
                    directory: $directory,
                ),
                'branch' => $branch,
                'path' => $directory,
            ]);
        } catch (RepositoryBrowserException $exception) {
            return $this->browserErrorResponse(
                $exception,
            );
        } catch (RepositoryStorageException $exception) {
            return $this->storageErrorResponse(
                $exception,
            );
        }
    }

    /**
     * Read a UTF-8 repository file.
     */
    public function blob(
        BrowseRepositoryRequest $request,
        Repository $repository,
    ): JsonResponse {
        Gate::authorize(
            'view',
            $repository,
        );

        $validated = $request->validated();

        try {
            $branch = $this->resolveBranch(
                repository: $repository,
                requestedBranch: $validated['branch'] ?? null,
            );

            if ($branch === null) {
                return $this->emptyRepositoryResponse();
            }

            $file = $validated['path'] ?? '';

            return response()->json([
                'data' => $this->browser->blob(
                    repository: $repository,
                    branch: $branch,
                    file: $file,
                ),
                'branch' => $branch,
            ]);
        } catch (RepositoryBrowserException $exception) {
            return $this->browserErrorResponse(
                $exception,
            );
        } catch (RepositoryStorageException $exception) {
            return $this->storageErrorResponse(
                $exception,
            );
        }
    }

    /**
     * Resolve an explicitly requested branch or Git-native default branch.
     */
    public function pdf(BrowseRepositoryRequest $request, Repository $repository): Response
    {
        Gate::authorize('view', $repository);
        $validated = $request->validated();
        try {
            $branch = $this->resolveBranch($repository, $validated['branch'] ?? null);
            if ($branch === null) {
                return $this->emptyRepositoryResponse();
            }
            $file = $validated['path'] ?? '';
            $temporary = $this->browser->pdf($repository, $branch, $file);
            try {
                return response()->file($temporary, [
                    'Content-Type' => 'application/pdf',
                    'Cache-Control' => 'private, no-store',
                    'X-Content-Type-Options' => 'nosniff',
                ])->setContentDisposition('inline', basename($file), 'document.pdf')->deleteFileAfterSend(true);
            } catch (\Throwable $exception) {
                unlink($temporary);
                throw $exception;
            }
        } catch (RepositoryBrowserException $exception) {
            return $this->browserErrorResponse($exception);
        } catch (RepositoryStorageException $exception) {
            return $this->storageErrorResponse($exception);
        }
    }

    private function resolveBranch(
        Repository $repository,
        ?string $requestedBranch,
    ): ?string {
        if (
            is_string($requestedBranch)
            && trim($requestedBranch) !== ''
        ) {
            return $requestedBranch;
        }

        return $this->storage->defaultBranch(
            $repository,
        );
    }

    /**
     * Return a conflict response for a repository without commits.
     */
    private function emptyRepositoryResponse(): JsonResponse
    {
        return response()->json([
            'message' => 'The repository is empty.',
            'error' => 'empty_repository',
        ], Response::HTTP_CONFLICT);
    }

    /**
     * Convert controlled browser failures into safe API responses.
     */
    private function browserErrorResponse(
        RepositoryBrowserException $exception,
    ): JsonResponse {
        $status = match ($exception->reason) {
            'branch_not_found',
            'path_not_found',
            'commit_not_found' => Response::HTTP_NOT_FOUND,

            'invalid_branch',
            'invalid_path',
            'path_required',
            'path_is_not_file',
            'invalid_commit' => Response::HTTP_UNPROCESSABLE_ENTITY,

            'blob_too_large' => Response::HTTP_REQUEST_ENTITY_TOO_LARGE,

            'binary_blob_not_supported' => (
                Response::HTTP_UNSUPPORTED_MEDIA_TYPE
            ),

            default => Response::HTTP_INTERNAL_SERVER_ERROR,
        };

        if (
            $status
            === Response::HTTP_INTERNAL_SERVER_ERROR
        ) {
            report($exception);
        }

        $message = match ($exception->reason) {
            'branch_not_found' => (
                'The requested branch was not found.'
            ),

            'path_not_found' => (
                'The requested repository path was not found.'
            ),

            'commit_not_found' => (
                'The requested commit was not found.'
            ),

            'invalid_branch' => (
                'The requested branch is invalid.'
            ),

            'invalid_path' => (
                'The requested repository path is invalid.'
            ),

            'invalid_commit' => (
                'The requested commit identifier is invalid.'
            ),

            'path_required' => (
                'A repository file path is required.'
            ),

            'path_is_not_file' => (
                'The requested path is not a file.'
            ),

            'blob_too_large' => (
                'The requested file is too large to display.'
            ),

            'binary_blob_not_supported' => (
                'Binary files cannot be displayed.'
            ),

            default => (
                'The repository could not be browsed.'
            ),
        };

        return response()->json([
            'message' => $message,
            'error' => $exception->reason,
        ], $status);
    }

    /**
     * Convert repository-storage inconsistency into a safe response.
     */
    private function storageErrorResponse(
        RepositoryStorageException $exception,
    ): JsonResponse {
        report($exception);

        return response()->json([
            'message' => 'Repository storage is unavailable.',
            'error' => 'repository_storage_unavailable',
        ], Response::HTTP_INTERNAL_SERVER_ERROR);
    }
}
