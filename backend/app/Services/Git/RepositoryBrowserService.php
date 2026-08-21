<?php

namespace App\Services\Git;

use App\Exceptions\GitCommandException;
use App\Exceptions\RepositoryBrowserException;
use App\Models\Repository;
use Throwable;

class RepositoryBrowserService
{
    private const DEFAULT_COMMIT_LIMIT = 50;

    private const MAX_COMMIT_LIMIT = 100;

    private const DEFAULT_MAX_BLOB_BYTES = 1048576;

    public function __construct(
        private readonly GitCommandRunner $git,
        private readonly RepositoryStorageService $storage,
    ) {}

    /**
     * List local branches in the managed repository.
     *
     * @return list<array{
     *     name: string,
     *     commit: string,
     *     committed_at: string|null,
     *     subject: string
     * }>
     */
    public function branches(
        Repository $repository,
    ): array {
        $path = $this->repositoryPath(
            $repository,
        );

        try {
            $output = $this->git->runRaw(
                operation: 'list-repository-branches',
                arguments: [
                    '--git-dir='.$path,
                    'for-each-ref',
                    '--sort=-committerdate',
                    '--format=%(refname:short)%09%(objectname)%09%(committerdate:iso-strict)%09%(subject)',
                    'refs/heads',
                ],
            );
        } catch (GitCommandException $exception) {
            throw $this->failure(
                repository: $repository,
                operation: 'list-branches',
                reason: 'git_command_failed',
                previous: $exception,
            );
        }

        $output = rtrim(
            $output,
            "\r\n",
        );

        if ($output === '') {
            return [];
        }

        $branches = [];

        foreach (
            preg_split('/\r?\n/', $output) ?: [] as $line
        ) {
            if ($line === '') {
                continue;
            }

            $fields = explode(
                "\t",
                $line,
                4,
            );

            if (count($fields) !== 4) {
                throw $this->failure(
                    repository: $repository,
                    operation: 'list-branches',
                    reason: 'unexpected_git_output',
                );
            }

            [
                $name,
                $commit,
                $committedAt,
                $subject,
            ] = $fields;

            if (
                $name === ''
                || ! preg_match(
                    '/\A[0-9a-f]{40,64}\z/',
                    $commit,
                )
            ) {
                throw $this->failure(
                    repository: $repository,
                    operation: 'list-branches',
                    reason: 'unexpected_git_output',
                );
            }

            $branches[] = [
                'name' => $name,
                'commit' => $commit,
                'committed_at' => $committedAt !== ''
                    ? $committedAt
                    : null,
                'subject' => $subject,
            ];
        }

        return $branches;
    }

    /**
     * List commits reachable from a repository branch.
     *
     * @return list<array{
     *     hash: string,
     *     parents: list<string>,
     *     author: array{name: string, email: string},
     *     authored_at: string,
     *     subject: string
     * }>
     */
    public function commits(
        Repository $repository,
        string $branch,
        int $limit = self::DEFAULT_COMMIT_LIMIT,
    ): array {
        $path = $this->repositoryPath(
            $repository,
        );

        $commit = $this->resolveBranchCommit(
            repository: $repository,
            path: $path,
            branch: $branch,
        );

        $limit = max(
            1,
            min(
                $limit,
                self::MAX_COMMIT_LIMIT,
            ),
        );

        try {
            $output = $this->git->runRaw(
                operation: 'list-repository-commits',
                arguments: [
                    '--git-dir='.$path,
                    'log',
                    '--max-count='.$limit,
                    '--date=iso-strict',
                    '--format=%H%x1f%P%x1f%an%x1f%ae%x1f%aI%x1f%s%x1e',
                    $commit,
                ],
            );
        } catch (GitCommandException $exception) {
            throw $this->failure(
                repository: $repository,
                operation: 'list-commits',
                reason: 'git_command_failed',
                previous: $exception,
            );
        }

        if ($output === '') {
            return [];
        }

        $commits = [];

        foreach (
            explode("\x1e", $output) as $record
        ) {
            $record = trim(
                $record,
                "\r\n",
            );

            if ($record === '') {
                continue;
            }

            $fields = explode(
                "\x1f",
                $record,
                6,
            );

            if (count($fields) !== 6) {
                throw $this->failure(
                    repository: $repository,
                    operation: 'list-commits',
                    reason: 'unexpected_git_output',
                );
            }

            [
                $hash,
                $parents,
                $authorName,
                $authorEmail,
                $authoredAt,
                $subject,
            ] = $fields;

            if (
                ! preg_match(
                    '/\A[0-9a-f]{40,64}\z/',
                    $hash,
                )
            ) {
                throw $this->failure(
                    repository: $repository,
                    operation: 'list-commits',
                    reason: 'unexpected_git_output',
                );
            }

            $parentHashes = $this->parseParentHashes(
                repository: $repository,
                operation: 'list-commits',
                parents: $parents,
            );

            if (
                $authorName === ''
                || $authorEmail === ''
                || $authoredAt === ''
            ) {
                throw $this->failure(
                    repository: $repository,
                    operation: 'list-commits',
                    reason: 'unexpected_git_output',
                );
            }

            $commits[] = [
                'hash' => $hash,
                'parents' => $parentHashes,
                'author' => [
                    'name' => $authorName,
                    'email' => $authorEmail,
                ],
                'authored_at' => $authoredAt,
                'subject' => $subject,
            ];
        }

        return $commits;
    }

