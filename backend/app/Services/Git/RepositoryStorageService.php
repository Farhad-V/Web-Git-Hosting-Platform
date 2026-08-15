<?php

namespace App\Services\Git;

use App\Exceptions\RepositoryStorageException;
use App\Models\Repository;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Throwable;

class RepositoryStorageService
{
    public function __construct(
        private readonly GitCommandRunner $git,
    ) {}

    /**
     * Initialize a managed bare Git repository.
     */
    public function initialize(Repository $repository): string
    {
        $path = $this->repositoryPath($repository);

        if (File::exists($path) || is_link($path)) {
            throw new RepositoryStorageException(
                operation: 'initialize',
                repositoryId: $repository->id,
            );
        }

        try {
            File::ensureDirectoryExists(
                $this->repositoriesDirectory(),
                0750,
                true,
            );

            $initialBranch = (string) config(
                'git.initial_branch',
                'main',
            );

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
            $this->removePartialDirectory($path);

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
     * Determine whether managed storage exists.
     */
    public function exists(Repository $repository): bool
    {
        $path = $this->repositoryPath($repository);

        return File::isDirectory($path)
            && ! is_link($path);
    }

    /**
     * Discover the current default branch when its reference exists.
     *
     * An empty bare repository has a symbolic HEAD but no corresponding
     * branch reference, so null is returned until the first push.
     */
    public function defaultBranch(
        Repository $repository,
    ): ?string {
        if (! $this->exists($repository)) {
            throw new RepositoryStorageException(
                operation: 'resolve-default-branch',
                repositoryId: $repository->id,
            );
        }

        $path = $this->repositoryPath($repository);

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

        $this->git->run(
            operation: 'validate-default-branch',
            arguments: [
                'check-ref-format',
                '--branch',
                $branch,
            ],
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
     * Delete managed repository storage.
     */
    public function delete(Repository $repository): void
    {
        $path = $this->repositoryPath($repository);

        if (is_link($path)) {
            throw new RepositoryStorageException(
                operation: 'delete',
                repositoryId: $repository->id,
            );
        }

        if (! File::exists($path)) {
            return;
        }

        if (! File::isDirectory($path)) {
            throw new RepositoryStorageException(
                operation: 'delete',
                repositoryId: $repository->id,
            );
        }

        if (! File::deleteDirectory($path)) {
            throw new RepositoryStorageException(
                operation: 'delete',
                repositoryId: $repository->id,
            );
        }
    }

    /**
     * Return the absolute managed storage path.
     */
    public function repositoryPath(Repository $repository): string
    {
        if (! Str::isUuid($repository->storage_uuid)) {
            throw new RepositoryStorageException(
                operation: 'resolve-path',
                repositoryId: $repository->id,
            );
        }

        $relativePath = str_replace(
            '/',
            DIRECTORY_SEPARATOR,
            $repository->storageRelativePath(),
        );

        return $this->storageRoot()
            .DIRECTORY_SEPARATOR
            .$relativePath;
    }

    /**
     * Return the absolute repositories directory.
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

        return $this->storageRoot()
            .DIRECTORY_SEPARATOR
            .$directory;
    }

    /**
     * Return the configured managed storage root.
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
     * Verify that Git created a valid bare repository.
     */
    private function assertValidBareRepository(
        Repository $repository,
        string $path,
    ): void {
        $requiredPaths = [
            $path.DIRECTORY_SEPARATOR.'HEAD',
            $path.DIRECTORY_SEPARATOR.'config',
            $path.DIRECTORY_SEPARATOR.'objects',
            $path.DIRECTORY_SEPARATOR.'refs',
        ];

        foreach ($requiredPaths as $requiredPath) {
            if (! File::exists($requiredPath)) {
                throw new RepositoryStorageException(
                    operation: 'verify-initialization',
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

        if ($isBare !== 'true') {
            throw new RepositoryStorageException(
                operation: 'verify-initialization',
                repositoryId: $repository->id,
            );
        }
    }

    /**
     * Remove a partially initialized directory after failure.
     */
    private function removePartialDirectory(string $path): void
    {
        if (
            File::isDirectory($path)
            && ! is_link($path)
        ) {
            File::deleteDirectory($path);
        }
    }
}
