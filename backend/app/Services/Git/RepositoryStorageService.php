<?php

namespace App\Services\Git;

use App\Exceptions\RepositoryStorageException;
use App\Models\Repository;
use Illuminate\Support\Facades\File;
use Throwable;

class RepositoryStorageService
{
    public function __construct(
        private readonly GitCommandRunner $git,
    ) {}

    /**
     * Initialize a managed bare Git repository.
     */
    public function initialize(
        Repository $repository,
    ): string {
        $path = $this->resolveNewTarget($repository);

        try {
            File::ensureDirectoryExists(
                $this->repositoriesDirectory(),
                0750,
                true,
            );

            $initialBranch = trim(
                (string) config(
                    'git.initial_branch',
                    'main',
                ),
            );

            if ($initialBranch === '') {
                throw new RepositoryStorageException(
                    operation: 'initialize',
                    repositoryId: $repository->id,
                );
            }

            $this->git->run(
                operation: 'validate-initial-branch',
                arguments: [
                    'check-ref-format',
                    '--branch',
                    $initialBranch,
                ],
            );

            $this->git->run(
                operation: 'initialize-bare-repository',
                arguments: [
                    'init',
                    '--bare',
                    "--initial-branch={$initialBranch}",
                    '--',
                    $path,
                ],
            );

            $this->assertValidBareRepository(
                repository: $repository,
                path: $path,
            );

            return $path;
        } catch (Throwable $exception) {
            $this->cleanupPartial($repository);

            if ($exception instanceof RepositoryStorageException) {
                throw $exception;
            }

            throw new RepositoryStorageException(
                operation: 'initialize',
                repositoryId: $repository->id,
                previous: $exception,
            );
        }
    }

    /**
     * Determine whether managed repository storage exists.
     */
    public function exists(
        Repository $repository,
    ): bool {
        $path = $this->pathFor($repository);

        return File::isDirectory($path)
            && ! is_link($path);
    }

    /**
     * Return the deterministic physical repository path.
     */
    public function pathFor(
        Repository $repository,
    ): string {
        if (
            ! is_int($repository->id)
            && ! ctype_digit((string) $repository->id)
        ) {
            throw new RepositoryStorageException(
                operation: 'resolve-path',
                repositoryId: (int) ($repository->id ?? 0),
            );
        }

        $repositoryId = (int) $repository->id;

        if ($repositoryId < 1) {
            throw new RepositoryStorageException(
                operation: 'resolve-path',
                repositoryId: $repositoryId,
            );
        }

        $path = $this->repositoriesDirectory()
            .DIRECTORY_SEPARATOR
            .$repositoryId
            .'.git';

        $this->assertContainedPath($path);

        return $path;
    }

    /**
     * Resolve an existing managed repository.
     */
    public function resolveExisting(
        Repository $repository,
    ): string {
        $path = $this->pathFor($repository);

        if (
            ! File::isDirectory($path)
            || is_link($path)
        ) {
            throw new RepositoryStorageException(
                operation: 'resolve-existing',
                repositoryId: $repository->id,
            );
        }

        $this->assertValidBareRepository(
            repository: $repository,
            path: $path,
        );

        return $path;
    }

    /**
     * Resolve a target for a repository that does not yet exist.
     */
    public function resolveNewTarget(
        Repository $repository,
    ): string {
        $path = $this->pathFor($repository);

        if (
            File::exists($path)
            || is_link($path)
        ) {
            throw new RepositoryStorageException(
                operation: 'resolve-new-target',
                repositoryId: $repository->id,
            );
        }

        return $path;
    }

    /**
     * Compatibility alias for code being normalized during M4.
     */
    public function repositoryPath(
        Repository $repository,
    ): string {
        return $this->pathFor($repository);
    }

    /**
     * Discover the current default branch from Git-native state.
     *
     * No default branch is persisted in application metadata.
     */
    public function defaultBranch(
        Repository $repository,
    ): ?string {
        $path = $this->resolveExisting($repository);

        $headReference = trim(
            $this->git->run(
                operation: 'resolve-head-reference',
                arguments: [
                    '--git-dir='.$path,
                    'symbolic-ref',
                    '--quiet',
                    'HEAD',
                ],
            ),
        );

        $prefix = 'refs/heads/';

        if (! str_starts_with($headReference, $prefix)) {
            throw new RepositoryStorageException(
                operation: 'resolve-default-branch',
                repositoryId: $repository->id,
            );
        }

        $branch = substr(
            $headReference,
            strlen($prefix),
        );

        $existingBranch = trim(
            $this->git->run(
                operation: 'find-default-branch-reference',
                arguments: [
                    '--git-dir='.$path,
                    'for-each-ref',
                    '--format=%(refname:short)',
                    '--count=1',
                    $headReference,
                ],
            ),
        );

        if (
            $existingBranch === ''
            || ! hash_equals($branch, $existingBranch)
        ) {
            return null;
        }

        return $branch;
    }

