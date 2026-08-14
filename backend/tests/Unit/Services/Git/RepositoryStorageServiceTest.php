<?php

namespace Tests\Unit\Services\Git;

use App\Exceptions\RepositoryStorageException;
use App\Models\Repository;
use App\Services\Git\RepositoryStorageService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Tests\TestCase;

class RepositoryStorageServiceTest extends TestCase
{
    use RefreshDatabase;

    private string $temporaryStorageRoot;

    protected function setUp(): void
    {
        parent::setUp();

        $this->temporaryStorageRoot = sys_get_temp_dir()
            .DIRECTORY_SEPARATOR
            .'git-hosting-platform-tests-'
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
        if (
            isset($this->temporaryStorageRoot)
            && File::isDirectory($this->temporaryStorageRoot)
        ) {
            File::deleteDirectory(
                $this->temporaryStorageRoot,
            );
        }

        parent::tearDown();
    }

    public function test_it_initializes_valid_bare_repository(): void
    {
        $repository = Repository::factory()->create();

        $service = app(RepositoryStorageService::class);

        $path = $service->initialize($repository);

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
            $service->exists($repository),
        );

        $head = File::get(
            $path.DIRECTORY_SEPARATOR.'HEAD',
        );

        $this->assertStringContainsString(
            'refs/heads/main',
            $head,
        );
    }

    public function test_storage_path_uses_immutable_repository_uuid(): void
    {
        $repository = Repository::factory()->create();

        $service = app(RepositoryStorageService::class);

        $expectedPath = $this->temporaryStorageRoot
            .DIRECTORY_SEPARATOR
            .'repositories'
            .DIRECTORY_SEPARATOR
            .$repository->storage_uuid
            .'.git';

        $this->assertSame(
            $expectedPath,
            $service->repositoryPath($repository),
        );
    }

    public function test_it_deletes_managed_repository_storage(): void
    {
        $repository = Repository::factory()->create();

        $service = app(RepositoryStorageService::class);

        $path = $service->initialize($repository);

        $this->assertTrue(
            File::isDirectory($path),
        );

        $service->delete($repository);

        $this->assertFalse(
            File::exists($path),
        );

        $this->assertFalse(
            $service->exists($repository),
        );
    }

    public function test_deleting_missing_storage_is_idempotent(): void
    {
        $repository = Repository::factory()->create();

        $service = app(RepositoryStorageService::class);

        $service->delete($repository);
        $service->delete($repository);

        $this->assertFalse(
            $service->exists($repository),
        );
    }

    public function test_it_refuses_to_overwrite_existing_storage(): void
    {
        $repository = Repository::factory()->create();

        $service = app(RepositoryStorageService::class);

        $service->initialize($repository);

        $this->expectException(
            RepositoryStorageException::class,
        );

        $service->initialize($repository);
    }

    public function test_it_rejects_invalid_storage_uuid(): void
    {
        $repository = Repository::factory()->create();

        $repository->forceFill([
            'storage_uuid' => '../outside-root',
        ]);

        $this->expectException(
            RepositoryStorageException::class,
        );

        app(RepositoryStorageService::class)
            ->repositoryPath($repository);
    }

    public function test_failed_initialization_leaves_no_repository_directory(): void
    {
        config([
            'git.initial_branch' => 'invalid..branch',
        ]);

        $repository = Repository::factory()->create();

        $service = app(RepositoryStorageService::class);
        $path = $service->repositoryPath($repository);

        try {
            $service->initialize($repository);

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
