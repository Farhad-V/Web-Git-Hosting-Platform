<?php

namespace Tests\Unit\Services\Git;

use App\Exceptions\RepositoryBrowserException;
use App\Exceptions\RepositoryStorageException;
use App\Models\Repository;
use App\Services\Git\GitCommandRunner;
use App\Services\Git\RepositoryBrowserService;
use App\Services\Git\RepositoryStorageService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Tests\TestCase;

class RepositoryBrowserServiceTest extends TestCase
{
    use RefreshDatabase;

    private string $temporaryRoot;

    private string $storageRoot;

    private string $worktreePath;

    protected function setUp(): void
    {
        parent::setUp();

        $this->temporaryRoot = sys_get_temp_dir()
            .DIRECTORY_SEPARATOR
            .'gitforge-browser-service-tests-'
            .Str::uuid();

        $this->storageRoot = $this->temporaryRoot
            .DIRECTORY_SEPARATOR
            .'storage';

        $this->worktreePath = $this->temporaryRoot
            .DIRECTORY_SEPARATOR
            .'worktree';

        config([
            'git.binary' => 'git',
            'git.timeout_seconds' => 10,
            'git.storage_root' => $this->storageRoot,
            'git.repositories_directory' => 'repositories',
            'git.initial_branch' => 'main',
            'git.browser_max_blob_bytes' => 1048576,
        ]);
    }

    protected function tearDown(): void
    {
        if (
            isset($this->temporaryRoot)
            && File::isDirectory($this->temporaryRoot)
        ) {
            File::deleteDirectory(
                $this->temporaryRoot,
            );
        }

        parent::tearDown();
    }

    public function test_empty_repository_has_no_branches(): void
    {
        $repository = Repository::factory()->create();

        app(RepositoryStorageService::class)
            ->initialize($repository);

        $branches = app(RepositoryBrowserService::class)
            ->branches($repository);

        $this->assertSame(
            [],
            $branches,
        );
    }

    public function test_it_lists_repository_branches(): void
    {
        $repository = $this->createRepositoryWithHistory();

        $branches = app(RepositoryBrowserService::class)
            ->branches($repository);

        $this->assertCount(
            1,
            $branches,
        );

        $this->assertSame(
            'main',
            $branches[0]['name'],
        );

        $this->assertMatchesRegularExpression(
            '/\A[0-9a-f]{40,64}\z/',
            $branches[0]['commit'],
        );

        $this->assertSame(
            'Add application source',
            $branches[0]['subject'],
        );

        $this->assertNotNull(
            $branches[0]['committed_at'],
        );
    }

    public function test_it_lists_repository_commits(): void
    {
        $repository = $this->createRepositoryWithHistory();

        $commits = app(RepositoryBrowserService::class)
            ->commits(
                repository: $repository,
                branch: 'main',
            );

        $this->assertCount(
            2,
            $commits,
        );

        $this->assertSame(
            'Add application source',
            $commits[0]['subject'],
        );

        $this->assertSame(
            'Initial commit',
            $commits[1]['subject'],
        );

        $this->assertSame(
            'Repository Browser Test',
            $commits[0]['author']['name'],
        );

        $this->assertSame(
            'browser@example.test',
            $commits[0]['author']['email'],
        );

        $this->assertCount(
            1,
            $commits[0]['parents'],
        );

        $this->assertSame(
            [],
            $commits[1]['parents'],
        );
    }

    public function test_it_limits_repository_commits(): void
    {
        $repository = $this->createRepositoryWithHistory();

        $commits = app(RepositoryBrowserService::class)
            ->commits(
                repository: $repository,
                branch: 'main',
                limit: 1,
            );

        $this->assertCount(
            1,
            $commits,
        );

        $this->assertSame(
            'Add application source',
            $commits[0]['subject'],
        );
    }

    public function test_it_returns_commit_details(): void
    {
        $repository = $this->createRepositoryWithHistory();

        $commits = app(RepositoryBrowserService::class)
            ->commits(
                repository: $repository,
                branch: 'main',
                limit: 1,
            );

        $commitHash = $commits[0]['hash'];

        $commit = app(RepositoryBrowserService::class)
            ->commit(
                repository: $repository,
                commit: $commitHash,
            );

        $this->assertSame(
            $commitHash,
            $commit['id'],
        );

        $this->assertMatchesRegularExpression(
            '/\A[0-9a-f]{40,64}\z/',
            $commit['tree'],
        );

        $this->assertCount(
            1,
            $commit['parents'],
        );

        $this->assertSame(
            'Repository Browser Test',
            $commit['author_name'],
        );

        $this->assertSame(
            'browser@example.test',
            $commit['author_email'],
        );

        $this->assertNotSame(
            '',
            $commit['authored_at'],
        );

        $this->assertSame(
            'Repository Browser Test',
            $commit['committer_name'],
        );

        $this->assertSame(
            'browser@example.test',
            $commit['committer_email'],
        );

        $this->assertNotSame(
            '',
            $commit['committed_at'],
        );

        $this->assertSame(
            'Add application source',
            $commit['message'],
        );
    }

