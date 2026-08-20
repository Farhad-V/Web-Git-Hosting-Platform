<?php

namespace App\Services\Git;

use App\Exceptions\GitCommandException;
use Illuminate\Support\Facades\Log;
use InvalidArgumentException;
use Symfony\Component\Process\Exception\ProcessTimedOutException;
use Symfony\Component\Process\Process;
use Throwable;

class GitCommandRunner
{
    /**
     * Run a controlled Git operation and return trimmed output.
     *
     * Use this method only for textual Git output where surrounding
     * whitespace is not semantically significant.
     *
     * @param  list<string>  $arguments
     */
    public function run(
        string $operation,
        array $arguments,
        ?string $workingDirectory = null,
        ?string $input = null,
    ): string {
        return trim(
            $this->runRaw(
                operation: $operation,
                arguments: $arguments,
                workingDirectory: $workingDirectory,
                input: $input,
            ),
        );
    }

    /**
     * Run a controlled Git operation while preserving stdout exactly.
     *
     * Raw execution is required for repository file contents and Smart HTTP
     * protocol responses because trimming or otherwise transforming stdout
     * would corrupt the returned data.
     *
     * The optional input string is passed directly to the Git process stdin.
     * It may contain arbitrary binary protocol data.
     *
     * Observability intentionally records only the approved logical operation
     * and bounded execution outcome. Command arguments, stdin, stdout, stderr,
     * credentials, repository content, and Git protocol payloads are never
     * written into process log context.
     *
     * @param  list<string>  $arguments
     */
    public function runRaw(
        string $operation,
        array $arguments,
        ?string $workingDirectory = null,
        ?string $input = null,
    ): string {
        $this->validateArguments(
            $arguments,
        );

        $binary = trim(
            (string) config(
                'git.binary',
                'git',
            ),
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
                'The Git process timeout must be at least one second.',
            );
        }

        $startedAt = hrtime(true);

        Log::info(
            'git_process_started',
            [
                'event' => 'git_process_started',
                'request_id' => $this->requestId(),
                'operation' => $operation,
            ],
        );

        try {
            $process = new Process(
                command: [
                    $binary,
                    ...$arguments,
                ],
                cwd: $workingDirectory,
                env: [
                    'GIT_CONFIG_NOSYSTEM' => '1',
                    'GIT_TERMINAL_PROMPT' => '0',
                    'LC_ALL' => 'C',
                ],
            );

            $process->setTimeout(
                $timeout,
            );

            if ($input !== null) {
                $process->setInput(
                    $input,
                );
            }

            $process->run();
        } catch (ProcessTimedOutException $exception) {
            $duration = $this->durationMilliseconds(
                $startedAt,
            );

            Log::warning(
                'git_process_timeout',
                [
                    'event' => 'git_process_timeout',
                    'request_id' => $this->requestId(),
                    'operation' => $operation,
                    'result' => 'failure',
                    'failure_category' => 'git_process_timeout',
                    'exit_code' => 124,
                    'timed_out' => true,
                    'configured_timeout' => $timeout,
                    'duration_ms' => $duration,
                ],
            );

            throw new GitCommandException(
                operation: $operation,
                processExitCode: 124,
                errorOutput: 'The Git process exceeded its timeout.',
                previous: $exception,
            );
        } catch (Throwable $exception) {
            Log::error(
                'git_process_start_failed',
                [
                    'event' => 'git_process_start_failed',
                    'request_id' => $this->requestId(),
                    'operation' => $operation,
                    'result' => 'failure',
                    'failure_category' => 'git_process_start_failure',
                    'duration_ms' => $this->durationMilliseconds(
                        $startedAt,
                    ),
                    'exception_class' => $exception::class,
                ],
            );

            throw new GitCommandException(
                operation: $operation,
                processExitCode: 1,
                errorOutput: 'The Git process could not be executed.',
                previous: $exception,
            );
        }

        $duration = $this->durationMilliseconds(
            $startedAt,
        );

        if (! $process->isSuccessful()) {
            $exitCode = $process->getExitCode() ?? 1;

            Log::warning(
                'git_process_failed',
                [
                    'event' => 'git_process_failed',
                    'request_id' => $this->requestId(),
                    'operation' => $operation,
                    'result' => 'failure',
                    'failure_category' => 'git_process_failure',
                    'exit_code' => $exitCode,
                    'timed_out' => false,
                    'duration_ms' => $duration,
                ],
            );

            throw new GitCommandException(
                operation: $operation,
                processExitCode: $exitCode,
                errorOutput: trim(
                    $process->getErrorOutput(),
                ),
            );
        }

        Log::info(
            'git_process_completed',
            [
                'event' => 'git_process_completed',
                'request_id' => $this->requestId(),
                'operation' => $operation,
                'result' => 'success',
                'exit_code' => 0,
                'timed_out' => false,
                'duration_ms' => $duration,
            ],
        );

        return $process->getOutput();
    }

    /**
     * Return the installed Git version.
     */
    public function version(): string
    {
        return $this->run(
            operation: 'version',
            arguments: [
                '--version',
            ],
        );
    }

    /**
     * Validate every process argument before execution.
     *
     * Symfony Process receives an argument array directly; no shell command
     * string is constructed. Null bytes are rejected explicitly because they
     * are invalid process arguments and could cause platform-specific
     * ambiguity.
     *
     * @param  list<string>  $arguments
     */
    private function validateArguments(
        array $arguments,
    ): void {
        foreach ($arguments as $argument) {
            if (! is_string($argument)) {
                throw new InvalidArgumentException(
                    'Every Git command argument must be a string.',
                );
            }

            if (str_contains($argument, "\0")) {
                throw new InvalidArgumentException(
                    'Git command arguments cannot contain null bytes.',
                );
            }
        }
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

    /**
     * Convert monotonic elapsed time to milliseconds.
     */
    private function durationMilliseconds(
        int $startedAt,
    ): float {
        return round(
            (hrtime(true) - $startedAt) / 1_000_000,
            3,
        );
    }
}