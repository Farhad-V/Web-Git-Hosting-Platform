<?php

namespace App\Services\Git;

use App\Exceptions\GitCommandException;
use InvalidArgumentException;
use Symfony\Component\Process\Exception\ProcessTimedOutException;
use Symfony\Component\Process\Process;

class GitCommandRunner
{
    /**
     * Run a controlled Git operation without invoking a command shell.
     *
     * @param  list<string>  $arguments
     */
    public function run(
        string $operation,
        array $arguments,
        ?string $workingDirectory = null,
    ): string {
        $this->validateArguments($arguments);

        $binary = (string) config('git.binary', 'git');
        $timeout = (int) config('git.timeout_seconds', 30);

        if (trim($binary) === '') {
            throw new InvalidArgumentException(
                'The configured Git binary cannot be empty.',
            );
        }

        if ($timeout < 1) {
            throw new InvalidArgumentException(
                'The Git process timeout must be at least one second.',
            );
        }

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

        $process->setTimeout($timeout);

        try {
            $process->run();
        } catch (ProcessTimedOutException $exception) {
            throw new GitCommandException(
                operation: $operation,
                processExitCode: 124,
                errorOutput: 'The Git process exceeded its timeout.',
                previous: $exception,
            );
        }

        if (! $process->isSuccessful()) {
            throw new GitCommandException(
                operation: $operation,
                processExitCode: $process->getExitCode() ?? 1,
                errorOutput: trim($process->getErrorOutput()),
            );
        }

        return trim($process->getOutput());
    }

    /**
     * Return the installed Git version.
     */
    public function version(): string
    {
        return $this->run(
            operation: 'version',
            arguments: ['--version'],
        );
    }

    /**
     * Validate every argument before process execution.
     *
     * @param  list<string>  $arguments
     */
    private function validateArguments(array $arguments): void
    {
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
}
