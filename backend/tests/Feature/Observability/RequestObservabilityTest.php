<?php

namespace Tests\Feature\Observability;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Route;
use Mockery;
use Tests\TestCase;

class RequestObservabilityTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Route::get(
            '/observability-test',
            function () {
                return response()->json([
                    'status' => 'ok',
                ]);
            },
        )->name(
            'observability.test',
        );
    }

    public function test_request_receives_server_generated_correlation_id(): void
    {
        Log::spy();

        $response = $this->getJson(
            '/observability-test',
            [
                'X-Request-ID' => 'client-controlled-id',
            ],
        );

        $response->assertOk()
            ->assertJson([
                'status' => 'ok',
            ]);

        $requestId = $response->headers->get(
            'X-Request-ID',
        );

        $this->assertIsString(
            $requestId,
        );

        $this->assertNotSame(
            '',
            $requestId,
        );

        $this->assertNotSame(
            'client-controlled-id',
            $requestId,
        );

        $this->assertMatchesRegularExpression(
            '/^[0-9a-f]{8}-[0-9a-f]{4}-4[0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/i',
            $requestId,
        );
    }

    public function test_request_start_and_completion_use_same_request_id(): void
    {
        Log::spy();

        $response = $this->getJson(
            '/observability-test',
        );

        $response->assertOk();

        $requestId = $response->headers->get(
            'X-Request-ID',
        );

        Log::shouldHaveReceived(
            'info',
        )->with(
            'request_started',
            Mockery::on(
                function (array $context) use ($requestId): bool {
                    return $context['event'] === 'request_started'
                        && $context['request_id'] === $requestId
                        && $context['method'] === 'GET'
                        && $context['path'] === 'observability-test'
                        && $context['actor_state'] === 'anonymous';
                },
            ),
        )->once();

        Log::shouldHaveReceived(
            'info',
        )->with(
            'request_completed',
            Mockery::on(
                function (array $context) use ($requestId): bool {
                    return $context['event'] === 'request_completed'
                        && $context['request_id'] === $requestId
                        && $context['route'] === 'observability.test'
                        && $context['status_code'] === 200
                        && $context['result'] === 'completed'
                        && is_float($context['duration_ms'])
                        && $context['duration_ms'] >= 0;
                },
            ),
        )->once();
    }

    public function test_sensitive_request_data_is_not_in_request_log_context(): void
    {
        Log::spy();

        $this->postJson(
            '/observability-test',
            [
                'password' => 'do-not-log-password',
                'token' => 'do-not-log-token',
            ],
            [
                'Authorization' => 'Bearer do-not-log-authorization',
            ],
        );

        Log::shouldHaveReceived(
            'info',
        )->with(
            'request_started',
            Mockery::on(
                function (array $context): bool {
                    $serialized = json_encode(
                        $context,
                    );

                    return ! str_contains(
                        $serialized,
                        'do-not-log-password',
                    )
                        && ! str_contains(
                            $serialized,
                            'do-not-log-token',
                        )
                        && ! str_contains(
                            $serialized,
                            'do-not-log-authorization',
                        )
                        && ! array_key_exists(
                            'authorization',
                            $context,
                        )
                        && ! array_key_exists(
                            'body',
                            $context,
                        );
                },
            ),
        )->once();
    }
}