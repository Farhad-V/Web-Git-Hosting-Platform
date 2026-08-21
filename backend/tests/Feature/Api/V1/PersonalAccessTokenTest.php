<?php

namespace Tests\Feature\Api\V1;

use App\Models\PersonalAccessToken;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PersonalAccessTokenTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_can_create_personal_access_token(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs(
                $user,
                'web',
            )
            ->postJson(
                route('api.v1.personal-access-tokens.store'),
                [
                    'name' => 'Development laptop',
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
                    'revoked_at' => null,
                ],
            ])
            ->assertJsonStructure([
                'message',
                'token' => [
                    'id',
                    'name',
                    'revoked_at',
                    'created_at',
                    'updated_at',
                ],
                'plain_text_token',
            ]);

        $plainTextToken = $response->json(
            'plain_text_token',
        );

        $this->assertIsString(
            $plainTextToken,
        );

        $this->assertMatchesRegularExpression(
            '/\Agitforge_pat_[0-9a-f]{32}_[0-9a-f]{64}\z/',
            $plainTextToken,
        );

        $storedToken = PersonalAccessToken::query()
            ->where(
                'name',
                'Development laptop',
            )
            ->firstOrFail();

        $this->assertSame(
            32,
            strlen(
                $storedToken->token_identifier,
            ),
        );

        $this->assertMatchesRegularExpression(
            '/\A[0-9a-f]{32}\z/',
            $storedToken->token_identifier,
        );

        $this->assertMatchesRegularExpression(
            '/\A[0-9a-f]{64}\z/',
            $storedToken->token_hash,
        );

        $this->assertNotSame(
            $plainTextToken,
            $storedToken->token_hash,
        );

        $this->assertSame(
            hash(
                'sha256',
                $plainTextToken,
            ),
            $storedToken->token_hash,
        );

        $this->assertNull(
            $storedToken->revoked_at,
        );
    }

    public function test_plaintext_token_is_not_exposed_when_listing_tokens(): void
    {
        $user = User::factory()->create();

        $user
            ->personalAccessTokens()
            ->create([
                'name' => 'Laptop',
                'token_identifier' => str_repeat(
                    'a',
                    32,
                ),
                'token_hash' => str_repeat(
                    'b',
                    64,
                ),
                'revoked_at' => null,
            ]);

        $response = $this
            ->actingAs(
                $user,
                'web',
            )
            ->getJson(
                route('api.v1.personal-access-tokens.index'),
            );

        $response
            ->assertOk()
            ->assertJsonCount(
                1,
                'data',
            )
            ->assertJsonFragment([
                'name' => 'Laptop',
            ]);

        $token = $response->json(
            'data.0',
        );

        $this->assertArrayNotHasKey(
            'plain_text_token',
            $token,
        );

        $this->assertArrayNotHasKey(
            'token_hash',
            $token,
        );

        $this->assertArrayNotHasKey(
            'token_identifier',
            $token,
        );
    }

    public function test_user_lists_only_their_own_tokens(): void
    {
        $user = User::factory()->create();

        $otherUser = User::factory()->create();

        $user
            ->personalAccessTokens()
            ->create([
                'name' => 'Owned token',
                'token_identifier' => str_repeat(
                    'a',
                    32,
                ),
                'token_hash' => str_repeat(
                    'b',
                    64,
                ),
                'revoked_at' => null,
            ]);

        $otherUser
            ->personalAccessTokens()
            ->create([
                'name' => 'Other token',
                'token_identifier' => str_repeat(
                    'c',
                    32,
                ),
                'token_hash' => str_repeat(
                    'd',
                    64,
                ),
                'revoked_at' => null,
            ]);

        $response = $this
            ->actingAs(
                $user,
                'web',
            )
            ->getJson(
                route('api.v1.personal-access-tokens.index'),
            );

        $response
            ->assertOk()
            ->assertJsonCount(
                1,
                'data',
            )
            ->assertJsonFragment([
                'name' => 'Owned token',
            ])
            ->assertJsonMissing([
                'name' => 'Other token',
            ]);
    }

    public function test_token_creation_requires_valid_name(): void
    {
        $user = User::factory()->create();

        $this
            ->actingAs(
                $user,
                'web',
            )
            ->postJson(
                route('api.v1.personal-access-tokens.store'),
                [
                    'name' => '',
                ],
            )
            ->assertUnprocessable()
            ->assertJsonValidationErrors(
                'name',
            );

        $this->assertDatabaseCount(
            'personal_access_tokens',
            0,
        );
    }

    public function test_token_name_is_trimmed(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs(
                $user,
                'web',
            )
            ->postJson(
                route('api.v1.personal-access-tokens.store'),
                [
                    'name' => '  Development laptop  ',
                ],
            );

        $response
            ->assertCreated()
            ->assertJsonPath(
                'token.name',
                'Development laptop',
            );

        $this->assertDatabaseHas(
            'personal_access_tokens',
            [
                'user_id' => $user->id,
                'name' => 'Development laptop',
            ],
        );
    }

    public function test_user_can_revoke_owned_token(): void
    {
        $user = User::factory()->create();

        $token = $user
            ->personalAccessTokens()
            ->create([
                'name' => 'Laptop',
                'token_identifier' => str_repeat(
                    'a',
                    32,
                ),
                'token_hash' => str_repeat(
                    'b',
                    64,
                ),
                'revoked_at' => null,
            ]);

        $this
            ->actingAs(
                $user,
                'web',
            )
            ->deleteJson(
                route(
                    'api.v1.personal-access-tokens.destroy',
                    [
                        'token' => $token->id,
                    ],
                ),
            )
            ->assertOk()
            ->assertJson([
                'message' => (
                    'Personal access token revoked successfully.'
                ),
            ]);

        $token->refresh();

        $this->assertNotNull(
            $token->revoked_at,
        );

        $this->assertDatabaseHas(
            'personal_access_tokens',
            [
                'id' => $token->id,
                'user_id' => $user->id,
            ],
        );
    }

    public function test_revocation_is_idempotent(): void
    {
        $user = User::factory()->create();

        $token = $user
            ->personalAccessTokens()
            ->create([
                'name' => 'Laptop',
                'token_identifier' => str_repeat(
                    'a',
                    32,
                ),
                'token_hash' => str_repeat(
                    'b',
                    64,
                ),
                'revoked_at' => now(),
            ]);

        $originalRevokedAt = $token
            ->revoked_at
            ->copy();

        $this
            ->actingAs(
                $user,
                'web',
            )
            ->deleteJson(
                route(
                    'api.v1.personal-access-tokens.destroy',
                    [
                        'token' => $token->id,
                    ],
                ),
            )
            ->assertOk();

        $token->refresh();

        $this->assertTrue(
            $token
                ->revoked_at
                ->equalTo(
                    $originalRevokedAt,
                ),
        );
    }

    public function test_user_cannot_revoke_another_users_token(): void
    {
        $user = User::factory()->create();

        $otherUser = User::factory()->create();

        $otherToken = $otherUser
            ->personalAccessTokens()
            ->create([
                'name' => 'Other laptop',
                'token_identifier' => str_repeat(
                    'a',
                    32,
                ),
                'token_hash' => str_repeat(
                    'b',
                    64,
                ),
                'revoked_at' => null,
            ]);

        $this
            ->actingAs(
                $user,
                'web',
            )
            ->deleteJson(
                route(
                    'api.v1.personal-access-tokens.destroy',
                    [
                        'token' => $otherToken->id,
                    ],
                ),
            )
            ->assertNotFound();

        $otherToken->refresh();

        $this->assertNull(
            $otherToken->revoked_at,
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
                [
                    'name' => 'Laptop',
                ],
            )
            ->assertUnauthorized();

        $this
            ->deleteJson(
                route(
                    'api.v1.personal-access-tokens.destroy',
                    [
                        'token' => 1,
                    ],
                ),
            )
            ->assertUnauthorized();
    }
}
