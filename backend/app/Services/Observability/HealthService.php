<?php

namespace App\Services\Observability;

use App\Services\Git\GitCommandRunner;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Throwable;

class HealthService
{
    public function __construct(
        private readonly GitCommandRunner $git,
    ) {}

    /**
     * Evaluate whether GitForge core dependencies are ready.
     *
     * @return array{
     *     ready: bool,
     *     checks: array{
     *         database: string,
     *         repository_storage: string,
     *         git: string,
     *         configuration: string
     *     }
     * }
     */
    public function readiness(): array
    {
        $checks = [
            'database' => $this->databaseStatus(),
            'repository_storage' => $this->repositoryStorageStatus(),
            'git' => $this->gitStatus(),
            'configuration' => $this->configurationStatus(),
        ];

        $ready = ! in_array(
            'unavailable',
            $checks,
            true,
        );

        if (! $ready) {
            Log::error(
                'readiness_failed',
                [
                    'event' => 'readiness_failed',
                    'request_id' => $this->requestId(),
                    'result' => 'failure',
                    'failure_category' => 'dependency_unavailable',
                    'failed_checks' => array_keys(
                        array_filter(
                            $checks,
                            static fn (string $status): bool => (
                                $status === 'unavailable'
                            ),
                        ),
                    ),
                ],
            );
        }

        return [
            'ready' => $ready,
            'checks' => $checks,
        ];
    }

    private function databaseStatus(): string
    {
        try {
            DB::connection()->getPdo();

            return 'available';
        } catch (Throwable $exception) {
            Log::error(
                'database_unavailable',
                [
                    'event' => 'database_unavailable',
                    'request_id' => $this->requestId(),
                    'operation' => 'readiness',
                    'result' => 'failure',
                    'failure_category' => 'unavailable',
                    'exception_class' => $exception::class,
                ],
            );

            return 'unavailable';
        }
    }

    private function repositoryStorageStatus(): string
    {
        $storageRoot = trim(
            (string) config(
                'git.storage_root',
                '',
            ),
        );

        if ($storageRoot === '') {
            return 'unavailable';
        }

        try {
            File::ensureDirectoryExists(
                $storageRoot,
                0750,
                true,
            );

            return File::isDirectory(
                $storageRoot,
            )
                && is_writable(
                    $storageRoot,
                )
                    ? 'available'
                    : 'unavailable';
        } catch (Throwable) {
            return 'unavailable';
        }
    }

    private function gitStatus(): string
    {
        try {
            $version = trim(
                $this->git->version(),
            );

            return $version !== ''
                ? 'available'
                : 'unavailable';
        } catch (Throwable) {
            return 'unavailable';
        }
    }

    private function configurationStatus(): string
    {
        $binary = trim(
            (string) config(
                'git.binary',
                '',
            ),
        );

        $storageRoot = trim(
            (string) config(
                'git.storage_root',
                '',
            ),
        );

        $timeout = (int) config(
            'git.timeout_seconds',
            0,
        );

        $maxRequestBytes = (int) config(
            'git.max_request_bytes',
            0,
        );

        $browserMaxBlobBytes = (int) config(
            'git.browser_max_blob_bytes',
            0,
        );

        if (
            $binary === ''
            || $storageRoot === ''
            || $timeout < 1
            || $maxRequestBytes < 1
            || $browserMaxBlobBytes < 1
        ) {
            return 'unavailable';
        }

        return 'available';
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
