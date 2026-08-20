<?php

namespace Tests\Unit\Services\Git;

use App\Exceptions\GitCommandException;
use App\Services\Git\GitCommandRunner;
use InvalidArgumentException;
use Tests\TestCase;

class GitCommandRunnerTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config([
            'git.binary' => 'git',
            'git.timeout_seconds' => 10,
        ]);
    }

    public function test_it_returns_the_installed_git_version(): void
    {
        $version = app(
            GitCommandRunner::class,
        )->version();

        $this->assertStringStartsWith(
            'git version ',
            $version,
        );
    }

    public function test_it_can_preserve_raw_git_output(): void
    {
        $runner = app(
            GitCommandRunner::class,
        );

        $rawOutput = $runner->runRaw(
            operation: 'raw-version',
            arguments: [
                '--version',
            ],
        );

        $trimmedOutput = $runner->run(
            operation: 'trimmed-version',
            arguments: [
                '--version',
            ],
        );

        $this->assertMatchesRegularExpression(
            '/\r?\n\z/',
            $rawOutput,
        );

        $this->assertSame(
            trim($rawOutput),
            $trimmedOutput,
        );

        $this->assertNotSame(
            $rawOutput,
            $trimmedOutput,
        );
    }

    public function test_it_passes_stdin_to_git_without_modifying_it(): void
    {
        $runner = app(
            GitCommandRunner::class,
        );

        $input = "GitForge\nbinary-safe-input\0content\n";

        $actualHash = $runner->run(
            operation: 'hash-stdin',
            arguments: [
                'hash-object',
                '--stdin',
            ],
            input: $input,
        );

        $temporaryFile = tempnam(
            sys_get_temp_dir(),
            'gitforge-hash-',
        );

        if ($temporaryFile === false) {
            $this->fail(
                'Unable to create temporary hash verification file.',
            );
        }

        try {
            file_put_contents(
                $temporaryFile,
                $input,
            );

            $expectedHash = $runner->run(
                operation: 'hash-file',
                arguments: [
                    'hash-object',
                    '--',
                    $temporaryFile,
                ],
            );

            $this->assertSame(
                $expectedHash,
                $actualHash,
            );
        } finally {
            if (is_file($temporaryFile)) {
                @unlink(
                    $temporaryFile,
                );
            }
        }
    }

    public function test_it_throws_exception_for_failed_git_command(): void
    {
        try {
            app(GitCommandRunner::class)->run(
                operation: 'invalid-test-operation',
                arguments: [
                    'not-a-real-git-command-for-tests',
                ],
            );

            $this->fail(
                'A failed Git command did not throw an exception.',
            );
        } catch (GitCommandException $exception) {
            $this->assertSame(
                'invalid-test-operation',
                $exception->operation,
            );

            $this->assertNotSame(
                0,
                $exception->processExitCode,
            );

            $this->assertNotSame(
                '',
                $exception->errorOutput,
            );
        }
    }

    public function test_it_rejects_non_string_arguments(): void
    {
        $this->expectException(
            InvalidArgumentException::class,
        );

        app(GitCommandRunner::class)->run(
            operation: 'invalid-argument-test',
            arguments: [
                123,
            ],
        );
    }

    public function test_it_rejects_null_bytes_in_arguments(): void
    {
        $this->expectException(
            InvalidArgumentException::class,
        );

        app(GitCommandRunner::class)->run(
            operation: 'null-byte-test',
            arguments: [
                "status\0malicious",
            ],
        );
    }

    public function test_it_rejects_empty_git_binary_configuration(): void
    {
        config([
            'git.binary' => '',
        ]);

        $this->expectException(
            InvalidArgumentException::class,
        );

        app(GitCommandRunner::class)
            ->version();
    }

    public function test_it_rejects_invalid_timeout_configuration(): void
    {
        config([
            'git.timeout_seconds' => 0,
        ]);

        $this->expectException(
            InvalidArgumentException::class,
        );

        app(GitCommandRunner::class)
            ->version();
    }

    public function test_process_timeout_is_mapped_to_git_command_exception(): void
    {
        config([
            'git.binary' => PHP_BINARY,
            'git.timeout_seconds' => 1,
        ]);

        try {
            app(GitCommandRunner::class)->run(
                operation: 'timeout-test',
                arguments: [
                    '-r',
                    'sleep(3);',
                ],
            );

            $this->fail(
                'A timed-out process did not throw an exception.',
            );
        } catch (GitCommandException $exception) {
            $this->assertSame(
                'timeout-test',
                $exception->operation,
            );

            $this->assertSame(
                124,
                $exception->processExitCode,
            );

            $this->assertSame(
                'The Git process exceeded its timeout.',
                $exception->errorOutput,
            );
        }
    }
}