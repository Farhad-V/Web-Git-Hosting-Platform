<?php

namespace Tests\Unit\Services\Git;

use App\Exceptions\GitCommandException;
use App\Services\Git\GitCommandRunner;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Mockery;
use Tests\TestCase;

class GitCommandRunnerObservabilityTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config([
            'git.binary' => 'git',
            'git.timeout_seconds' => 10,
        ]);

        $request = Request::create(
            '/test',
            'GET',
        );

        $request->attributes->set(
            'request_id',
            'test-request-id',
        );

        $this->app->instance(
            'request',
            $request,
        );
    }

    public function test_successful_process_logs_start_and_completion_with_correlation(): void
    {
        Log::spy();

        $output = app(
            GitCommandRunner::class,
        )->run(
            operation: 'observability-version',
            arguments: [
                '--version',
            ],
        );

        $this->assertStringStartsWith(
            'git version ',
            $output,
        );

        Log::shouldHaveReceived(
            'info',
        )->with(
            'git_process_started',
            Mockery::on(
                function (array $context): bool {
                    return $context['event'] === 'git_process_started'
                        && $context['request_id'] === 'test-request-id'
                        && $context['operation'] === 'observability-version';
                },
            ),
        )->once();

        Log::shouldHaveReceived(
            'info',
        )->with(
            'git_process_completed',
            Mockery::on(
                function (array $context): bool {
                    return $context['event'] === 'git_process_completed'
                        && $context['request_id'] === 'test-request-id'
                        && $context['operation'] === 'observability-version'
                        && $context['result'] === 'success'
                        && $context['exit_code'] === 0
                        && $context['timed_out'] === false
                        && is_float($context['duration_ms'])
                        && $context['duration_ms'] >= 0;
                },
            ),
        )->once();
    }

    public function test_failed_process_logs_bounded_failure_without_stderr_or_arguments(): void
    {
        Log::spy();

        $secretMarker = 'do-not-log-git-secret';

        try {
            app(
                GitCommandRunner::class,
            )->run(
                operation: 'observability-failure',
                arguments: [
                    'not-a-real-git-command',
                    $secretMarker,
                ],
            );

            $this->fail(
                'Expected GitCommandException was not thrown.',
            );
        } catch (GitCommandException) {
            //
        }

        Log::shouldHaveReceived(
            'warning',
        )->with(
            'git_process_failed',
            Mockery::on(
                function (array $context) use ($secretMarker): bool {
                    $serialized = json_encode(
                        $context,
                    );

                    return $context['event'] === 'git_process_failed'
                        && $context['request_id'] === 'test-request-id'
                        && $context['operation'] === 'observability-failure'
                        && $context['result'] === 'failure'
                        && $context['failure_category'] === 'git_process_failure'
                        && $context['timed_out'] === false
                        && ! array_key_exists(
                            'arguments',
                            $context,
                        )
                        && ! array_key_exists(
                            'stderr',
                            $context,
                        )
                        && ! array_key_exists(
                            'input',
                            $context,
                        )
                        && ! str_contains(
                            $serialized,
                            $secretMarker,
                        );
                },
            ),
        )->once();
    }

    public function test_timeout_logs_classified_timeout_without_process_payload(): void
    {
        config([
            'git.binary' => PHP_BINARY,
            'git.timeout_seconds' => 1,
        ]);

        Log::spy();

        try {
            app(
                GitCommandRunner::class,
            )->run(
                operation: 'observability-timeout',
                arguments: [
                    '-r',
                    'sleep(3);',
                ],
            );

            $this->fail(
                'Expected timeout exception was not thrown.',
            );
        } catch (GitCommandException $exception) {
            $this->assertSame(
                124,
                $exception->processExitCode,
            );
        }

        Log::shouldHaveReceived(
            'warning',
        )->with(
            'git_process_timeout',
            Mockery::on(
                function (array $context): bool {
                    return $context['event'] === 'git_process_timeout'
                        && $context['request_id'] === 'test-request-id'
                        && $context['operation'] === 'observability-timeout'
                        && $context['failure_category'] === 'git_process_timeout'
                        && $context['exit_code'] === 124
                        && $context['timed_out'] === true
                        && $context['configured_timeout'] === 1
                        && ! array_key_exists(
                            'arguments',
                            $context,
                        )
                        && ! array_key_exists(
                            'stderr',
                            $context,
                        );
                },
            ),
        )->once();
    }
}