    public function test_it_returns_root_commit_details_without_parents(): void
    {
        $repository = $this->createRepositoryWithHistory();

        $commits = app(RepositoryBrowserService::class)
            ->commits(
                repository: $repository,
                branch: 'main',
            );

        $rootCommitHash = $commits[1]['hash'];

        $commit = app(RepositoryBrowserService::class)
            ->commit(
                repository: $repository,
                commit: $rootCommitHash,
            );

        $this->assertSame(
            $rootCommitHash,
            $commit['id'],
        );

        $this->assertSame(
            [],
            $commit['parents'],
        );

        $this->assertSame(
            'Initial commit',
            $commit['message'],
        );
    }

    public function test_it_rejects_invalid_commit_identifier(): void
    {
        $repository = $this->createRepositoryWithHistory();

        try {
            app(RepositoryBrowserService::class)
                ->commit(
                    repository: $repository,
                    commit: '../main',
                );

            $this->fail(
                'An invalid commit identifier was accepted.',
            );
        } catch (RepositoryBrowserException $exception) {
            $this->assertSame(
                'invalid_commit',
                $exception->reason,
            );

            $this->assertSame(
                'resolve-commit',
                $exception->operation,
            );

            $this->assertSame(
                $repository->id,
                $exception->repositoryId,
            );
        }
    }

    public function test_it_rejects_unknown_commit_identifier(): void
    {
        $repository = $this->createRepositoryWithHistory();

        try {
            app(RepositoryBrowserService::class)
                ->commit(
                    repository: $repository,
                    commit: str_repeat(
                        'f',
                        40,
                    ),
                );

            $this->fail(
                'An unknown commit identifier was accepted.',
            );
        } catch (RepositoryBrowserException $exception) {
            $this->assertSame(
                'commit_not_found',
                $exception->reason,
            );

            $this->assertSame(
                'resolve-commit',
                $exception->operation,
            );

            $this->assertSame(
                $repository->id,
                $exception->repositoryId,
            );
        }
    }

    public function test_it_rejects_non_commit_git_object(): void
    {
        $repository = $this->createRepositoryWithHistory();

        $runner = app(
            GitCommandRunner::class,
        );

        $blobHash = $runner->run(
            operation: 'resolve-browser-test-blob',
            arguments: [
                'rev-parse',
                'HEAD:README.md',
            ],
            workingDirectory: $this->worktreePath,
        );

        try {
            app(RepositoryBrowserService::class)
                ->commit(
                    repository: $repository,
                    commit: $blobHash,
                );

            $this->fail(
                'A non-commit Git object was accepted as a commit.',
            );
        } catch (RepositoryBrowserException $exception) {
            $this->assertSame(
                'commit_not_found',
                $exception->reason,
            );

            $this->assertSame(
                'resolve-commit',
                $exception->operation,
            );
        }
    }

    public function test_it_lists_root_and_nested_tree_entries(): void
    {
        $repository = $this->createRepositoryWithHistory();

        $browser = app(
            RepositoryBrowserService::class,
        );

        $rootEntries = $browser->tree(
            repository: $repository,
            branch: 'main',
        );

        $this->assertCount(
            2,
            $rootEntries,
        );

        $this->assertSame(
            'directory',
            $rootEntries[0]['type'],
        );

        $this->assertSame(
            'src',
            $rootEntries[0]['name'],
        );

        $this->assertSame(
            'src',
            $rootEntries[0]['path'],
        );

        $this->assertNull(
            $rootEntries[0]['size'],
        );

        $this->assertSame(
            'file',
            $rootEntries[1]['type'],
        );

        $this->assertSame(
            'README.md',
            $rootEntries[1]['name'],
        );

        $nestedEntries = $browser->tree(
            repository: $repository,
            branch: 'main',
            directory: 'src',
        );

        $this->assertCount(
            1,
            $nestedEntries,
        );

        $this->assertSame(
            'app.php',
            $nestedEntries[0]['name'],
        );

        $this->assertSame(
            'src/app.php',
            $nestedEntries[0]['path'],
        );

        $this->assertSame(
            'file',
            $nestedEntries[0]['type'],
        );
    }

