<?php

namespace App\Services\Git;

use App\Exceptions\GitAuthenticationException;
use App\Models\User;
use App\Services\Auth\PersonalAccessTokenService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

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
            $this->logFailure(
                request: $request,
                reason: 'missing',
            );

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
            $this->logFailure(
                request: $request,
                reason: 'invalid_or_revoked',
            );

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
            $this->logFailure(
                request: $request,
                reason: 'username_mismatch',
            );

            throw new GitAuthenticationException;
        }

        Log::info(
            'pat_authentication_succeeded',
            [
                'event' => 'pat_authentication_succeeded',
                'request_id' => $request->attributes->get('request_id'),
                'user_id' => $user->id,
                'token_identifier' => $token->token_identifier,
                'authentication_type' => 'pat',
                'result' => 'success',
            ],
        );

        return $user;
    }

    private function logFailure(
        Request $request,
        string $reason,
    ): void {
        Log::warning(
            'pat_authentication_failed',
            [
                'event' => 'pat_authentication_failed',
                'request_id' => $request->attributes->get('request_id'),
                'authentication_type' => 'pat',
                'result' => 'failure',
                'failure_category' => 'authentication_failure',
                'reason' => $reason,
            ],
        );
    }
}