    /**
     * Return one Git commit and its metadata.
     *
     * @return array{
     *     id: string,
     *     tree: string,
     *     parents: list<string>,
     *     author_name: string,
     *     author_email: string,
     *     authored_at: string,
     *     committer_name: string,
     *     committer_email: string,
     *     committed_at: string,
     *     message: string
     * }
     */
    public function commit(
        Repository $repository,
        string $commit,
    ): array {
        $path = $this->repositoryPath(
            $repository,
        );

        $commit = $this->normalizeCommitIdentifier(
            repository: $repository,
            commit: $commit,
        );

        /*
         * ^{commit} forces Git to resolve the supplied identifier specifically
         * to a commit object. An unknown identifier or a non-commit object
         * therefore fails before any commit metadata is read.
         */
        try {
            $resolvedCommit = $this->git->run(
                operation: 'resolve-repository-commit',
                arguments: [
                    '--git-dir='.$path,
                    'rev-parse',
                    '--verify',
                    $commit.'^{commit}',
                ],
            );
        } catch (GitCommandException $exception) {
            throw $this->failure(
                repository: $repository,
                operation: 'resolve-commit',
                reason: 'commit_not_found',
                previous: $exception,
            );
        }

        if (
            ! preg_match(
                '/\A[0-9a-f]{40,64}\z/',
                $resolvedCommit,
            )
        ) {
            throw $this->failure(
                repository: $repository,
                operation: 'resolve-commit',
                reason: 'unexpected_git_output',
            );
        }

        try {
            $output = $this->git->runRaw(
                operation: 'read-repository-commit',
                arguments: [
                    '--git-dir='.$path,
                    'show',
                    '--no-patch',
                    '--date=iso-strict',
                    '--format=%H%x1f%T%x1f%P%x1f%an%x1f%ae%x1f%aI%x1f%cn%x1f%ce%x1f%cI%x1f%B%x1e',
                    $resolvedCommit,
                ],
            );
        } catch (GitCommandException $exception) {
            throw $this->failure(
                repository: $repository,
                operation: 'read-commit',
                reason: 'git_command_failed',
                previous: $exception,
            );
        }

        $output = trim(
            $output,
            "\r\n",
        );

        if (
            $output === ''
            || ! str_ends_with(
                $output,
                "\x1e",
            )
        ) {
            throw $this->failure(
                repository: $repository,
                operation: 'read-commit',
                reason: 'unexpected_git_output',
            );
        }

        $record = substr(
            $output,
            0,
            -1,
        );

        $fields = explode(
            "\x1f",
            $record,
            10,
        );

        if (count($fields) !== 10) {
            throw $this->failure(
                repository: $repository,
                operation: 'read-commit',
                reason: 'unexpected_git_output',
            );
        }

        [
            $id,
            $tree,
            $parents,
            $authorName,
            $authorEmail,
            $authoredAt,
            $committerName,
            $committerEmail,
            $committedAt,
            $message,
        ] = $fields;

        if (
            ! preg_match(
                '/\A[0-9a-f]{40,64}\z/',
                $id,
            )
            || ! preg_match(
                '/\A[0-9a-f]{40,64}\z/',
                $tree,
            )
            || $authorName === ''
            || $authorEmail === ''
            || $authoredAt === ''
            || $committerName === ''
            || $committerEmail === ''
            || $committedAt === ''
        ) {
            throw $this->failure(
                repository: $repository,
                operation: 'read-commit',
                reason: 'unexpected_git_output',
            );
        }

        $parentHashes = $this->parseParentHashes(
            repository: $repository,
            operation: 'read-commit',
            parents: $parents,
        );

        /*
         * %B returns the complete commit message. Remove only Git's final
         * structural line break so the API does not expose formatter padding.
         */
        $message = rtrim(
            $message,
            "\r\n",
        );

        return [
            'id' => $id,
            'tree' => $tree,
            'parents' => $parentHashes,
            'author_name' => $authorName,
            'author_email' => $authorEmail,
            'authored_at' => $authoredAt,
            'committer_name' => $committerName,
            'committer_email' => $committerEmail,
            'committed_at' => $committedAt,
            'message' => $message,
        ];
    }