    public function test_it_reads_blob_without_trimming_content(): void
    {
        $repository = $this->createRepositoryWithHistory();

        $blob = app(RepositoryBrowserService::class)
            ->blob(
                repository: $repository,
                branch: 'main',
                file: 'README.md',
            );

        $this->assertSame(
            'README.md',
            $blob['path'],
        );

        $this->assertSame(
            "  # Repository Browser\n\nPreserve whitespace.  \n",
            $blob['content'],
        );

        $this->assertSame(
            strlen(
                $blob['content'],
            ),
            $blob['size'],
        );

        $this->assertSame(
            'utf-8',
            $blob['encoding'],
        );
    }

    public function test_it_rejects_unknown_branch(): void
    {
        $repository = $this->createRepositoryWithHistory();

        try {
            app(RepositoryBrowserService::class)
                ->commits(
                    repository: $repository,
                    branch: 'missing',
                );

            $this->fail(
                'An unknown branch did not fail.',
            );
        } catch (RepositoryBrowserException $exception) {
            $this->assertSame(
                'branch_not_found',
                $exception->reason,
            );

            $this->assertSame(
                $repository->id,
                $exception->repositoryId,
            );
        }
    }

    public function test_it_rejects_invalid_branch(): void
    {
        $repository = $this->createRepositoryWithHistory();

        try {
            app(RepositoryBrowserService::class)
                ->commits(
                    repository: $repository,
                    branch: '../main',
                );

            $this->fail(
                'An invalid branch was accepted.',
            );
        } catch (RepositoryBrowserException $exception) {
            $this->assertSame(
                'invalid_branch',
                $exception->reason,
            );

            $this->assertSame(
                'resolve-branch',
                $exception->operation,
            );
        }
    }

    public function test_it_rejects_invalid_repository_path(): void
    {
        $repository = $this->createRepositoryWithHistory();

        try {
            app(RepositoryBrowserService::class)
                ->blob(
                    repository: $repository,
                    branch: 'main',
                    file: '../config',
                );

            $this->fail(
                'An invalid repository path did not fail.',
            );
        } catch (RepositoryBrowserException $exception) {
            $this->assertSame(
                'invalid_path',
                $exception->reason,
            );
        }
    }

    public function test_it_rejects_missing_repository_path(): void
    {
        $repository = $this->createRepositoryWithHistory();

        try {
            app(RepositoryBrowserService::class)
                ->blob(
                    repository: $repository,
                    branch: 'main',
                    file: 'missing.txt',
                );

            $this->fail(
                'A missing repository path did not fail.',
            );
        } catch (RepositoryBrowserException $exception) {
            $this->assertSame(
                'path_not_found',
                $exception->reason,
            );
        }
    }

    public function test_it_rejects_directory_when_blob_is_requested(): void
    {
        $repository = $this->createRepositoryWithHistory();

        try {
            app(RepositoryBrowserService::class)
                ->blob(
                    repository: $repository,
                    branch: 'main',
                    file: 'src',
                );

            $this->fail(
                'A repository directory was returned as a blob.',
            );
        } catch (RepositoryBrowserException $exception) {
            $this->assertSame(
                'path_is_not_file',
                $exception->reason,
            );
        }
    }

    public function test_it_rejects_blob_larger_than_configured_limit(): void
    {
        $repository = $this->createRepositoryWithHistory();

        config([
            'git.browser_max_blob_bytes' => 10,
        ]);

        try {
            app(RepositoryBrowserService::class)
                ->blob(
                    repository: $repository,
                    branch: 'main',
                    file: 'README.md',
                );

            $this->fail(
                'An oversized blob did not fail.',
            );
        } catch (RepositoryBrowserException $exception) {
            $this->assertSame(
                'blob_too_large',
                $exception->reason,
            );
        }
    }

    public function test_it_rejects_invalid_blob_size_configuration(): void
    {
        $repository = $this->createRepositoryWithHistory();

        config([
            'git.browser_max_blob_bytes' => 0,
        ]);

        try {
            app(RepositoryBrowserService::class)
                ->blob(
                    repository: $repository,
                    branch: 'main',
                    file: 'README.md',
                );

            $this->fail(
                'An invalid blob-size configuration was accepted.',
            );
        } catch (RepositoryBrowserException $exception) {
            $this->assertSame(
                'invalid_configuration',
                $exception->reason,
            );
        }
    }

