<?php

namespace App\Jobs;

use App\Enums\RepositoryStatus;
use App\Models\Repository;
use App\Services\Git\RepositoryStorageService;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Throwable;

class DeleteRepositoryStorage implements ShouldBeUnique, ShouldQueue
{
    use Queueable;

    /**
     * Maximum number of processing attempts.
     */
    public int $tries = 3;

    /**
     * Maximum number of seconds the job may run.
     */
    public int $timeout = 60;

    /**
     * Seconds for which the unique job lock is retained.
     */
    public int $uniqueFor = 3600;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public readonly int $repositoryId,
    ) {
        $this->onQueue('repository-storage');
    }

    /**
     * Return the unique job identifier.
     */
    public function uniqueId(): string
    {
        return (string) $this->repositoryId;
    }

    /**
     * Return retry delays in seconds.
     *
     * @return list<int>
     */
    public function backoff(): array
    {
        return [
            10,
            60,
            300,
        ];
    }

    /**
     * Delete storage first, then soft-delete metadata.
     */
    public function handle(
        RepositoryStorageService $storage,
    ): void {
        $repository = Repository::query()->find(
            $this->repositoryId,
        );

        if ($repository === null) {
            return;
        }

        if (
            ! in_array(
                $repository->status,
                [
                    RepositoryStatus::DeletionPending,
                    RepositoryStatus::DeletionFailed,
                ],
                true,
            )
        ) {
            return;
        }

        $storage->delete($repository);

        DB::transaction(
            static function () use ($repository): void {
                $repository->delete();
            },
        );
    }

    /**
     * Record a recoverable failure after all retries are exhausted.
     */
    public function failed(Throwable $exception): void
    {
        $repository = Repository::query()->find(
            $this->repositoryId,
        );

        if ($repository === null) {
            return;
        }

        $repository->update([
            'status' => RepositoryStatus::DeletionFailed,
            'deletion_failure_reason' => Str::limit(
                $exception->getMessage(),
                2000,
                '',
            ),
        ]);
    }
}
