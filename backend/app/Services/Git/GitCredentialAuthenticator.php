<?php

namespace App\Services\Git;

use App\Exceptions\GitAuthenticationException;
use App\Models\User;
use Illuminate\Http\Request;
use Laravel\Sanctum\PersonalAccessToken;

class GitCredentialAuthenticator
{
    /**
     * Authenticate Git Basic credentials and verify an ability.
     *
     * Git clients send:
     * - username as the Basic-auth username
     * - Sanctum personal access token as the password
     */
    public function authenticate(
        Request $request,
        string $requiredAbility,
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

        $accessToken = PersonalAccessToken::findToken(
            $plainTextToken,
        );

        if (
            $accessToken === null
            || (
                $accessToken->expires_at !== null
                && $accessToken->expires_at->isPast()
            )
            || ! $accessToken->can($requiredAbility)
        ) {
            throw new GitAuthenticationException;
        }

        $user = $accessToken->tokenable;

        if (
            ! $user instanceof User
            || ! hash_equals(
                $user->username,
                mb_strtolower(trim($username)),
            )
        ) {
            throw new GitAuthenticationException;
        }

        $accessToken->forceFill([
            'last_used_at' => now(),
        ])->save();

        $user->withAccessToken($accessToken);

        return $user;
    }
}
