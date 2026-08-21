<?php

namespace Tests\Feature\Observability;

use App\Exceptions\RepositoryStorageException;
use App\Models\Repository;
use App\Services\Git\GitCommandRunner;
use App\Services\Git\RepositoryStorageService;
use App\Services\Observability\HealthService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Mockery;
use ReflectionMethod;
use Tests\TestCase;

class InfrastructureObservabilityTest extends TestCase
{
    private string $temporaryStorageRoot;

    protected function setUp(): void
    {
        parent::setUp();

        $this->temporaryStorageRoot = sys_get_temp_dir()
            .DIRECTORY_SEPARATOR
            .'gitforge-infrastructure-observability-'
            .Str::uuid();

        config([
            'git.binary' => 'git',
            'git.timeout_seconds' => 10,
            'git.storage_root' => $this->temporaryStorageRoot,
            'git.repositories_directory' => 'repositories',
            'git.initial_branch' => 'main',
            'git.max_request_bytes' => 1024,
            'git.browser_max_blob_bytes' => 1024,
        ]);

        $request = Request::create(
            '/infrastructure-observability-test',
            'GET',
        );

        $request->attributes->set(
            'request_id',
            'infrastructure-request-id',
        );

        $this->app->instance(
            'request',
            $request,
        );
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

        DB::purge(
            'observability-broken',
        );

        parent::tearDown();
    }

    public function test_missing_repository_storage_records_named_event(): void
    {
        $repository = $this->repository(
            101,
        );

        Log::spy();

        try {
            app(
                RepositoryStorageService::class,
            )->resolveExisting(
                $repository,
            );

            $this->fail(
                'Missing repository storage unexpectedly resolved.',
            );
        } catch (RepositoryStorageException $exception) {
            $this->assertSame(
                'resolve-existing',
                $exception->operation,
            );
        }

        Log::shouldHaveReceived(
            'error',
        )->with(
            'repository_storage_missing',
            Mockery::on(
                function (array $context): bool {
                    return $context['event']
                            === 'repository_storage_missing'
                        && $context['request_id']
                            === 'infrastructure-request-id'
                        && $context['repository_id'] === 101
                        && $context['operation']
                            === 'resolve-existing'
                        && $context['result'] === 'failure'
                        && $context['failure_category']
                            === 'missing';
                },
            ),
        )->once();

        Log::shouldHaveReceived(
            'warning',
        )->with(
            'repository_storage_resolution_failed',
            Mockery::on(
                function (array $context): bool {
                    return $context['repository_id'] === 101
                        && $context['failure_category']
                            === 'missing';
                },
            ),
        )->once();
    }

    public function test_unexpected_existing_creation_target_records_named_event(): void
    {
        $repository = $this->repository(
            102,
        );

        $repositoriesDirectory = $this->temporaryStorageRoot
            .DIRECTORY_SEPARATOR
            .'repositories';

        File::ensureDirectoryExists(
            $repositoriesDirectory,
        );

        File::ensureDirectoryExists(
            $repositoriesDirectory
                .DIRECTORY_SEPARATOR
                .'102.git',
        );

        Log::spy();

        try {
            app(
                RepositoryStorageService::class,
            )->resolveNewTarget(
                $repository,
            );

            $this->fail(
                'Existing repository creation target was accepted.',
            );
        } catch (RepositoryStorageException $exception) {
            $this->assertSame(
                'resolve-new-target',
                $exception->operation,
            );
        }

        Log::shouldHaveReceived(
            'error',
        )->with(
            'repository_storage_unexpected_existing_target',
            Mockery::on(
                function (array $context): bool {
                    return $context['event']
                            === 'repository_storage_unexpected_existing_target'
                        && $context['request_id']
                            === 'infrastructure-request-id'
                        && $context['repository_id'] === 102
                        && $context['operation']
                            === 'resolve-new-target'
                        && $context['failure_category']
                            === 'inconsistent';
                },
            ),
        )->once();
    }

    public function test_unsafe_storage_path_records_security_relevant_event(): void
    {
        File::ensureDirectoryExists(
            $this->temporaryStorageRoot,
        );

        $storage = app(
            RepositoryStorageService::class,
        );

        $method = new ReflectionMethod(
            RepositoryStorageService::class,
            'assertContainedPath',
        );

        Log::spy();

        $outsidePath = sys_get_temp_dir()
            .DIRECTORY_SEPARATOR
            .'gitforge-outside-storage-'
            .Str::uuid();

        try {
            $method->invoke(
                $storage,
                $outsidePath,
                103,
            );

            $this->fail(
                'Unsafe repository storage path was accepted.',
            );
        } catch (RepositoryStorageException $exception) {
            $this->assertSame(
                'path-containment',
                $exception->operation,
            );
        }

        Log::shouldHaveReceived(
            'error',
        )->with(
            'repository_storage_unsafe_path',
            Mockery::on(
                function (array $context): bool {
                    return $context['event']
                            === 'repository_storage_unsafe_path'
                        && $context['request_id']
                            === 'infrastructure-request-id'
                        && $context['repository_id'] === 103
                        && $context['operation']
                            === 'path-containment'
                        && $context['result'] === 'failure'
                        && $context['failure_category']
                            === 'unsafe';
                },
            ),
        )->once();

        Log::shouldHaveReceived(
            'warning',
        )->with(
            'repository_storage_resolution_failed',
            Mockery::on(
                function (array $context): bool {
                    return $context['repository_id'] === 103
                        && $context['failure_category']
                            === 'unsafe';
                },
            ),
        )->once();
    }

    public function test_database_connectivity_failure_records_database_and_readiness_events(): void
    {
        File::ensureDirectoryExists(
            $this->temporaryStorageRoot,
        );

        config([
            'database.default' => 'observability-broken',
            'database.connections.observability-broken' => [
                'driver' => 'unsupported-observability-driver',
            ],
        ]);

        DB::purge(
            'observability-broken',
        );

        $git = Mockery::mock(
            GitCommandRunner::class,
        );

        $git->shouldReceive(
            'version',
        )
            ->once()
            ->andReturn(
                'git version test',
            );

        Log::spy();

        $health = new HealthService(
            $git,
        );

        $result = $health->readiness();

        $this->assertFalse(
            $result['ready'],
        );

        $this->assertSame(
            'unavailable',
            $result['checks']['database'],
        );

        Log::shouldHaveReceived(
            'error',
        )->with(
            'database_unavailable',
            Mockery::on(
                function (array $context): bool {
                    return $context['event']
                            === 'database_unavailable'
                        && $context['request_id']
                            === 'infrastructure-request-id'
                        && $context['operation']
                            === 'readiness'
                        && $context['result'] === 'failure'
                        && $context['failure_category']
                            === 'unavailable'
                        && isset(
                            $context['exception_class'],
                        );
                },
            ),
        )->once();

        Log::shouldHaveReceived(
            'error',
        )->with(
            'readiness_failed',
            Mockery::on(
                function (array $context): bool {
                    return $context['event']
                            === 'readiness_failed'
                        && $context['request_id']
                            === 'infrastructure-request-id'
                        && $context['result'] === 'failure'
                        && $context['failure_category']
                            === 'dependency_unavailable'
                        && in_array(
                            'database',
                            $context['failed_checks'],
                            true,
                        );
                },
            ),
        )->once();
    }

    private function repository(
        int $id,
    ): Repository {
        $repository = new Repository;

        $repository->forceFill([
            'id' => $id,
        ]);

        return $repository;
    }
}
