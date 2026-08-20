<?php

namespace Tests\Feature\Observability;

use App\Services\Observability\HealthService;
use Mockery;
use Tests\TestCase;

class HealthReadinessTest extends TestCase
{
    public function test_liveness_endpoint_remains_available(): void
    {
        $this->get('/up')
            ->assertOk();
    }

    public function test_readiness_returns_minimal_ok_response_when_dependencies_are_ready(): void
    {
        $health = Mockery::mock(
            HealthService::class,
        );

        $health->shouldReceive(
            'readiness',
        )
            ->once()
            ->andReturn([
                'ready' => true,
                'checks' => [
                    'database' => 'available',
                    'repository_storage' => 'available',
                    'git' => 'available',
                    'configuration' => 'available',
                ],
            ]);

        $this->app->instance(
            HealthService::class,
            $health,
        );

        $response = $this->getJson(
            '/api/v1/health/readiness',
        );

        $response
            ->assertOk()
            ->assertExactJson([
                'status' => 'ok',
            ]);

        $this->assertNotNull(
            $response->headers->get(
                'X-Request-ID',
            ),
        );
    }

    public function test_readiness_returns_503_without_exposing_dependency_details(): void
    {
        $health = Mockery::mock(
            HealthService::class,
        );

        $health->shouldReceive(
            'readiness',
        )
            ->once()
            ->andReturn([
                'ready' => false,
                'checks' => [
                    'database' => 'unavailable',
                    'repository_storage' => 'available',
                    'git' => 'available',
                    'configuration' => 'available',
                ],
            ]);

        $this->app->instance(
            HealthService::class,
            $health,
        );

        $response = $this->getJson(
            '/api/v1/health/readiness',
        );

        $response
            ->assertStatus(503)
            ->assertExactJson([
                'status' => 'unavailable',
            ]);

        $response->assertJsonMissing([
            'database' => 'unavailable',
        ]);

        $response->assertJsonMissingPath(
            'checks',
        );
    }
}