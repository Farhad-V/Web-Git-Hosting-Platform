<?php

namespace Tests\Unit\Services\Git;

use App\Exceptions\GitCommandException;
use App\Services\Git\GitCommandRunner;
use InvalidArgumentException;
use Tests\TestCase;

class GitCommandRunnerTest extends TestCase
{
    public function test_it_returns_the_installed_git_version(): void
    {
        config([
            'git.binary' => 'git',
            'git.timeout_seconds' => 10,
        ]);

        $version = app(GitCommandRunner::class)->version();

        $this->assertStringStartsWith(
            'git version ',
            $version,
        );
    }

    public function test_it_can_preserve_raw_git_output(): void
    {
        config([
            'git.binary' => 'git',
            'git.timeout_seconds' => 10,
        ]);

        $runner = app(GitCommandRunner::class);

        $rawOutput = $runner->runRaw(
            operation: 'raw-version',
            arguments: ['--version'],
        );

        $trimmedOutput = $runner->run(
            operation: 'trimmed-version',
            arguments: ['--version'],
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

    public function test_it_throws_exception_for_failed_git_command(): void
    {
        config([
            'git.binary' => 'git',
            'git.timeout_seconds' => 10,
        ]);

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

        app(GitCommandRunner::class)->version();
    }
}
