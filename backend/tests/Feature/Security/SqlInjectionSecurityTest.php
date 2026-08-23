<?php

namespace Tests\Feature\Security;

use App\Models\Repository;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class SqlInjectionSecurityTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_sql_injection_payload_does_not_bypass_authentication(): void
    {
        $user = User::factory()->create([
            'email' => 'owner@example.test',
            'password' => Hash::make(
                'CorrectPassword123!',
            ),
        ]);

        $payload = "owner@example.test' OR '1'='1";

        $response = $this->postJson(
            route('api.v1.auth.login'),
            [
                'email' => $payload,
                'password' => 'incorrect-password',
            ],
        );

        $response
            ->assertUnprocessable()
            ->assertJsonValidationErrors([
                'email',
            ]);

        $this->assertGuest(
            'web',
        );

        $this->assertDatabaseHas(
            'users',
            [
                'id' => $user->id,
                'email' => 'owner@example.test',
            ],
        );
    }

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
