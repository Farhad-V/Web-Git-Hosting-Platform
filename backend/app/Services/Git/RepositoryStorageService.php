<?php

namespace App\Services\Git;

use App\Exceptions\RepositoryStorageException;
use App\Models\Repository;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
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
        $path = null;

        try {
            $repositoriesDirectory = $this->repositoriesDirectory();

            File::ensureDirectoryExists(
                $repositoriesDirectory,
                0750,
                true,
            );

            /*
             * Re-check containment after the directory exists so canonical
             * filesystem resolution can detect a symlink or junction that
             * escapes the configured storage root.
             */
            $this->assertContainedPath(
                $repositoriesDirectory,
            );

            $path = $this->resolveNewTarget(
                $repository,
            );

            $initialBranch = trim(
                (string) config(
                    'git.initial_branch',
                    'main',
                ),
            );

            if ($initialBranch === '') {
                $this->logResolutionFailure(
                    operation: 'initialize',
                    repositoryId: $repository->id,
                    category: 'invalid',
                );

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
            /*
             * Compensation is attempted only after a repository target was
             * successfully resolved. A root/containment failure must never
             * trigger deletion against an untrusted path.
             */
            if ($path !== null) {
                try {
                    $this->cleanupPartial(
                        $repository,
                    );
                } catch (Throwable $cleanupException) {
                    report($cleanupException);
                }
            }

            if ($exception instanceof RepositoryStorageException) {
                throw $exception;
            }

            $this->logResolutionFailure(
                operation: 'initialize',
                repositoryId: $repository->id,
                category: 'unavailable',
            );

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
        try {
            $path = $this->pathFor(
                $repository,
            );
        } catch (RepositoryStorageException) {
            return false;
        }

        if (is_link($path)) {
            $this->logUnsafePath(
                operation: 'exists',
                repositoryId: $repository->id,
            );

            return false;
        }

        if (! File::isDirectory($path)) {
            return false;
        }

        try {
            $this->assertContainedPath(
                $path,
            );
        } catch (RepositoryStorageException) {
            return false;
        }

        return true;
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
            $repositoryId = (int) ($repository->id ?? 0);

            $this->logResolutionFailure(
                operation: 'resolve-path',
                repositoryId: $repositoryId,
                category: 'invalid',
            );

            throw new RepositoryStorageException(
                operation: 'resolve-path',
                repositoryId: $repositoryId,
            );
        }

        $repositoryId = (int) $repository->id;

        if ($repositoryId < 1) {
            $this->logResolutionFailure(
                operation: 'resolve-path',
                repositoryId: $repositoryId,
                category: 'invalid',
            );

            throw new RepositoryStorageException(
                operation: 'resolve-path',
                repositoryId: $repositoryId,
            );
        }

        $path = $this->repositoriesDirectory()
            .DIRECTORY_SEPARATOR
            .$repositoryId
            .'.git';

        $this->assertContainedPath(
            $path,
            repositoryId: $repositoryId,
        );

        return $path;
    }

    /**
     * Resolve an existing managed repository.
     */
    public function resolveExisting(
        Repository $repository,
    ): string {
        $path = $this->pathFor(
            $repository,
        );

        if (is_link($path)) {
            $this->logUnsafePath(
                operation: 'resolve-existing',
                repositoryId: $repository->id,
            );

            throw new RepositoryStorageException(
                operation: 'resolve-existing',
                repositoryId: $repository->id,
            );
        }

        if (! File::isDirectory($path)) {
            $this->logMissingStorage(
                operation: 'resolve-existing',
                repositoryId: $repository->id,
            );

            throw new RepositoryStorageException(
                operation: 'resolve-existing',
                repositoryId: $repository->id,
            );
        }

        $this->assertContainedPath(
            $path,
            repositoryId: $repository->id,
        );

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
        $path = $this->pathFor(
            $repository,
        );

        if (
            File::exists($path)
            || is_link($path)
        ) {
            $this->logUnexpectedExistingTarget(
                repositoryId: $repository->id,
            );

            if (is_link($path)) {
                $this->logUnsafePath(
                    operation: 'resolve-new-target',
                    repositoryId: $repository->id,
                );
            }

            throw new RepositoryStorageException(
                operation: 'resolve-new-target',
                repositoryId: $repository->id,
            );
        }

        return $path;
    }

    /**
     * Compatibility alias retained while Git services are normalized.
     */
    public function repositoryPath(
        Repository $repository,
    ): string {
        return $this->pathFor(
            $repository,
        );
    }

    /**
     * Discover the current default branch from Git-native state.
     *
     * No default branch is persisted in application metadata.
     */
    public function defaultBranch(
        Repository $repository,
    ): ?string {
        $path = $this->resolveExisting(
            $repository,
        );

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
            $this->logResolutionFailure(
                operation: 'resolve-default-branch',
                repositoryId: $repository->id,
                category: 'inconsistent',
            );

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
            || ! hash_equals(
                $branch,
                $existingBranch,
            )
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
        $path = $this->pathFor(
            $repository,
        );

        if (is_link($path)) {
            $this->logUnsafePath(
                operation: 'cleanup-partial',
                repositoryId: $repository->id,
            );

            throw new RepositoryStorageException(
                operation: 'cleanup-partial',
                repositoryId: $repository->id,
            );
        }

        $this->assertContainedPath(
            $path,
            repositoryId: $repository->id,
        );

        if (! File::exists($path)) {
            return;
        }

        if (! File::isDirectory($path)) {
            $this->logResolutionFailure(
                operation: 'cleanup-partial',
                repositoryId: $repository->id,
                category: 'inconsistent',
            );

            throw new RepositoryStorageException(
                operation: 'cleanup-partial',
                repositoryId: $repository->id,
            );
        }

        if (! File::deleteDirectory($path)) {
            $this->logResolutionFailure(
                operation: 'cleanup-partial',
                repositoryId: $repository->id,
                category: 'unavailable',
            );

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
            $this->logResolutionFailure(
                operation: 'resolve-root',
                repositoryId: 0,
                category: 'invalid',
            );

            throw new RepositoryStorageException(
                operation: 'resolve-root',
                repositoryId: 0,
            );
        }

        $path = $this->storageRoot()
            .DIRECTORY_SEPARATOR
            .$directory;

        if (is_link($path)) {
            $this->logUnsafePath(
                operation: 'path-containment',
                repositoryId: 0,
            );

            throw new RepositoryStorageException(
                operation: 'path-containment',
                repositoryId: 0,
            );
        }

        $this->assertContainedPath(
            $path,
        );

        return $path;
    }

    /**
     * Return the configured repository-storage root.
     */
    private function storageRoot(): string
    {
        $root = trim(
            (string) config(
                'git.storage_root',
                storage_path('app/git'),
            ),
        );

        $root = rtrim(
            $root,
            '/\\',
        );

        if (
            $root === ''
            || ! $this->isAbsolutePath($root)
        ) {
            $this->logResolutionFailure(
                operation: 'resolve-root',
                repositoryId: 0,
                category: 'invalid',
            );

            throw new RepositoryStorageException(
                operation: 'resolve-root',
                repositoryId: 0,
            );
        }

        return $root;
    }

    /**
     * Verify that a path cannot escape the configured storage root.
     *
     * The lexical comparison protects non-existing targets. When the storage
     * root exists, the nearest existing candidate ancestor is also resolved
     * canonically so filesystem links or junctions cannot escape the root.
     */
    private function assertContainedPath(
        string $path,
        int $repositoryId = 0,
    ): void {
        $storageRoot = $this->storageRoot();

        $root = $this->normalizePath(
            $storageRoot,
        );

        $candidate = $this->normalizePath(
            $path,
        );

        if (
            $candidate === $root
            || ! str_starts_with(
                $candidate,
                $root.DIRECTORY_SEPARATOR,
            )
        ) {
            $this->logUnsafePath(
                operation: 'path-containment',
                repositoryId: $repositoryId,
            );

            throw new RepositoryStorageException(
                operation: 'path-containment',
                repositoryId: $repositoryId,
            );
        }

        $canonicalRoot = realpath(
            $storageRoot,
        );

        /*
         * A not-yet-created storage root can only be checked lexically.
         * initialize() re-runs this check after creating the repositories
         * directory, at which point canonical verification becomes possible.
         */
        if ($canonicalRoot === false) {
            return;
        }

        $existingAncestor = $path;

        while (
            ! File::exists($existingAncestor)
            && ! is_link($existingAncestor)
        ) {
            $parent = dirname(
                $existingAncestor,
            );

            if ($parent === $existingAncestor) {
                break;
            }

            $existingAncestor = $parent;
        }

        $canonicalAncestor = realpath(
            $existingAncestor,
        );

        if ($canonicalAncestor === false) {
            $this->logResolutionFailure(
                operation: 'path-containment',
                repositoryId: $repositoryId,
                category: 'unavailable',
            );

            throw new RepositoryStorageException(
                operation: 'path-containment',
                repositoryId: $repositoryId,
            );
        }

        $canonicalRoot = $this->normalizePath(
            $canonicalRoot,
        );

        $canonicalAncestor = $this->normalizePath(
            $canonicalAncestor,
        );

        if (
            $canonicalAncestor !== $canonicalRoot
            && ! str_starts_with(
                $canonicalAncestor,
                $canonicalRoot.DIRECTORY_SEPARATOR,
            )
        ) {
            $this->logUnsafePath(
                operation: 'path-containment',
                repositoryId: $repositoryId,
            );

            throw new RepositoryStorageException(
                operation: 'path-containment',
                repositoryId: $repositoryId,
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

        $normalized = rtrim(
            $normalized,
            DIRECTORY_SEPARATOR,
        );

        /*
         * Windows filesystem path comparison is ordinarily
         * case-insensitive.
         */
        if (PHP_OS_FAMILY === 'Windows') {
            $normalized = mb_strtolower(
                $normalized,
            );
        }

        return $normalized;
    }

    /**
     * Determine whether a configured path is absolute.
     */
    private function isAbsolutePath(
        string $path,
    ): bool {
        if (PHP_OS_FAMILY === 'Windows') {
            return preg_match(
                '/\A(?:[A-Za-z]:[\\\\\/]|\\\\\\\\)/',
                $path,
            ) === 1;
        }

        return str_starts_with(
            $path,
            '/',
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
            $this->logUnsafePath(
                operation: 'verify-repository',
                repositoryId: $repository->id,
            );

            throw new RepositoryStorageException(
                operation: 'verify-repository',
                repositoryId: $repository->id,
            );
        }

        $this->assertContainedPath(
            $path,
            repositoryId: $repository->id,
        );

        $requiredFiles = [
            $path.DIRECTORY_SEPARATOR.'HEAD',
            $path.DIRECTORY_SEPARATOR.'config',
        ];

        foreach ($requiredFiles as $requiredFile) {
            if (! File::isFile($requiredFile)) {
                $this->logResolutionFailure(
                    operation: 'verify-repository',
                    repositoryId: $repository->id,
                    category: 'invalid',
                );

                throw new RepositoryStorageException(
                    operation: 'verify-repository',
                    repositoryId: $repository->id,
                );
            }
        }

        $requiredDirectories = [
            $path.DIRECTORY_SEPARATOR.'objects',
            $path.DIRECTORY_SEPARATOR.'refs',
        ];

        foreach ($requiredDirectories as $requiredDirectory) {
            if (! File::isDirectory($requiredDirectory)) {
                $this->logResolutionFailure(
                    operation: 'verify-repository',
                    repositoryId: $repository->id,
                    category: 'invalid',
                );

                throw new RepositoryStorageException(
                    operation: 'verify-repository',
                    repositoryId: $repository->id,
                );
            }
        }

        $isBare = trim(
            $this->git->run(
                operation: 'verify-bare-repository',
                arguments: [
                    '--git-dir='.$path,
                    'rev-parse',
                    '--is-bare-repository',
                ],
            ),
        );

        if (! hash_equals('true', $isBare)) {
            $this->logResolutionFailure(
                operation: 'verify-repository',
                repositoryId: $repository->id,
                category: 'invalid',
            );

            throw new RepositoryStorageException(
                operation: 'verify-repository',
                repositoryId: $repository->id,
            );
        }
    }

    /**
     * Record a categorized repository-storage resolution failure.
     */
    private function logResolutionFailure(
        string $operation,
        int $repositoryId,
        string $category,
    ): void {
        Log::warning(
            'repository_storage_resolution_failed',
            [
                'event' => 'repository_storage_resolution_failed',
                'request_id' => $this->requestId(),
                'repository_id' => $repositoryId > 0
                    ? $repositoryId
                    : null,
                'operation' => $operation,
                'result' => 'failure',
                'failure_category' => $category,
            ],
        );
    }

    /**
     * Record missing physical storage for an existing repository.
     */
    private function logMissingStorage(
        string $operation,
        int $repositoryId,
    ): void {
        Log::error(
            'repository_storage_missing',
            [
                'event' => 'repository_storage_missing',
                'request_id' => $this->requestId(),
                'repository_id' => $repositoryId,
                'operation' => $operation,
                'result' => 'failure',
                'failure_category' => 'missing',
            ],
        );

        $this->logResolutionFailure(
            operation: $operation,
            repositoryId: $repositoryId,
            category: 'missing',
        );
    }

    /**
     * Record a security-relevant storage-containment failure.
     */
    private function logUnsafePath(
        string $operation,
        int $repositoryId,
    ): void {
        Log::error(
            'repository_storage_unsafe_path',
            [
                'event' => 'repository_storage_unsafe_path',
                'request_id' => $this->requestId(),
                'repository_id' => $repositoryId > 0
                    ? $repositoryId
                    : null,
                'operation' => $operation,
                'result' => 'failure',
                'failure_category' => 'unsafe',
            ],
        );

        $this->logResolutionFailure(
            operation: $operation,
            repositoryId: $repositoryId,
            category: 'unsafe',
        );
    }

    /**
     * Record an unexpected physical target during repository creation.
     */
    private function logUnexpectedExistingTarget(
        int $repositoryId,
    ): void {
        Log::error(
            'repository_storage_unexpected_existing_target',
            [
                'event' => 'repository_storage_unexpected_existing_target',
                'request_id' => $this->requestId(),
                'repository_id' => $repositoryId,
                'operation' => 'resolve-new-target',
                'result' => 'failure',
                'failure_category' => 'inconsistent',
            ],
        );

        $this->logResolutionFailure(
            operation: 'resolve-new-target',
            repositoryId: $repositoryId,
            category: 'inconsistent',
        );
    }

    /**
     * Return the current request correlation identifier when available.
     */
    private function requestId(): ?string
    {
        if (! app()->bound('request')) {
            return null;
        }

        $requestId = request()->attributes->get(
            'request_id',
        );

        return is_string($requestId)
            && $requestId !== ''
                ? $requestId
                : null;
    }
}