    /**
     * Remove partial storage created by a failed creation workflow.
     *
     * This method is compensation for creation failure, not repository
     * deletion functionality.
     */
    public function cleanupPartial(
        Repository $repository,
    ): void {
        $path = $this->pathFor($repository);

        if (is_link($path)) {
            throw new RepositoryStorageException(
                operation: 'cleanup-partial',
                repositoryId: $repository->id,
            );
        }

        if (! File::exists($path)) {
            return;
        }

        if (! File::isDirectory($path)) {
            throw new RepositoryStorageException(
                operation: 'cleanup-partial',
                repositoryId: $repository->id,
            );
        }

        if (! File::deleteDirectory($path)) {
            throw new RepositoryStorageException(
                operation: 'cleanup-partial',
                repositoryId: $repository->id,
            );
        }
    }

    /**
     * Return the configured repositories directory.
     */
    private function repositoriesDirectory(): string
    {
        $directory = trim(
            (string) config(
                'git.repositories_directory',
                'repositories',
            ),
            '/\\',
        );

        if (
            $directory === ''
            || str_contains($directory, '..')
            || str_contains($directory, '/')
            || str_contains($directory, '\\')
        ) {
            throw new RepositoryStorageException(
                operation: 'resolve-root',
                repositoryId: 0,
            );
        }

        $path = $this->storageRoot()
            .DIRECTORY_SEPARATOR
            .$directory;

        $this->assertContainedPath($path);

        return $path;
    }

    /**
     * Return the configured repository-storage root.
     */
    private function storageRoot(): string
    {
        $root = rtrim(
            (string) config(
                'git.storage_root',
                storage_path('app/git'),
            ),
            '/\\',
        );

        if ($root === '') {
            throw new RepositoryStorageException(
                operation: 'resolve-root',
                repositoryId: 0,
            );
        }

        return $root;
    }

    /**
     * Verify that a path cannot escape the configured storage root.
     */
    private function assertContainedPath(
        string $path,
    ): void {
        $root = $this->normalizePath(
            $this->storageRoot(),
        );

        $candidate = $this->normalizePath($path);

        if (
            $candidate === $root
            || ! str_starts_with(
                $candidate,
                $root.DIRECTORY_SEPARATOR,
            )
        ) {
            throw new RepositoryStorageException(
                operation: 'path-containment',
                repositoryId: 0,
            );
        }
    }

    /**
     * Normalize a path for containment comparison.
     */
    private function normalizePath(
        string $path,
    ): string {
        $normalized = str_replace(
            ['/', '\\'],
            DIRECTORY_SEPARATOR,
            $path,
        );

        return rtrim(
            $normalized,
            DIRECTORY_SEPARATOR,
        );
    }

    /**
     * Verify that Git storage is a valid bare repository.
     */
    private function assertValidBareRepository(
        Repository $repository,
        string $path,
    ): void {
        if (is_link($path)) {
            throw new RepositoryStorageException(
                operation: 'verify-repository',
                repositoryId: $repository->id,
            );
        }

        $requiredPaths = [
            $path.DIRECTORY_SEPARATOR.'HEAD',
            $path.DIRECTORY_SEPARATOR.'config',
            $path.DIRECTORY_SEPARATOR.'objects',
            $path.DIRECTORY_SEPARATOR.'refs',
        ];

        foreach ($requiredPaths as $requiredPath) {
            if (! File::exists($requiredPath)) {
                throw new RepositoryStorageException(
                    operation: 'verify-repository',
                    repositoryId: $repository->id,
                );
            }
        }

        $isBare = $this->git->run(
            operation: 'verify-bare-repository',
            arguments: [
                '--git-dir='.$path,
                'rev-parse',
                '--is-bare-repository',
            ],
        );

        if (! hash_equals('true', $isBare)) {
            throw new RepositoryStorageException(
                operation: 'verify-repository',
                repositoryId: $repository->id,
            );
        }
    }
}