    /**
     * List the immediate entries in a repository directory.
     *
     * @return list<array{
     *     name: string,
     *     path: string,
     *     type: string,
     *     mode: string,
     *     object_id: string,
     *     size: int|null
     * }>
     */
    public function tree(
        Repository $repository,
        string $branch,
        string $directory = '',
    ): array {
        $path = $this->repositoryPath(
            $repository,
        );

        $commit = $this->resolveBranchCommit(
            repository: $repository,
            path: $path,
            branch: $branch,
        );

        $directory = $this->normalizeRepositoryPath(
            repository: $repository,
            operation: 'list-tree',
            path: $directory,
            allowEmpty: true,
        );

        $treeish = $directory === ''
            ? $commit
            : $commit.':'.$directory;

        try {
            $output = $this->git->runRaw(
                operation: 'list-repository-tree',
                arguments: [
                    '--git-dir='.$path,
                    'ls-tree',
                    '-z',
                    '-l',
                    $treeish,
                ],
            );
        } catch (GitCommandException $exception) {
            throw $this->failure(
                repository: $repository,
                operation: 'list-tree',
                reason: 'path_not_found',
                previous: $exception,
            );
        }

        if ($output === '') {
            if ($directory !== '') {
                throw $this->failure(
                    repository: $repository,
                    operation: 'list-tree',
                    reason: 'path_not_found',
                );
            }

            return [];
        }

        $entries = [];

        foreach (
            explode("\0", $output) as $record
        ) {
            if ($record === '') {
                continue;
            }

            if (
                preg_match(
                    '/\A([0-7]{6}) ([a-z]+) ([0-9a-f]{40,64}) +(-|\d+)\t(.*)\z/s',
                    $record,
                    $matches,
                ) !== 1
            ) {
                throw $this->failure(
                    repository: $repository,
                    operation: 'list-tree',
                    reason: 'unexpected_git_output',
                );
            }

            $gitType = $matches[2];

            $type = match ($gitType) {
                'tree' => 'directory',
                'blob' => 'file',
                'commit' => 'submodule',

                default => throw $this->failure(
                    repository: $repository,
                    operation: 'list-tree',
                    reason: 'unexpected_git_output',
                ),
            };

            $name = $matches[5];

            $entries[] = [
                'name' => $name,
                'path' => $directory === ''
                    ? $name
                    : $directory.'/'.$name,
                'type' => $type,
                'mode' => $matches[1],
                'object_id' => $matches[3],
                'size' => $matches[4] === '-'
                    ? null
                    : (int) $matches[4],
            ];
        }

        usort(
            $entries,
            static function (
                array $first,
                array $second,
            ): int {
                $order = [
                    'directory' => 0,
                    'file' => 1,
                    'submodule' => 2,
                ];

                $firstOrder = $order[
                    $first['type']
                ] ?? 99;

                $secondOrder = $order[
                    $second['type']
                ] ?? 99;

                if ($firstOrder !== $secondOrder) {
                    return $firstOrder <=> $secondOrder;
                }

                return strcasecmp(
                    $first['name'],
                    $second['name'],
                );
            },
        );

        return $entries;
    }

