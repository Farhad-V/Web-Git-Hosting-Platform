<?php

namespace Tests\Feature\Security;

use App\Models\Repository;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SqlInjectionSecurityTest extends TestCase
{
    use RefreshDatabase;

    public function test_repository_name_sql_injection_payload_is_rejected_as_invalid_input(): void
    {
        $user = User::factory()->create();

        Repository::factory()->create([
            'owner_id' => $user->id,
            'name' => 'existing-repository',
            'visibility' => 'private',
        ]);

        $payload = "existing-repository' OR '1'='1";

        $response = $this
            ->actingAs(
                $user,
                'web',
            )
            ->postJson(
                route('api.v1.repositories.store'),
                [
                    'name' => $payload,
                    'visibility' => 'private',
                ],
            );

        $response
            ->assertUnprocessable()
            ->assertJsonValidationErrors([
                'name',
            ]);

        $this->assertDatabaseCount(
            'repositories',
            1,
        );

        $this->assertDatabaseMissing(
            'repositories',
            [
                'owner_id' => $user->id,
                'name' => mb_strtolower(
                    trim($payload),
                ),
            ],
        );
    }

    public function test_repository_owner_filter_treats_user_identity_as_bound_value(): void
    {
        $firstUser = User::factory()->create();

        $secondUser = User::factory()->create();

        Repository::factory()->create([
            'owner_id' => $firstUser->id,
            'name' => 'first-private-repository',
            'visibility' => 'private',
        ]);

        Repository::factory()->create([
            'owner_id' => $secondUser->id,
            'name' => 'second-private-repository',
            'visibility' => 'private',
        ]);

        $response = $this
            ->actingAs(
                $firstUser,
                'web',
            )
            ->getJson(
                route('api.v1.repositories.index'),
            );

        $response
            ->assertOk()
            ->assertJsonCount(
                1,
                'data',
            )
            ->assertJsonPath(
                'data.0.name',
                'first-private-repository',
            );

        $response->assertJsonMissing([
            'name' => 'second-private-repository',
        ]);
    }
}
