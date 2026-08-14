<?php

namespace Tests\Feature\Jobs;

use App\Enums\RepositoryStatus;
use App\Exceptions\RepositoryStorageException;
use App\Jobs\DeleteRepositoryStorage;
use App\Models\Repository;
use App\Services\Git\RepositoryStorageService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Tests\TestCase;

class DeleteRepositoryStorageTest extends TestCase
{
    use RefreshDatabase;

    private string $temporaryStorageRoot;

    protected function setUp(): void
    {
        parent::setUp();

        $this->temporaryStorageRoot = sys_get_temp_dir()
            .DIRECTORY_SEPARATOR
            .'git-hosting-platform-job-tests-'
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

    public function test_job_deletes_storage_before_soft_deleting_metadata(): void
    {
        $repository = Repository::factory()->create([
            'status' => RepositoryStatus::DeletionPending,
            'deletion_requested_at' => now(),
        ]);

        $storage = app(RepositoryStorageService::class);
        $path = $storage->initialize($repository);

        $this->assertTrue(
            File::isDirectory($path),
        );

        $job = new DeleteRepositoryStorage(
            $repository->id,
        );

        $job->handle($storage);

        $this->assertFalse(
            File::exists($path),
        );

        $this->assertSoftDeleted(
            'repositories',
            [
                'id' => $repository->id,
            ],
        );
    }

    public function test_job_is_idempotent_when_storage_is_missing(): void
    {
        $repository = Repository::factory()->create([
            'status' => RepositoryStatus::DeletionPending,
            'deletion_requested_at' => now(),
        ]);

        $job = new DeleteRepositoryStorage(
            $repository->id,
        );

        $job->handle(
            app(RepositoryStorageService::class),
        );

        $this->assertSoftDeleted(
            'repositories',
            [
                'id' => $repository->id,
            ],
        );
    }

    public function test_job_ignores_repository_not_scheduled_for_deletion(): void
    {
        $repository = Repository::factory()->create([
            'status' => RepositoryStatus::Active,
        ]);

        $job = new DeleteRepositoryStorage(
            $repository->id,
        );

        $job->handle(
            app(RepositoryStorageService::class),
        );

        $this->assertDatabaseHas('repositories', [
            'id' => $repository->id,
            'status' => RepositoryStatus::Active->value,
            'deleted_at' => null,
        ]);
    }

    public function test_failed_job_records_recoverable_failure_state(): void
    {
        $repository = Repository::factory()->create([
            'status' => RepositoryStatus::DeletionPending,
            'deletion_requested_at' => now(),
        ]);

        $job = new DeleteRepositoryStorage(
            $repository->id,
        );

        $job->failed(
            new RepositoryStorageException(
                operation: 'delete',
                repositoryId: $repository->id,
            ),
        );

        $repository->refresh();

        $this->assertSame(
            RepositoryStatus::DeletionFailed,
            $repository->status,
        );

        $this->assertSame(
            'Repository storage operation [delete] failed '
            ."for repository [{$repository->id}].",
            $repository->deletion_failure_reason,
        );
    }

    public function test_job_has_stable_unique_identity_and_retry_schedule(): void
    {
        $job = new DeleteRepositoryStorage(42);

        $this->assertSame('42', $job->uniqueId());

        $this->assertSame(
            [
                10,
                60,
                300,
            ],
            $job->backoff(),
        );

        $this->assertSame(3, $job->tries);
        $this->assertSame(60, $job->timeout);
        $this->assertSame(3600, $job->uniqueFor);
    }
}
