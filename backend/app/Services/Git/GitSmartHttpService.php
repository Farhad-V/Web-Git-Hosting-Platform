<?php

namespace App\Services\Git;

use App\Exceptions\GitTransportException;
use App\Models\Repository;
use InvalidArgumentException;
use Symfony\Component\Process\Exception\ProcessTimedOutException;
use Symfony\Component\Process\Process;

class GitSmartHttpService
{
    private const UPLOAD_PACK = 'upload-pack';

    private const RECEIVE_PACK = 'receive-pack';

    public function __construct(
        private readonly RepositoryStorageService $storage,
    ) {}

    /**
     * Generate a Smart HTTP reference advertisement.
     */
    public function advertise(
        Repository $repository,
        string $service,
    ): string {
        $this->validateService($service);

        $repositoryPath = $this->requireRepositoryPath(
            $repository,
        );

        $output = $this->run(
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
        $this->validateService($service);

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

        return $this->run(
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
        return $this->storage->defaultBranch($repository);
    }

    /**
     * Return the HTTP request content type for a Git service.
     */
    public function requestContentType(string $service): string
    {
        $this->validateService($service);

        return "application/x-git-{$service}-request";
    }

    /**
     * Return the HTTP result content type for a Git service.
     */
    public function resultContentType(string $service): string
    {
        $this->validateService($service);

        return "application/x-git-{$service}-result";
    }

    /**
     * Return the advertisement content type for a Git service.
     */
    public function advertisementContentType(
        string $service,
    ): string {
        $this->validateService($service);

        return "application/x-git-{$service}-advertisement";
    }

    /**
     * Run a controlled Git transport process.
     *
     * @param  list<string>  $arguments
     */
    private function run(
        string $operation,
        array $arguments,
        ?string $input = null,
    ): string {
        $binary = trim(
            (string) config('git.binary', 'git'),
        );

        $timeout = (int) config(
            'git.timeout_seconds',
            30,
        );

        if ($binary === '') {
            throw new InvalidArgumentException(
                'The configured Git binary cannot be empty.',
            );
        }

        if ($timeout < 1) {
            throw new InvalidArgumentException(
                'The Git process timeout must be positive.',
            );
        }

        $process = new Process(
            command: [
                $binary,
                ...$arguments,
            ],
            env: [
                'GIT_CONFIG_NOSYSTEM' => '1',
                'GIT_TERMINAL_PROMPT' => '0',
                'LC_ALL' => 'C',
            ],
        );

        $process->setTimeout($timeout);

        if ($input !== null) {
            $process->setInput($input);
        }

        try {
            $process->run();
        } catch (ProcessTimedOutException $exception) {
            throw new GitTransportException(
                operation: $operation,
                processExitCode: 124,
                message: 'Git transport process timed out.',
                previous: $exception,
            );
        }

        if (! $process->isSuccessful()) {
            throw new GitTransportException(
                operation: $operation,
                processExitCode: $process->getExitCode() ?? 1,
            );
        }

        return $process->getOutput();
    }

    /**
     * Resolve an existing managed bare repository.
     */
    private function requireRepositoryPath(
        Repository $repository,
    ): string {
        if (! $this->storage->exists($repository)) {
            throw new GitTransportException(
                operation: 'resolve-repository',
                processExitCode: 404,
                message: 'Managed Git repository storage was not found.',
            );
        }

        return $this->storage->repositoryPath(
            $repository,
        );
    }

    /**
     * Validate the requested Git service.
     */
    private function validateService(string $service): void
    {
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
    private function packetLine(string $payload): string
    {
        return sprintf(
            '%04x',
            strlen($payload) + 4,
        ).$payload;
    }
}
