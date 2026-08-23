<?php

namespace Tests\Unit\Services\Git;

use App\Exceptions\RepositoryStorageException;
use App\Models\Repository;
use App\Services\Git\GitCommandRunner;
use App\Services\Git\RepositoryStorageService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Tests\TestCase;

class RepositoryStorageServiceTest extends TestCase
{
    use RefreshDatabase;

    private string $temporaryStorageRoot;

    private string $temporaryOutsideRoot;

    protected function setUp(): void
    {
        parent::setUp();

        $this->temporaryStorageRoot = sys_get_temp_dir()
            .DIRECTORY_SEPARATOR
            .'gitforge-storage-tests-'
            .Str::uuid();

        $this->temporaryOutsideRoot = sys_get_temp_dir()
            .DIRECTORY_SEPARATOR
            .'gitforge-storage-outside-tests-'
            .Str::uuid();

        config([
            'git.binary' => 'git',
            'git.timeout_seconds' => 10,
            'git.storage_root' => $this->temporaryStorageRoot,
            'git.repositories_directory' => 'repositories',
            'git.initial_branch' => 'main',
        ]);
    }

    protected function tearDown(): void
    {
        $repositoriesLink = $this->temporaryStorageRoot
            .DIRECTORY_SEPARATOR
            .'repositories';

        if (is_link($repositoriesLink)) {
            @unlink($repositoriesLink);
        }

        if (
            isset($this->temporaryStorageRoot)
            && File::isDirectory($this->temporaryStorageRoot)
        ) {
            File::deleteDirectory(
                $this->temporaryStorageRoot,
            );
        }

        if (
            isset($this->temporaryOutsideRoot)
            && File::isDirectory($this->temporaryOutsideRoot)
        ) {
            File::deleteDirectory(
                $this->temporaryOutsideRoot,
            );
        }

        parent::tearDown();
    }

    public function test_it_initializes_valid_bare_repository(): void
    {
        $repository = Repository::factory()->create();

        $service = app(
            RepositoryStorageService::class,
        );

        $path = $service->initialize(
            $repository,
        );

        $this->assertTrue(
            File::isDirectory($path),
        );

        $this->assertTrue(
            File::exists(
                $path.DIRECTORY_SEPARATOR.'HEAD',
            ),
        );

        $this->assertTrue(
            File::exists(
                $path.DIRECTORY_SEPARATOR.'config',
            ),
        );

        $this->assertTrue(
            File::isDirectory(
                $path.DIRECTORY_SEPARATOR.'objects',
            ),
        );

        $this->assertTrue(
            File::isDirectory(
                $path.DIRECTORY_SEPARATOR.'refs',
            ),
        );

        $this->assertTrue(
            $service->exists(
                $repository,
            ),
        );
    }

    public function test_empty_repository_has_no_discoverable_default_branch(): void
    {
        $repository = Repository::factory()->create();

        $service = app(
            RepositoryStorageService::class,
        );

        $service->initialize(
            $repository,
        );

        $this->assertNull(
            $service->defaultBranch(
                $repository,
            ),
        );
    }

    public function test_it_discovers_default_branch_after_first_push(): void
    {
        $repository = Repository::factory()->create();

        $service = app(
            RepositoryStorageService::class,
        );

        $git = app(
            GitCommandRunner::class,
        );

        $bareRepositoryPath = $service->initialize(
            $repository,
        );

        $workingRepositoryPath = $this->temporaryStorageRoot
            .DIRECTORY_SEPARATOR
            .'working-repository';

        File::ensureDirectoryExists(
            $workingRepositoryPath,
        );

        $git->run(
            operation: 'initialize-test-working-repository',
            arguments: [
                'init',
                '--initial-branch=main',
                '--',
                $workingRepositoryPath,
            ],
        );

        File::put(
            $workingRepositoryPath
                .DIRECTORY_SEPARATOR
                .'README.md',
            "# Test Repository\n",
        );

        $git->run(
            operation: 'stage-test-commit',
            arguments: [
                '-C',
                $workingRepositoryPath,
                'add',
                'README.md',
            ],
        );

        $git->run(
            operation: 'create-test-commit',
            arguments: [
                '-C',
                $workingRepositoryPath,
                '-c',
                'user.name=GitForge Test',
                '-c',
                'user.email=gitforge@example.test',
                'commit',
                '-m',
                'test: create first commit',
            ],
        );

        $git->run(
            operation: 'push-test-default-branch',
            arguments: [
                '-C',
                $workingRepositoryPath,
                'push',
                $bareRepositoryPath,
                'main',
            ],
        );

        $this->assertSame(
            'main',
            $service->defaultBranch(
                $repository,
            ),
        );
    }

    public function test_storage_path_uses_database_repository_id(): void
    {
        $repository = Repository::factory()->create();

        $service = app(
            RepositoryStorageService::class,
        );

        $expectedPath = $this->temporaryStorageRoot
            .DIRECTORY_SEPARATOR
            .'repositories'
            .DIRECTORY_SEPARATOR
            .$repository->id
            .'.git';

        $this->assertSame(
            $expectedPath,
            $service->pathFor(
                $repository,
            ),
        );

        $this->assertSame(
            $expectedPath,
            $service->repositoryPath(
                $repository,
            ),
        );
    }

