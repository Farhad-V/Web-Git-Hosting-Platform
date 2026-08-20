<?php

namespace App\Services\Git;

use App\Exceptions\GitCommandException;
use App\Exceptions\GitTransportException;
use App\Exceptions\RepositoryStorageException;
use App\Models\Repository;
use InvalidArgumentException;

class GitSmartHttpService
{
    private const UPLOAD_PACK = 'upload-pack';

    private const RECEIVE_PACK = 'receive-pack';

    public function __construct(
        private readonly RepositoryStorageService $storage,
        private readonly GitCommandRunner $git,
    ) {}

    /**
     * Generate a Smart HTTP reference advertisement.
     */
    public function advertise(
        Repository $repository,
        string $service,
    ): string {
        $this->validateService(
            $service,
        );

        $repositoryPath = $this->requireRepositoryPath(
            $repository,
        );

        $output = $this->runTransport(
            operation: "advertise-{$service}",
            arguments: [
                $service,
                '--stateless-rpc',
                '--advertise-refs',
                $repositoryPath,
            ],
        );

        return $this->packetLine(
            "# service=git-{$service}\n",
        )
            .'0000'
            .$output;
    }

    /**
     * Execute a Smart HTTP stateless RPC request.
     */
    public function rpc(
        Repository $repository,
        string $service,
        string $requestBody,
    ): string {
        $this->validateService(
            $service,
        );

        $maximumBytes = (int) config(
            'git.max_request_bytes',
            104_857_600,
        );

        if ($maximumBytes < 1) {
            throw new InvalidArgumentException(
                'The Git request-size limit must be positive.',
            );
        }

        if (strlen($requestBody) > $maximumBytes) {
            throw new GitTransportException(
                operation: "rpc-{$service}",
                processExitCode: 413,
                message: 'Git request body exceeds the configured limit.',
            );
        }

        $repositoryPath = $this->requireRepositoryPath(
            $repository,
        );

        return $this->runTransport(
            operation: "rpc-{$service}",
            arguments: [
                $service,
                '--stateless-rpc',
                $repositoryPath,
            ],
            input: $requestBody,
        );
    }

    /**
     * Discover the repository default branch after a successful push.
     */
    public function defaultBranch(
        Repository $repository,
    ): ?string {
        return $this->storage->defaultBranch(
            $repository,
        );
    }

    /**
     * Return the HTTP request content type for a Git service.
     */
    public function requestContentType(
        string $service,
    ): string {
        $this->validateService(
            $service,
        );

        return "application/x-git-{$service}-request";
    }

    /**
     * Return the HTTP result content type for a Git service.
     */
    public function resultContentType(
        string $service,
    ): string {
        $this->validateService(
            $service,
        );

        return "application/x-git-{$service}-result";
    }

    /**
     * Return the advertisement content type for a Git service.
     */
    public function advertisementContentType(
        string $service,
    ): string {
        $this->validateService(
            $service,
        );

        return "application/x-git-{$service}-advertisement";
    }

    /**
     * Execute a Smart HTTP Git process through the central Git runner.
     *
     * @param  list<string>  $arguments
     */
    private function runTransport(
        string $operation,
        array $arguments,
        ?string $input = null,
    ): string {
        try {
            return $this->git->runRaw(
                operation: $operation,
                arguments: $arguments,
                input: $input,
            );
        } catch (GitCommandException $exception) {
            $message = $exception->processExitCode === 124
                ? 'Git transport process timed out.'
                : 'Git transport operation failed.';

            throw new GitTransportException(
                operation: $operation,
                processExitCode: $exception->processExitCode,
                message: $message,
                previous: $exception,
            );
        }
    }

    /**
     * Resolve and verify an existing managed bare repository.
     */
    private function requireRepositoryPath(
        Repository $repository,
    ): string {
        try {
            return $this->storage->resolveExisting(
                $repository,
            );
        } catch (RepositoryStorageException $exception) {
            throw new GitTransportException(
                operation: 'resolve-repository',
                processExitCode: 500,
                message: 'Managed Git repository storage is unavailable.',
                previous: $exception,
            );
        }
    }

    /**
     * Validate the requested Smart HTTP Git service.
     */
    private function validateService(
        string $service,
    ): void {
        if (
            ! in_array(
                $service,
                [
                    self::UPLOAD_PACK,
                    self::RECEIVE_PACK,
                ],
                true,
            )
        ) {
            throw new InvalidArgumentException(
                'Unsupported Git Smart HTTP service.',
            );
        }
    }

    /**
     * Encode one Git packet-line.
     */
    private function packetLine(
        string $payload,
    ): string {
        return sprintf(
            '%04x',
            strlen($payload) + 4,
        ).$payload;
    }
}