    /**
     * Read a UTF-8 text blob without altering its contents.
     *
     * @return array{
     *     path: string,
     *     size: int,
     *     content: string,
     *     encoding: string
     * }
     */
    public function blob(
        Repository $repository,
        string $branch,
        string $file,
    ): array {
        $path = $this->repositoryPath(
            $repository,
        );

        $commit = $this->resolveBranchCommit(
            repository: $repository,
            path: $path,
            branch: $branch,
        );

        $file = $this->normalizeRepositoryPath(
            repository: $repository,
            operation: 'read-blob',
            path: $file,
            allowEmpty: false,
        );

        $object = $commit.':'.$file;

        try {
            $type = $this->git->run(
                operation: 'resolve-repository-object-type',
                arguments: [
                    '--git-dir='.$path,
                    'cat-file',
                    '-t',
                    $object,
                ],
            );
        } catch (GitCommandException $exception) {
            throw $this->failure(
                repository: $repository,
                operation: 'read-blob',
                reason: 'path_not_found',
                previous: $exception,
            );
        }

        if ($type !== 'blob') {
            throw $this->failure(
                repository: $repository,
                operation: 'read-blob',
                reason: 'path_is_not_file',
            );
        }

        try {
            $sizeOutput = $this->git->run(
                operation: 'resolve-repository-blob-size',
                arguments: [
                    '--git-dir='.$path,
                    'cat-file',
                    '-s',
                    $object,
                ],
            );
        } catch (GitCommandException $exception) {
            throw $this->failure(
                repository: $repository,
                operation: 'read-blob',
                reason: 'git_command_failed',
                previous: $exception,
            );
        }

        if (
            ! preg_match(
                '/\A\d+\z/',
                $sizeOutput,
            )
        ) {
            throw $this->failure(
                repository: $repository,
                operation: 'read-blob',
                reason: 'unexpected_git_output',
            );
        }

        $size = (int) $sizeOutput;

        $maximumSize = (int) config(
            'git.browser_max_blob_bytes',
            self::DEFAULT_MAX_BLOB_BYTES,
        );

        if ($maximumSize < 1) {
            throw $this->failure(
                repository: $repository,
                operation: 'read-blob',
                reason: 'invalid_configuration',
            );
        }

        if ($size > $maximumSize) {
            throw $this->failure(
                repository: $repository,
                operation: 'read-blob',
                reason: 'blob_too_large',
            );
        }

        try {
            $content = $this->git->runRaw(
                operation: 'read-repository-blob',
                arguments: [
                    '--git-dir='.$path,
                    'cat-file',
                    'blob',
                    $object,
                ],
            );
        } catch (GitCommandException $exception) {
            throw $this->failure(
                repository: $repository,
                operation: 'read-blob',
                reason: 'git_command_failed',
                previous: $exception,
            );
        }

        if (strlen($content) !== $size) {
            throw $this->failure(
                repository: $repository,
                operation: 'read-blob',
                reason: 'unexpected_git_output',
            );
        }

        if (
            str_contains(
                $content,
                "\0",
            )
            || preg_match(
                '//u',
                $content,
            ) !== 1
        ) {
            throw $this->failure(
                repository: $repository,
                operation: 'read-blob',
                reason: 'binary_blob_not_supported',
            );
        }

        return [
            'path' => $file,
            'size' => $size,
            'content' => $content,
            'encoding' => 'utf-8',
        ];
    }

    /**
     * Return the managed repository path after verifying storage.
     */
    private function repositoryPath(
        Repository $repository,
    ): string {
        if (! $this->storage->exists($repository)) {
            throw $this->failure(
                repository: $repository,
                operation: 'resolve-storage',
                reason: 'storage_not_found',
            );
        }

        return $this->storage->resolveExisting(
            $repository,
        );
    }

