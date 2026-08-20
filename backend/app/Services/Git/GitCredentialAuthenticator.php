<?php

namespace App\Services\Git;

use App\Exceptions\GitAuthenticationException;
use App\Models\User;
use App\Services\Auth\PersonalAccessTokenService;
use Illuminate\Http\Request;

class GitCredentialAuthenticator
{
    public function __construct(
        private readonly PersonalAccessTokenService $tokens,
    ) {}

    /**
     * Authenticate Git Smart HTTP Basic credentials.
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
            trim(
                $username,
            ),
        );

        $token = $this->tokens->resolveActive(
            $plainTextToken,
        );

        if ($token === null) {
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
}