    public function test_repository_name_does_not_affect_physical_storage_path(): void
    {
        $repository = Repository::factory()->create([
            'name' => 'original-name',
        ]);

        $service = app(
            RepositoryStorageService::class,
        );

        $originalPath = $service->pathFor(
            $repository,
        );

        $repository->forceFill([
            'name' => 'changed-name',
        ]);

        $this->assertSame(
            $originalPath,
            $service->pathFor(
                $repository,
            ),
        );
    }

    public function test_relative_storage_root_is_rejected(): void
    {
        config([
            'git.storage_root' => 'relative/git/storage',
        ]);

        $repository = Repository::factory()->create();

        try {
            app(RepositoryStorageService::class)
                ->pathFor(
                    $repository,
                );

            $this->fail(
                'A relative repository storage root was accepted.',
            );
        } catch (RepositoryStorageException $exception) {
            $this->assertSame(
                'resolve-root',
                $exception->operation,
            );
        }
    }

    public function test_traversal_repository_directory_is_rejected(): void
    {
        config([
            'git.repositories_directory' => '../outside',
        ]);

        $repository = Repository::factory()->create();

        try {
            app(RepositoryStorageService::class)
                ->pathFor(
                    $repository,
                );

            $this->fail(
                'A traversal-oriented repositories directory was accepted.',
            );
        } catch (RepositoryStorageException $exception) {
            $this->assertSame(
                'resolve-root',
                $exception->operation,
            );
        }
    }

    public function test_absolute_repository_directory_is_rejected(): void
    {
        $absoluteDirectory = PHP_OS_FAMILY === 'Windows'
            ? 'C:\\gitforge\\outside'
            : '/tmp/gitforge/outside';

        config([
            'git.repositories_directory' => $absoluteDirectory,
        ]);

        $repository = Repository::factory()->create();

        try {
            app(RepositoryStorageService::class)
                ->pathFor(
                    $repository,
                );

            $this->fail(
                'An absolute repositories directory was accepted.',
            );
        } catch (RepositoryStorageException $exception) {
            $this->assertSame(
                'resolve-root',
                $exception->operation,
            );
        }
    }

    public function test_symlinked_repositories_directory_outside_root_is_rejected(): void
    {
        File::ensureDirectoryExists(
            $this->temporaryStorageRoot,
        );

        File::ensureDirectoryExists(
            $this->temporaryOutsideRoot,
        );

        $repositoriesLink = $this->temporaryStorageRoot
            .DIRECTORY_SEPARATOR
            .'repositories';

        $linked = @symlink(
            $this->temporaryOutsideRoot,
            $repositoriesLink,
        );

        if (! $linked) {
            $this->markTestSkipped(
                'Directory symlinks are unavailable for this PHP process.',
            );
        }

        $repository = Repository::factory()->create();

        try {
            app(RepositoryStorageService::class)
                ->pathFor(
                    $repository,
                );

            $this->fail(
                'A repositories directory symlink escaping the storage root '
                .'was accepted.',
            );
        } catch (RepositoryStorageException $exception) {
            $this->assertSame(
                'path-containment',
                $exception->operation,
            );
        } finally {
            if (is_link($repositoriesLink)) {
                @unlink($repositoriesLink);
            }
        }
    }

    public function test_it_refuses_to_overwrite_existing_storage(): void
    {
        $repository = Repository::factory()->create();

        $service = app(
            RepositoryStorageService::class,
        );

        $service->initialize(
            $repository,
        );

        $this->expectException(
            RepositoryStorageException::class,
        );

        $service->initialize(
            $repository,
        );
    }

    public function test_resolve_existing_returns_managed_repository_path(): void
    {
        $repository = Repository::factory()->create();

        $service = app(
            RepositoryStorageService::class,
        );

        $path = $service->initialize(
            $repository,
        );

        $this->assertSame(
            $path,
            $service->resolveExisting(
                $repository,
            ),
        );
    }

    public function test_resolve_existing_rejects_missing_storage(): void
    {
        $repository = Repository::factory()->create();

        $this->expectException(
            RepositoryStorageException::class,
        );

        app(RepositoryStorageService::class)
            ->resolveExisting(
                $repository,
            );
    }

    public function test_cleanup_partial_removes_failed_creation_storage(): void
    {
        $repository = Repository::factory()->create();

        $service = app(
            RepositoryStorageService::class,
        );

        $path = $service->initialize(
            $repository,
        );

        $this->assertTrue(
            File::isDirectory($path),
        );

        $service->cleanupPartial(
            $repository,
        );

        $this->assertFalse(
            File::exists($path),
        );

        $this->assertFalse(
            $service->exists(
                $repository,
            ),
        );
    }

    public function test_cleanup_partial_is_idempotent_for_missing_storage(): void
    {
        $repository = Repository::factory()->create();

        $service = app(
            RepositoryStorageService::class,
        );

        $service->cleanupPartial(
            $repository,
        );

        $service->cleanupPartial(
            $repository,
        );

        $this->assertFalse(
            $service->exists(
                $repository,
            ),
        );
    }

    public function test_failed_initialization_leaves_no_repository_directory(): void
    {
        config([
            'git.initial_branch' => 'invalid..branch',
        ]);

        $repository = Repository::factory()->create();

        $service = app(
            RepositoryStorageService::class,
        );

        $path = $service->pathFor(
            $repository,
        );

        try {
            $service->initialize(
                $repository,
            );

            $this->fail(
                'Invalid branch initialization did not fail.',
            );
        } catch (RepositoryStorageException) {
            $this->assertFalse(
                File::exists($path),
            );
        }
    }
}
