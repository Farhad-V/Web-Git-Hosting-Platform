<?php

namespace Tests\Unit\Services\Git;

use App\Exceptions\GitCommandException;
use App\Exceptions\GitTransportException;
use App\Exceptions\RepositoryStorageException;
use App\Models\Repository;
use App\Services\Git\GitCommandRunner;
use App\Services\Git\GitSmartHttpService;
use App\Services\Git\RepositoryStorageService;
use InvalidArgumentException;
use Mockery;
use Tests\TestCase;

class GitSmartHttpServiceTest extends TestCase
{
    public function test_advertisement_is_executed_through_central_git_runner(): void
    {
        $repository = $this->repository();

        $repositoryPath = 'C:\\gitforge\\repositories\\1.git';

        $advertisement = "001eexample-advertisement\n";

        $storage = Mockery::mock(
            RepositoryStorageService::class,
        );

        $storage
            ->shouldReceive('resolveExisting')
            ->once()
            ->with($repository)
            ->andReturn($repositoryPath);

        $git = Mockery::mock(
            GitCommandRunner::class,
        );

        $git
            ->shouldReceive('runRaw')
            ->once()
            ->with(
                'advertise-upload-pack',
                [
                    'upload-pack',
                    '--stateless-rpc',
                    '--advertise-refs',
                    $repositoryPath,
                ],
                null,
                null,
            )
            ->andReturn(
                $advertisement,
            );

        $service = new GitSmartHttpService(
            storage: $storage,
            git: $git,
        );

        $serviceAnnouncement = "# service=git-upload-pack\n";

        $expected = sprintf(
            '%04x',
            strlen($serviceAnnouncement) + 4,
        )
            .$serviceAnnouncement
            .'0000'
            .$advertisement;

        $this->assertSame(
            $expected,
            $service->advertise(
                repository: $repository,
                service: 'upload-pack',
            ),
        );
    }

    public function test_rpc_passes_request_body_to_central_git_runner_unchanged(): void
    {
        config([
            'git.max_request_bytes' => 1024,
        ]);

        $repository = $this->repository();

        $repositoryPath = 'C:\\gitforge\\repositories\\1.git';

        $requestBody = "0008NA\0K";

        $responseBody = "0008ACK\n";

        $storage = Mockery::mock(
            RepositoryStorageService::class,
        );

        $storage
            ->shouldReceive('resolveExisting')
            ->once()
            ->with($repository)
            ->andReturn($repositoryPath);

        $git = Mockery::mock(
            GitCommandRunner::class,
        );

        $git
            ->shouldReceive('runRaw')
            ->once()
            ->with(
                'rpc-upload-pack',
                [
                    'upload-pack',
                    '--stateless-rpc',
                    $repositoryPath,
                ],
                null,
                $requestBody,
            )
            ->andReturn(
                $responseBody,
            );

        $service = new GitSmartHttpService(
            storage: $storage,
            git: $git,
        );

        $this->assertSame(
            $responseBody,
            $service->rpc(
                repository: $repository,
                service: 'upload-pack',
                requestBody: $requestBody,
            ),
        );
    }

    public function test_receive_pack_is_an_allowed_transport_service(): void
    {
        $storage = Mockery::mock(
            RepositoryStorageService::class,
        );

        $git = Mockery::mock(
            GitCommandRunner::class,
        );

        $service = new GitSmartHttpService(
            storage: $storage,
            git: $git,
        );

        $this->assertSame(
            'application/x-git-receive-pack-request',
            $service->requestContentType(
                'receive-pack',
            ),
        );

        $this->assertSame(
            'application/x-git-receive-pack-result',
            $service->resultContentType(
                'receive-pack',
            ),
        );

        $this->assertSame(
            'application/x-git-receive-pack-advertisement',
            $service->advertisementContentType(
                'receive-pack',
            ),
        );
    }

    public function test_unsupported_transport_service_is_rejected(): void
    {
        $service = new GitSmartHttpService(
            storage: Mockery::mock(
                RepositoryStorageService::class,
            ),
            git: Mockery::mock(
                GitCommandRunner::class,
            ),
        );

        $this->expectException(
            InvalidArgumentException::class,
        );

        $service->requestContentType(
            'shell',
        );
    }

