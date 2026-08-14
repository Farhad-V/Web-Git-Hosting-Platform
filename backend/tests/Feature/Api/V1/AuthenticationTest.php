<?php

namespace Tests\Feature\Api\V1;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_register(): void
    {
        $response = $this->postJson(route('api.v1.auth.register'), [
            'name' => 'Farhad Valizadegan',
            'username' => '  FARHAD-VALIZADEGAN  ',
            'email' => '  FARHAD@example.com  ',
            'password' => 'StrongPassword!123',
            'password_confirmation' => 'StrongPassword!123',
            'device_name' => 'web',
        ]);

        $response
            ->assertCreated()
            ->assertJson([
                'message' => 'Registration successful.',
                'token_type' => 'Bearer',
                'user' => [
                    'name' => 'Farhad Valizadegan',
                    'username' => 'farhad-valizadegan',
                    'email' => 'farhad@example.com',
                ],
            ])
            ->assertJsonStructure([
                'message',
                'user' => [
                    'id',
                    'name',
                    'username',
                    'email',
                    'email_verified_at',
                    'created_at',
                    'updated_at',
                ],
                'access_token',
                'token_type',
            ]);

        $this->assertDatabaseHas('users', [
            'name' => 'Farhad Valizadegan',
            'username' => 'farhad-valizadegan',
            'email' => 'farhad@example.com',
        ]);

        $user = User::query()
            ->where('email', 'farhad@example.com')
            ->firstOrFail();

        $this->assertTrue(
            Hash::check('StrongPassword!123', $user->password)
        );

        $this->assertDatabaseCount('personal_access_tokens', 1);
    }

    public function test_registration_requires_valid_data(): void
    {
        $response = $this->postJson(route('api.v1.auth.register'), [
            'name' => 'F',
            'username' => 'ADMIN',
            'email' => 'invalid-email',
            'password' => 'weak',
            'password_confirmation' => 'different',
        ]);

        $response
            ->assertUnprocessable()
            ->assertJsonValidationErrors([
                'name',
                'username',
                'email',
                'password',
            ]);

        $this->assertDatabaseCount('users', 0);
        $this->assertDatabaseCount('personal_access_tokens', 0);
    }

    public function test_username_must_be_unique(): void
    {
        User::factory()->create([
            'username' => 'farhad',
        ]);

        $response = $this->postJson(route('api.v1.auth.register'), [
            'name' => 'Another User',
            'username' => 'FARHAD',
            'email' => 'another@example.com',
            'password' => 'StrongPassword!123',
            'password_confirmation' => 'StrongPassword!123',
        ]);

        $response
            ->assertUnprocessable()
            ->assertJsonValidationErrors('username');

        $this->assertDatabaseCount('users', 1);
    }

    public function test_email_must_be_unique(): void
    {
        User::factory()->create([
            'email' => 'farhad@example.com',
        ]);

        $response = $this->postJson(route('api.v1.auth.register'), [
            'name' => 'Another User',
            'username' => 'another-user',
            'email' => 'FARHAD@example.com',
            'password' => 'StrongPassword!123',
            'password_confirmation' => 'StrongPassword!123',
        ]);

        $response
            ->assertUnprocessable()
            ->assertJsonValidationErrors('email');

        $this->assertDatabaseCount('users', 1);
    }

    public function test_user_can_login(): void
    {
        User::factory()->create([
            'username' => 'farhad',
            'email' => 'farhad@example.com',
            'password' => 'StrongPassword!123',
        ]);

        $response = $this->postJson(route('api.v1.auth.login'), [
            'email' => '  FARHAD@example.com  ',
            'password' => 'StrongPassword!123',
            'device_name' => 'web',
        ]);

        $response
            ->assertOk()
            ->assertJson([
                'message' => 'Login successful.',
                'token_type' => 'Bearer',
                'user' => [
                    'username' => 'farhad',
                    'email' => 'farhad@example.com',
                ],
            ])
            ->assertJsonStructure([
                'message',
                'user',
                'access_token',
                'token_type',
            ]);

        $this->assertDatabaseCount('personal_access_tokens', 1);
    }

    public function test_login_rejects_invalid_credentials(): void
    {
        User::factory()->create([
            'email' => 'farhad@example.com',
            'password' => 'StrongPassword!123',
        ]);

        $response = $this->postJson(route('api.v1.auth.login'), [
            'email' => 'farhad@example.com',
            'password' => 'IncorrectPassword!123',
        ]);

        $response
            ->assertUnprocessable()
            ->assertJsonValidationErrors('email');

        $this->assertDatabaseCount('personal_access_tokens', 0);
    }

    public function test_authenticated_user_can_view_profile(): void
    {
        $user = User::factory()->create([
            'username' => 'farhad',
            'email' => 'farhad@example.com',
        ]);

        $token = $user
            ->createToken('test')
            ->plainTextToken;

        $response = $this
            ->withToken($token)
            ->getJson(route('api.v1.auth.me'));

        $response
            ->assertOk()
            ->assertJson([
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'username' => 'farhad',
                    'email' => 'farhad@example.com',
                ],
            ]);
    }

    public function test_unauthenticated_user_cannot_view_profile(): void
    {
        $this
            ->getJson(route('api.v1.auth.me'))
            ->assertUnauthorized();
    }

    public function test_user_can_logout(): void
    {
        $user = User::factory()->create();

        $token = $user
            ->createToken('test')
            ->plainTextToken;

        $this
            ->withToken($token)
            ->postJson(route('api.v1.auth.logout'))
            ->assertOk()
            ->assertJson([
                'message' => 'Logout successful.',
            ]);

        $this->assertDatabaseCount('personal_access_tokens', 0);

        $this->app['auth']->forgetGuards();

        $this
            ->withToken($token)
            ->getJson(route('api.v1.auth.me'))
            ->assertUnauthorized();
    }
}
