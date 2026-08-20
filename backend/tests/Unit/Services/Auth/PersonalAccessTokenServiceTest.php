<?php

namespace Tests\Unit\Services\Auth;

use App\Models\User;
use App\Services\Auth\PersonalAccessTokenService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PersonalAccessTokenServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_issues_token_using_v1_format_and_persists_only_hash(): void
    {
        $user = User::factory()->create();

        $service = app(
            PersonalAccessTokenService::class,
        );

        [$token, $plainTextToken] = $service->issue(
            user: $user,
            name: 'Development laptop',
        );

        $this->assertMatchesRegularExpression(
            '/\Agitforge_pat_[0-9a-f]{32}_[0-9a-f]{64}\z/',
            $plainTextToken,
        );

        $this->assertMatchesRegularExpression(
            '/\A[0-9a-f]{32}\z/',
            $token->token_identifier,
        );

        $this->assertMatchesRegularExpression(
            '/\A[0-9a-f]{64}\z/',
            $token->token_hash,
        );

        $this->assertSame(
            hash(
                'sha256',
                $plainTextToken,
            ),
            $token->token_hash,
        );

        $this->assertNotSame(
            $plainTextToken,
            $token->token_hash,
        );

        $this->assertNull(
            $token->revoked_at,
        );
    }

    public function test_it_resolves_valid_active_token(): void
    {
        $user = User::factory()->create();

        $service = app(
            PersonalAccessTokenService::class,
        );

        [$issuedToken, $plainTextToken] = $service->issue(
            user: $user,
            name: 'Git client',
        );

        $resolvedToken = $service->resolveActive(
            $plainTextToken,
        );

        $this->assertNotNull(
            $resolvedToken,
        );

        $this->assertSame(
            $issuedToken->id,
            $resolvedToken->id,
        );

        $this->assertSame(
            $user->id,
            $resolvedToken->user->id,
        );
    }

    public function test_it_rejects_malformed_token(): void
    {
        $service = app(
            PersonalAccessTokenService::class,
        );

        $this->assertNull(
            $service->resolveActive(
                'invalid-token',
            ),
        );
    }

    public function test_it_rejects_unknown_token_identifier(): void
    {
        $service = app(
            PersonalAccessTokenService::class,
        );

        $plainTextToken = sprintf(
            'gitforge_pat_%s_%s',
            str_repeat(
                'a',
                32,
            ),
            str_repeat(
                'b',
                64,
            ),
        );

        $this->assertNull(
            $service->resolveActive(
                $plainTextToken,
            ),
        );
    }

    public function test_it_rejects_token_with_wrong_secret(): void
    {
        $user = User::factory()->create();

        $service = app(
            PersonalAccessTokenService::class,
        );

        [, $plainTextToken] = $service->issue(
            user: $user,
            name: 'Git client',
        );

        $parts = explode(
            '_',
            $plainTextToken,
        );

        $invalidToken = sprintf(
            'gitforge_pat_%s_%s',
            $parts[2],
            str_repeat(
                'f',
                64,
            ),
        );

        $this->assertNull(
            $service->resolveActive(
                $invalidToken,
            ),
        );
    }

    public function test_it_rejects_revoked_token(): void
    {
        $user = User::factory()->create();

        $service = app(
            PersonalAccessTokenService::class,
        );

        [$token, $plainTextToken] = $service->issue(
            user: $user,
            name: 'Git client',
        );

        $token->forceFill([
            'revoked_at' => now(),
        ])->save();

        $this->assertNull(
            $service->resolveActive(
                $plainTextToken,
            ),
        );
    }

    public function test_issued_tokens_are_unique(): void
    {
        $user = User::factory()->create();

        $service = app(
            PersonalAccessTokenService::class,
        );

        [$firstToken, $firstPlainText] = $service->issue(
            user: $user,
            name: 'First',
        );

        [$secondToken, $secondPlainText] = $service->issue(
            user: $user,
            name: 'Second',
        );

        $this->assertNotSame(
            $firstPlainText,
            $secondPlainText,
        );

        $this->assertNotSame(
            $firstToken->token_identifier,
            $secondToken->token_identifier,
        );

        $this->assertNotSame(
            $firstToken->token_hash,
            $secondToken->token_hash,
        );
    }
}