    public function test_rpc_rejects_request_larger_than_configured_limit(): void
    {
        config([
            'git.max_request_bytes' => 4,
        ]);

        $service = new GitSmartHttpService(
            storage: Mockery::mock(
                RepositoryStorageService::class,
            ),
            git: Mockery::mock(
                GitCommandRunner::class,
            ),
        );

        try {
            $service->rpc(
                repository: $this->repository(),
                service: 'upload-pack',
                requestBody: '12345',
            );

            $this->fail(
                'An oversized Smart HTTP request was accepted.',
            );
        } catch (GitTransportException $exception) {
            $this->assertSame(
                'rpc-upload-pack',
                $exception->operation,
            );

            $this->assertSame(
                413,
                $exception->processExitCode,
            );
        }
    }

    public function test_missing_repository_storage_is_mapped_to_transport_failure(): void
    {
        $repository = $this->repository();

        $storageFailure = new RepositoryStorageException(
            operation: 'resolve-existing',
            repositoryId: 1,
        );

        $storage = Mockery::mock(
            RepositoryStorageService::class,
        );

        $storage
            ->shouldReceive('resolveExisting')
            ->once()
            ->with($repository)
            ->andThrow(
                $storageFailure,
            );

        $service = new GitSmartHttpService(
            storage: $storage,
            git: Mockery::mock(
                GitCommandRunner::class,
            ),
        );

        try {
            $service->advertise(
                repository: $repository,
                service: 'upload-pack',
            );

            $this->fail(
                'Unavailable repository storage was accepted.',
            );
        } catch (GitTransportException $exception) {
            $this->assertSame(
                'resolve-repository',
                $exception->operation,
            );

            $this->assertSame(
                500,
                $exception->processExitCode,
            );

            $this->assertSame(
                $storageFailure,
                $exception->getPrevious(),
            );

            $this->assertSame(
                'Managed Git repository storage is unavailable.',
                $exception->getMessage(),
            );
        }
    }

    public function test_git_command_failure_is_translated_to_transport_exception(): void
    {
        $repository = $this->repository();

        $repositoryPath = 'C:\\gitforge\\repositories\\1.git';

        $storage = Mockery::mock(
            RepositoryStorageService::class,
        );

        $storage
            ->shouldReceive('resolveExisting')
            ->once()
            ->with($repository)
            ->andReturn($repositoryPath);

        $gitFailure = new GitCommandException(
            operation: 'rpc-upload-pack',
            processExitCode: 128,
            errorOutput: 'internal git failure',
        );

        $git = Mockery::mock(
            GitCommandRunner::class,
        );

        $git
            ->shouldReceive('runRaw')
            ->once()
            ->andThrow(
                $gitFailure,
            );

        $service = new GitSmartHttpService(
            storage: $storage,
            git: $git,
        );

        try {
            $service->rpc(
                repository: $repository,
                service: 'upload-pack',
                requestBody: '0000',
            );

            $this->fail(
                'A Git command failure was not translated.',
            );
        } catch (GitTransportException $exception) {
            $this->assertSame(
                'rpc-upload-pack',
                $exception->operation,
            );

            $this->assertSame(
                128,
                $exception->processExitCode,
            );

            $this->assertSame(
                $gitFailure,
                $exception->getPrevious(),
            );

            $this->assertSame(
                'Git transport operation failed.',
                $exception->getMessage(),
            );
        }
    }

    public function test_git_command_timeout_is_translated_to_transport_timeout(): void
    {
        $repository = $this->repository();

        $repositoryPath = 'C:\\gitforge\\repositories\\1.git';

        $storage = Mockery::mock(
            RepositoryStorageService::class,
        );

        $storage
            ->shouldReceive('resolveExisting')
            ->once()
            ->with($repository)
            ->andReturn($repositoryPath);

        $git = Mockery::mock(
            GitCommandRunner::class,
        );

        $git
            ->shouldReceive('runRaw')
            ->once()
            ->andThrow(
                new GitCommandException(
                    operation: 'rpc-receive-pack',
                    processExitCode: 124,
                    errorOutput: 'The Git process exceeded its timeout.',
                ),
            );

        $service = new GitSmartHttpService(
            storage: $storage,
            git: $git,
        );

        try {
            $service->rpc(
                repository: $repository,
                service: 'receive-pack',
                requestBody: '0000',
            );

            $this->fail(
                'A Git timeout was not translated.',
            );
        } catch (GitTransportException $exception) {
            $this->assertSame(
                'rpc-receive-pack',
                $exception->operation,
            );

            $this->assertSame(
                124,
                $exception->processExitCode,
            );

            $this->assertSame(
                'Git transport process timed out.',
                $exception->getMessage(),
            );
        }
    }

    /**
     * Create a repository model suitable for service-level tests.
     */
    private function repository(): Repository
    {
        $repository = new Repository();

        $repository->setAttribute(
            'id',
            1,
        );

        return $repository;
    }
}