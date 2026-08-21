<?php

namespace Tests\Feature\Api\V1;

use App\Models\PersonalAccessToken;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    public function test_csrf_bootstrap_returns_session_csrf_token(): void
    {
        $response = $this->getJson(
            route('api.v1.auth.csrf-token'),
        );

        $response
            ->assertOk()
            ->assertJsonStructure([
                'csrf_token',
            ]);

        $this->assertIsString(
            $response->json('csrf_token'),
        );

        $this->assertNotSame(
            '',
            $response->json('csrf_token'),
        );
    }

    public function test_user_can_register_and_session_is_established(): void
    {
        $response = $this->postJson(
            route('api.v1.auth.register'),
            [
                'name' => 'Farhad Valizadegan',
                'username' => 'farhad',
                'email' => 'FARHAD@EXAMPLE.COM',
                'password' => 'StrongPassword1!',
                'password_confirmation' => 'StrongPassword1!',
            ],
        );

        $response
            ->assertCreated()
            ->assertJson([
                'message' => 'Registration successful.',
                'user' => [
                    'name' => 'Farhad Valizadegan',
                    'username' => 'farhad',
                    'email' => 'farhad@example.com',
                ],
            ])
            ->assertJsonMissingPath('access_token')
            ->assertJsonMissingPath('token_type');

        $user = User::query()
            ->where('email', 'farhad@example.com')
            ->firstOrFail();

        $this->assertAuthenticatedAs(
            $user,
            'web',
        );

        $this->assertDatabaseHas(
            'users',
            [
                'id' => $user->id,
                'username' => 'farhad',
                'email' => 'farhad@example.com',
            ],
        );

        $this->assertTrue(
            Hash::check(
                'StrongPassword1!',
                $user->password,
            ),
        );

        $this->assertDatabaseCount(
            'personal_access_tokens',
            0,
        );
    }

    public function test_registration_requires_valid_data(): void
    {
        $response = $this->postJson(
            route('api.v1.auth.register'),
            [
                'name' => 'F',
                'username' => 'invalid username',
                'email' => 'not-an-email',
                'password' => 'weak',
                'password_confirmation' => 'different',
            ],
        );

        $response
            ->assertUnprocessable()
            ->assertJsonValidationErrors([
                'name',
                'username',
                'email',
                'password',
            ]);

        $this->assertDatabaseCount(
            'users',
            0,
        );
    }

    public function test_username_must_be_unique(): void
    {
        User::factory()->create([
            'username' => 'farhad',
        ]);

        $response = $this->postJson(
            route('api.v1.auth.register'),
            [
                'name' => 'Another User',
                'username' => 'FARHAD',
                'email' => 'another@example.com',
                'password' => 'StrongPassword1!',
                'password_confirmation' => 'StrongPassword1!',
            ],
        );

        $response
            ->assertUnprocessable()
            ->assertJsonValidationErrors(
                'username',
            );
    }

    public function test_email_must_be_unique(): void
    {
        User::factory()->create([
            'email' => 'farhad@example.com',
        ]);

        $response = $this->postJson(
            route('api.v1.auth.register'),
            [
                'name' => 'Another User',
                'username' => 'another-user',
                'email' => 'FARHAD@EXAMPLE.COM',
                'password' => 'StrongPassword1!',
                'password_confirmation' => 'StrongPassword1!',
            ],
        );

        $response
            ->assertUnprocessable()
            ->assertJsonValidationErrors(
                'email',
            );
    }

    public function test_user_can_login_and_session_is_established(): void
    {
        $user = User::factory()->create([
            'email' => 'farhad@example.com',
            'password' => 'StrongPassword1!',
        ]);

        $response = $this->postJson(
            route('api.v1.auth.login'),
            [
                'email' => ' FARHAD@EXAMPLE.COM ',
                'password' => 'StrongPassword1!',
            ],
        );

        $response
            ->assertOk()
            ->assertJson([
                'message' => 'Login successful.',
                'user' => [
                    'id' => $user->id,
                    'email' => 'farhad@example.com',
                ],
            ])
            ->assertJsonMissingPath('access_token')
            ->assertJsonMissingPath('token_type');

        $this->assertAuthenticatedAs(
            $user,
            'web',
        );

        $this->assertDatabaseCount(
            'personal_access_tokens',
            0,
        );
    }

    public function test_login_rejects_invalid_credentials(): void
    {
        User::factory()->create([
            'email' => 'farhad@example.com',
            'password' => 'StrongPassword1!',
        ]);

        $response = $this->postJson(
            route('api.v1.auth.login'),
            [
                'email' => 'farhad@example.com',
                'password' => 'WrongPassword1!',
            ],
        );

        $response
            ->assertUnprocessable()
            ->assertJsonValidationErrors(
                'email',
            );

        $this->assertGuest(
            'web',
        );
    }

    public function test_authenticated_user_can_view_profile(): void
    {
        $user = User::factory()->create();

        $this
            ->actingAs(
                $user,
                'web',
            )
            ->getJson(
                route('api.v1.auth.me'),
            )
            ->assertOk()
            ->assertJson([
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'username' => $user->username,
                    'email' => $user->email,
                ],
            ]);
    }

    public function test_unauthenticated_user_cannot_view_profile(): void
    {
        $this
            ->getJson(
                route('api.v1.auth.me'),
            )
            ->assertUnauthorized();
    }

    public function test_user_can_logout_and_session_is_invalidated(): void
    {
        $user = User::factory()->create();

        PersonalAccessToken::query()->create([
            'user_id' => $user->id,
            'name' => 'Git client',
            'token_identifier' => str_repeat('a', 32),
            'token_hash' => str_repeat('b', 64),
            'revoked_at' => null,
        ]);

        $this
            ->actingAs(
                $user,
                'web',
            )
            ->postJson(
                route('api.v1.auth.logout'),
            )
            ->assertOk()
            ->assertJson([
                'message' => 'Logout successful.',
            ]);

        $this->assertGuest(
            'web',
        );

        /*
         * Logging out of the browser session must not revoke or delete
         * GitForge personal access tokens.
         */
        $this->assertDatabaseHas(
            'personal_access_tokens',
            [
                'user_id' => $user->id,
                'name' => 'Git client',
                'revoked_at' => null,
            ],
        );
    }
}
