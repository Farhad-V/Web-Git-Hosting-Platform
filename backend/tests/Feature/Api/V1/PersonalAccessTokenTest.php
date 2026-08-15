<?php

namespace Tests\Feature\Api\V1;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\PersonalAccessToken;
use Tests\TestCase;

class PersonalAccessTokenTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_create_repository_access_token(): void
    {
        $user = User::factory()->create([
            'password' => 'StrongPassword!123',
        ]);

        $sessionToken = $user
            ->createToken('web', ['api'])
            ->plainTextToken;

        $expiresAt = now()
            ->addDays(30)
            ->startOfSecond();

        $response = $this
            ->withToken($sessionToken)
            ->postJson(
                route('api.v1.personal-access-tokens.store'),
                [
                    'name' => 'Development laptop',
                    'current_password' => 'StrongPassword!123',
                    'abilities' => [
                        'repository:read',
                        'repository:write',
                    ],
                    'expires_at' => $expiresAt->toISOString(),
                ],
            );

        $response
            ->assertCreated()
            ->assertJson([
                'message' => (
                    'Personal access token created successfully.'
                ),
                'token' => [
                    'name' => 'Development laptop',
                    'abilities' => [
                        'repository:read',
                        'repository:write',
                    ],
                    'is_expired' => false,
                ],
            ])
            ->assertJsonStructure([
                'message',
                'token' => [
                    'id',
                    'name',
                    'abilities',
                    'last_used_at',
                    'expires_at',
                    'is_expired',
                    'created_at',
                ],
                'plain_text_token',
            ]);

        $plainTextToken = $response->json(
            'plain_text_token',
        );

        $this->assertIsString($plainTextToken);
        $this->assertStringContainsString(
            '|',
            $plainTextToken,
        );

        $storedToken = PersonalAccessToken::query()
            ->where('name', 'Development laptop')
            ->firstOrFail();

        $this->assertSame(
            [
                'repository:read',
                'repository:write',
            ],
            $storedToken->abilities,
        );

        $this->assertNotSame(
            $plainTextToken,
            $storedToken->token,
        );

        $this->assertTrue(
            $storedToken->expires_at->equalTo($expiresAt),
        );
    }

    public function test_user_can_list_only_repository_access_tokens(): void
    {
        $user = User::factory()->create();

        $sessionToken = $user
            ->createToken('web', ['api'])
            ->plainTextToken;

        $user->createToken(
            'Read only',
            ['repository:read'],
        );

        $user->createToken(
            'Read and write',
            [
                'repository:read',
                'repository:write',
            ],
        );

        $response = $this
            ->withToken($sessionToken)
            ->getJson(
                route('api.v1.personal-access-tokens.index'),
            );

        $response
            ->assertOk()
            ->assertJsonCount(2, 'data')
            ->assertJsonFragment([
                'name' => 'Read only',
                'abilities' => ['repository:read'],
            ])
            ->assertJsonFragment([
                'name' => 'Read and write',
                'abilities' => [
                    'repository:read',
                    'repository:write',
                ],
            ])
            ->assertJsonMissing([
                'name' => 'web',
            ]);

        $this->assertArrayNotHasKey(
            'plain_text_token',
            $response->json('data.0'),
        );
    }

    public function test_token_creation_requires_correct_password(): void
    {
        $user = User::factory()->create([
            'password' => 'StrongPassword!123',
        ]);

        $sessionToken = $user
            ->createToken('web', ['api'])
            ->plainTextToken;

        $this
            ->withToken($sessionToken)
            ->postJson(
                route('api.v1.personal-access-tokens.store'),
                [
                    'name' => 'Laptop',
                    'current_password' => 'IncorrectPassword!123',
                    'abilities' => ['repository:read'],
                ],
            )
            ->assertUnprocessable()
            ->assertJsonValidationErrors('current_password');

        $this->assertDatabaseCount(
            'personal_access_tokens',
            1,
        );
    }

    public function test_token_creation_rejects_invalid_input(): void
    {
        $user = User::factory()->create([
            'password' => 'StrongPassword!123',
        ]);

        $sessionToken = $user
            ->createToken('web', ['api'])
            ->plainTextToken;

        $this
            ->withToken($sessionToken)
            ->postJson(
                route('api.v1.personal-access-tokens.store'),
                [
                    'name' => '',
                    'current_password' => 'StrongPassword!123',
                    'abilities' => ['administrator'],
                    'expires_at' => now()
                        ->subDay()
                        ->toISOString(),
                ],
            )
            ->assertUnprocessable()
            ->assertJsonValidationErrors([
                'name',
                'abilities.0',
                'expires_at',
            ]);

        $this->assertDatabaseCount(
            'personal_access_tokens',
            1,
        );
    }

    public function test_user_can_revoke_owned_repository_token(): void
    {
        $user = User::factory()->create();

        $sessionToken = $user
            ->createToken('web', ['api'])
            ->plainTextToken;

        $repositoryToken = $user->createToken(
            'Laptop',
            ['repository:read'],
        )->accessToken;

        $this
            ->withToken($sessionToken)
            ->deleteJson(
                route(
                    'api.v1.personal-access-tokens.destroy',
                    ['token' => $repositoryToken->id],
                ),
            )
            ->assertOk()
            ->assertJson([
                'message' => (
                    'Personal access token revoked successfully.'
                ),
            ]);

        $this->assertDatabaseMissing(
            'personal_access_tokens',
            [
                'id' => $repositoryToken->id,
            ],
        );

        $this->assertDatabaseHas(
            'personal_access_tokens',
            [
                'name' => 'web',
            ],
        );
    }

    public function test_user_cannot_revoke_another_users_token(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();

        $sessionToken = $user
            ->createToken('web', ['api'])
            ->plainTextToken;

        $otherToken = $otherUser->createToken(
            'Other laptop',
            ['repository:read'],
        )->accessToken;

        $this
            ->withToken($sessionToken)
            ->deleteJson(
                route(
                    'api.v1.personal-access-tokens.destroy',
                    ['token' => $otherToken->id],
                ),
            )
            ->assertNotFound();

        $this->assertDatabaseHas(
            'personal_access_tokens',
            [
                'id' => $otherToken->id,
            ],
        );
    }

    public function test_unauthenticated_user_cannot_manage_tokens(): void
    {
        $this
            ->getJson(
                route('api.v1.personal-access-tokens.index'),
            )
            ->assertUnauthorized();

        $this
            ->postJson(
                route('api.v1.personal-access-tokens.store'),
                [],
            )
            ->assertUnauthorized();

        $this
            ->deleteJson(
                route(
                    'api.v1.personal-access-tokens.destroy',
                    ['token' => 1],
                ),
            )
            ->assertUnauthorized();
    }
}