    /**
     * Resolve a local branch to an immutable commit hash.
     */
    private function resolveBranchCommit(
        Repository $repository,
        string $path,
        string $branch,
    ): string {
        $branch = trim(
            $branch,
        );

        if (
            $branch === ''
            || strlen($branch) > 255
            || str_starts_with(
                $branch,
                '-',
            )
            || preg_match(
                '/[\x00-\x20\x7f]/',
                $branch,
            ) === 1
        ) {
            throw $this->failure(
                repository: $repository,
                operation: 'resolve-branch',
                reason: 'invalid_branch',
            );
        }

        try {
            $this->git->run(
                operation: 'validate-repository-branch',
                arguments: [
                    'check-ref-format',
                    '--branch',
                    $branch,
                ],
            );
        } catch (GitCommandException $exception) {
            throw $this->failure(
                repository: $repository,
                operation: 'resolve-branch',
                reason: 'invalid_branch',
                previous: $exception,
            );
        }

        try {
            $commit = $this->git->run(
                operation: 'resolve-repository-branch',
                arguments: [
                    '--git-dir='.$path,
                    'show-ref',
                    '--verify',
                    '--hash',
                    'refs/heads/'.$branch,
                ],
            );
        } catch (GitCommandException $exception) {
            throw $this->failure(
                repository: $repository,
                operation: 'resolve-branch',
                reason: 'branch_not_found',
                previous: $exception,
            );
        }

        if (
            ! preg_match(
                '/\A[0-9a-f]{40,64}\z/',
                $commit,
            )
        ) {
            throw $this->failure(
                repository: $repository,
                operation: 'resolve-branch',
                reason: 'unexpected_git_output',
            );
        }

        return $commit;
    }

    /**
     * Normalize an untrusted Git commit identifier.
     */
    private function normalizeCommitIdentifier(
        Repository $repository,
        string $commit,
    ): string {
        $commit = trim(
            $commit,
        );

        /*
         * V1 exposes immutable commit identifiers through the browser API.
         * Accept full SHA-1 or SHA-256 object IDs only. This avoids exposing
         * arbitrary revision-expression syntax through a client-controlled
         * route parameter.
         */
        if (
            ! preg_match(
                '/\A(?:[0-9a-fA-F]{40}|[0-9a-fA-F]{64})\z/',
                $commit,
            )
        ) {
            throw $this->failure(
                repository: $repository,
                operation: 'resolve-commit',
                reason: 'invalid_commit',
            );
        }

        return strtolower(
            $commit,
        );
    }

    /**
     * Parse and validate a whitespace-separated parent commit list.
     *
     * @return list<string>
     */
    private function parseParentHashes(
        Repository $repository,
        string $operation,
        string $parents,
    ): array {
        if ($parents === '') {
            return [];
        }

        $parentHashes = preg_split(
            '/\s+/',
            $parents,
        ) ?: [];

        foreach ($parentHashes as $parentHash) {
            if (
                ! preg_match(
                    '/\A[0-9a-f]{40,64}\z/',
                    $parentHash,
                )
            ) {
                throw $this->failure(
                    repository: $repository,
                    operation: $operation,
                    reason: 'unexpected_git_output',
                );
            }
        }

        return $parentHashes;
    }

    /**
     * Validate a path within a Git tree.
     */
    private function normalizeRepositoryPath(
        Repository $repository,
        string $operation,
        string $path,
        bool $allowEmpty,
    ): string {
        $path = trim(
            str_replace(
                '\\',
                '/',
                $path,
            ),
            '/',
        );

        if ($path === '') {
            if ($allowEmpty) {
                return '';
            }

            throw $this->failure(
                repository: $repository,
                operation: $operation,
                reason: 'path_required',
            );
        }

        if (
            strlen($path) > 4096
            || str_contains(
                $path,
                "\0",
            )
            || preg_match(
                '/[\x01-\x1f\x7f]/',
                $path,
            ) === 1
        ) {
            throw $this->failure(
                repository: $repository,
                operation: $operation,
                reason: 'invalid_path',
            );
        }

        $segments = explode(
            '/',
            $path,
        );

        foreach ($segments as $segment) {
            if (
                $segment === ''
                || $segment === '.'
                || $segment === '..'
            ) {
                throw $this->failure(
                    repository: $repository,
                    operation: $operation,
                    reason: 'invalid_path',
                );
            }
        }

        return implode(
            '/',
            $segments,
        );
    }

    /**
     * Build a consistent browsing exception.
     */
    private function failure(
        Repository $repository,
        string $operation,
        string $reason,
        ?Throwable $previous = null,
    ): RepositoryBrowserException {
        return new RepositoryBrowserException(
            operation: $operation,
            repositoryId: $repository->id,
            reason: $reason,
            previous: $previous,
        );
    }
}
