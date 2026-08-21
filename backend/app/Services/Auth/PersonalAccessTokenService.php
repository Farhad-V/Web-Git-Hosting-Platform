<?php

namespace App\Services\Auth;

use App\Models\PersonalAccessToken;
use App\Models\User;

class PersonalAccessTokenService
{
    private const TOKEN_PREFIX = 'gitforge_pat';

    private const IDENTIFIER_BYTES = 16;

    private const SECRET_BYTES = 32;

    /**
     * Issue a new GitForge personal access token.
     *
     * The plaintext token is returned only to the caller and is never
     * persisted.
     *
     * @return array{PersonalAccessToken, string}
     */
    public function issue(
        User $user,
        string $name,
    ): array {
        $identifier = bin2hex(
            random_bytes(
                self::IDENTIFIER_BYTES,
            ),
        );

        $secret = bin2hex(
            random_bytes(
                self::SECRET_BYTES,
            ),
        );

        $plainTextToken = sprintf(
            '%s_%s_%s',
            self::TOKEN_PREFIX,
            $identifier,
            $secret,
        );

        $token = $user
            ->personalAccessTokens()
            ->create([
                'name' => $name,
                'token_identifier' => $identifier,
                'token_hash' => $this->hash(
                    $plainTextToken,
                ),
                'revoked_at' => null,
            ]);

        return [
            $token,
            $plainTextToken,
        ];
    }

    /**
     * Resolve a valid, active PAT from plaintext credentials.
     */
    public function resolveActive(
        string $plainTextToken,
    ): ?PersonalAccessToken {
        $identifier = $this->extractIdentifier(
            $plainTextToken,
        );

        if ($identifier === null) {
            return null;
        }

        $token = PersonalAccessToken::query()
            ->with('user')
            ->where(
                'token_identifier',
                $identifier,
            )
            ->whereNull(
                'revoked_at',
            )
            ->first();

        if ($token === null) {
            return null;
        }

        if (
            ! hash_equals(
                (string) $token->token_hash,
                $this->hash(
                    $plainTextToken,
                ),
            )
        ) {
            return null;
        }

        return $token;
    }

    /**
     * Hash a plaintext PAT for persistence or verification.
     */
    public function hash(
        string $plainTextToken,
    ): string {
        return hash(
            'sha256',
            $plainTextToken,
        );
    }

    /**
     * Extract the indexed identifier from a valid V1 PAT.
     */
    private function extractIdentifier(
        string $plainTextToken,
    ): ?string {
        $pattern = sprintf(
            '/\A%s_([0-9a-f]{32})_[0-9a-f]{64}\z/',
            preg_quote(
                self::TOKEN_PREFIX,
                '/',
            ),
        );

        if (
            preg_match(
                $pattern,
                $plainTextToken,
                $matches,
            ) !== 1
        ) {
            return null;
        }

        return $matches[1];
    }
}
