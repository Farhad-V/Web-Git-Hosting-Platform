<?php

namespace App\Services\Git;

use App\Exceptions\GitAuthenticationException;
use App\Models\PersonalAccessToken;
use App\Models\User;
use Illuminate\Http\Request;

class GitCredentialAuthenticator
{
    private const TOKEN_PREFIX = 'gitforge_pat';

    /**
     * Authenticate Git Smart HTTP Basic credentials.
     *
     * The Basic-auth username is the GitForge owner namespace.
     * The Basic-auth password is a GitForge personal access token.
     */
    public function authenticate(
        Request $request,
    ): User {
        $username = $request->getUser();
        $plainTextToken = $request->getPassword();

        if (
            ! is_string($username)
            || trim($username) === ''
            || ! is_string($plainTextToken)
            || $plainTextToken === ''
        ) {
            throw new GitAuthenticationException;
        }

        $username = mb_strtolower(
            trim($username),
        );

        $identifier = $this->extractIdentifier(
            $plainTextToken,
        );

        $token = PersonalAccessToken::query()
            ->with('user')
            ->where(
                'token_identifier',
                $identifier,
            )
            ->whereNull('revoked_at')
            ->first();

        if ($token === null) {
            throw new GitAuthenticationException;
        }

        $candidateHash = hash(
            'sha256',
            $plainTextToken,
        );

        if (
            ! hash_equals(
                $token->token_hash,
                $candidateHash,
            )
        ) {
            throw new GitAuthenticationException;
        }

        $user = $token->user;

        if (
            ! $user instanceof User
            || ! hash_equals(
                $user->username,
                $username,
            )
        ) {
            throw new GitAuthenticationException;
        }

        return $user;
    }

    /**
     * Extract and validate the indexed token identifier.
     */
    private function extractIdentifier(
        string $plainTextToken,
    ): string {
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
            throw new GitAuthenticationException;
        }

        return $matches[1];
    }
}