<?php

namespace Tests\Feature\Observability;

use App\Models\Repository;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Log;
use Mockery;
use Tests\TestCase;

class AuthorizationObservabilityTest extends TestCase
{
    use RefreshDatabase;

    public function test_anonymous_public_repository_read_logs_allow_decision(): void
    {
        $repository = Repository::factory()
            ->publicRepository()
            ->create();

        Log::spy();

        $response = $this->getJson(
            route(
                'api.v1.repositories.show',
                $repository,
            ),
        );

        $response->assertOk();

        $requestId = $response->headers->get(
            'X-Request-ID',
        );

        Log::shouldHaveReceived(
            'info',
        )->with(
            'authorization_decision',
            Mockery::on(
                function (array $context) use (
                    $repository,
                    $requestId,
                ): bool {
                    return $context['event']
                            === 'authorization_decision'
                        && $context['request_id']
                            === $requestId
                        && $context['user_id'] === null
                        && $context['actor_state']
                            === 'anonymous'
                        && $context['repository_id']
                            === $repository->id
                        && $context['operation']
                            === 'view'
                        && $context['decision']
                            === 'allow'
                        && $context['result']
                            === 'allowed'
                        && $context['failure_category']
                            === null;
                },
            ),
        )->once();
    }

    public function test_private_repository_read_denial_is_classified(): void
    {
        $owner = User::factory()->create();
        $viewer = User::factory()->create();

        $repository = Repository::factory()
            ->privateRepository()
            ->create([
                'owner_id' => $owner->id,
            ]);

        Log::spy();

        $response = $this
            ->actingAs(
                $viewer,
                'web',
            )
            ->getJson(
                route(
                    'api.v1.repositories.show',
                    $repository,
                ),
            );

        $response->assertNotFound();

        $requestId = $response->headers->get(
            'X-Request-ID',
        );

        Log::shouldHaveReceived(
            'info',
        )->with(
            'authorization_decision',
            Mockery::on(
                function (array $context) use (
                    $viewer,
                    $repository,
                    $requestId,
                ): bool {
                    return $context['event']
                            === 'authorization_decision'
                        && $context['request_id']
                            === $requestId
                        && $context['user_id']
                            === $viewer->id
                        && $context['actor_state']
                            === 'authenticated'
                        && $context['repository_id']
                            === $repository->id
                        && $context['operation']
                            === 'view'
                        && $context['decision']
                            === 'deny'
                        && $context['result']
                            === 'denied'
                        && $context['failure_category']
                            === 'authorization_denied';
                },
            ),
        )->once();
    }

    public function test_non_owner_write_decision_is_denied(): void
    {
        $owner = User::factory()->create();
        $otherUser = User::factory()->create();

        $repository = Repository::factory()
            ->privateRepository()
            ->create([
                'owner_id' => $owner->id,
            ]);

        Log::spy();

        $request = request();

        $request->attributes->set(
            'request_id',
            'write-authorization-request',
        );

        $allowed = app(
            \App\Policies\RepositoryPolicy::class,
        )->write(
            $otherUser,
            $repository,
        );

        $this->assertFalse(
            $allowed,
        );

        Log::shouldHaveReceived(
            'info',
        )->with(
            'authorization_decision',
            Mockery::on(
                function (array $context) use (
                    $otherUser,
                    $repository,
                ): bool {
                    return $context['request_id']
                            === 'write-authorization-request'
                        && $context['user_id']
                            === $otherUser->id
                        && $context['repository_id']
                            === $repository->id
                        && $context['operation']
                            === 'write'
                        && $context['decision']
                            === 'deny'
                        && $context['failure_category']
                            === 'authorization_denied';
                },
            ),
        )->once();
    }
}