    public function test_it_rejects_binary_blob(): void
    {
        $repository = $this->createRepositoryWithHistory();

        $binaryPath = $this->worktreePath
            .DIRECTORY_SEPARATOR
            .'binary.dat';

        File::put(
            $binaryPath,
            "GitForge\0Binary\n",
        );

        $runner = app(
            GitCommandRunner::class,
        );

        $runner->run(
            operation: 'stage-browser-test-binary',
            arguments: [
                'add',
                'binary.dat',
            ],
            workingDirectory: $this->worktreePath,
        );

        $runner->run(
            operation: 'commit-browser-test-binary',
            arguments: [
                'commit',
                '-m',
                'Add binary file',
            ],
            workingDirectory: $this->worktreePath,
        );

        $runner->run(
            operation: 'push-browser-test-binary',
            arguments: [
                'push',
                'origin',
                'main',
            ],
            workingDirectory: $this->worktreePath,
        );

        try {
            app(RepositoryBrowserService::class)
                ->blob(
                    repository: $repository,
                    branch: 'main',
                    file: 'binary.dat',
                );

            $this->fail(
                'A binary repository blob was displayed.',
            );
        } catch (RepositoryBrowserException $exception) {
            $this->assertSame(
                'binary_blob_not_supported',
                $exception->reason,
            );
        }
    }

    public function test_corrupt_repository_storage_is_rejected_before_browsing(): void
    {
        $repository = $this->createRepositoryWithHistory();

        $storage = app(
            RepositoryStorageService::class,
        );

        $repositoryPath = $storage->repositoryPath(
            $repository,
        );

        File::delete(
            $repositoryPath
                .DIRECTORY_SEPARATOR
                .'config',
        );

        try {
            app(RepositoryBrowserService::class)
                ->branches(
                    $repository,
                );

            $this->fail(
                'Corrupt repository storage was accepted for browsing.',
            );
        } catch (RepositoryStorageException $exception) {
            $this->assertSame(
                'verify-repository',
                $exception->operation,
            );

            $this->assertSame(
                $repository->id,
                $exception->repositoryId,
            );
        }
    }

    /**
     * Create a managed bare repository containing two commits.
     */
    private function createRepositoryWithHistory(): Repository
    {
        $repository = Repository::factory()->create();

        $storage = app(
            RepositoryStorageService::class,
        );

        $runner = app(
            GitCommandRunner::class,
        );

        $barePath = $storage->initialize(
            $repository,
        );

        File::ensureDirectoryExists(
            $this->worktreePath,
        );

        $runner->run(
            operation: 'initialize-browser-test-worktree',
            arguments: [
                'init',
                '--initial-branch=main',
            ],
            workingDirectory: $this->worktreePath,
        );

        $runner->run(
            operation: 'configure-browser-test-user-name',
            arguments: [
                'config',
                'user.name',
                'Repository Browser Test',
            ],
            workingDirectory: $this->worktreePath,
        );

        $runner->run(
            operation: 'configure-browser-test-user-email',
            arguments: [
                'config',
                'user.email',
                'browser@example.test',
            ],
            workingDirectory: $this->worktreePath,
        );

        File::put(
            $this->worktreePath
                .DIRECTORY_SEPARATOR
                .'README.md',
            "  # Repository Browser\n\nPreserve whitespace.  \n",
        );

        $runner->run(
            operation: 'stage-browser-test-readme',
            arguments: [
                'add',
                'README.md',
            ],
            workingDirectory: $this->worktreePath,
        );

        $runner->run(
            operation: 'commit-browser-test-readme',
            arguments: [
                'commit',
                '-m',
                'Initial commit',
            ],
            workingDirectory: $this->worktreePath,
        );

        File::ensureDirectoryExists(
            $this->worktreePath
                .DIRECTORY_SEPARATOR
                .'src',
        );

        File::put(
            $this->worktreePath
                .DIRECTORY_SEPARATOR
                .'src'
                .DIRECTORY_SEPARATOR
                .'app.php',
            "<?php\n\necho 'GitForge';\n",
        );

        $runner->run(
            operation: 'stage-browser-test-source',
            arguments: [
                'add',
                'src/app.php',
            ],
            workingDirectory: $this->worktreePath,
        );

        $runner->run(
            operation: 'commit-browser-test-source',
            arguments: [
                'commit',
                '-m',
                'Add application source',
            ],
            workingDirectory: $this->worktreePath,
        );

        $runner->run(
            operation: 'add-browser-test-remote',
            arguments: [
                'remote',
                'add',
                'origin',
                $barePath,
            ],
            workingDirectory: $this->worktreePath,
        );

        $runner->run(
            operation: 'push-browser-test-history',
            arguments: [
                'push',
                'origin',
                'main',
            ],
            workingDirectory: $this->worktreePath,
        );

        return $repository;
    }
}
