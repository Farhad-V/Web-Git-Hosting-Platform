<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\StorePersonalAccessTokenRequest;
use App\Http\Resources\PersonalAccessTokenResource;
use App\Services\Auth\PersonalAccessTokenService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class PersonalAccessTokenController extends Controller
{
    public function __construct(
        private readonly PersonalAccessTokenService $tokens,
    ) {}

    /**
     * List PATs owned by the authenticated user.
     */
    public function index(
        Request $request,
    ): AnonymousResourceCollection {
        $tokens = $request
            ->user()
            ->personalAccessTokens()
            ->latest()
            ->get();

        return PersonalAccessTokenResource::collection(
            $tokens,
        );
    }

    /**
     * Create a GitForge personal access token.
     *
     * The complete plaintext token is returned exactly once.
     */
    public function store(
        StorePersonalAccessTokenRequest $request,
    ): JsonResponse {
        [$token, $plainTextToken] = $this->tokens->issue(
            user: $request->user(),
            name: $request->validated()['name'],
        );

        Log::info(
            'pat_created',
            [
                'event' => 'pat_created',
                'request_id' => $request->attributes->get('request_id'),
                'user_id' => $request->user()->id,
                'token_id' => $token->id,
                'token_identifier' => $token->token_identifier,
                'result' => 'success',
            ],
        );

        return response()->json([
            'message' => 'Personal access token created successfully.',
            'token' => new PersonalAccessTokenResource(
                $token,
            ),
            'plain_text_token' => $plainTextToken,
        ], Response::HTTP_CREATED);
    }

    /**
     * Revoke a PAT owned by the current user.
     */
    public function destroy(
        Request $request,
        int $token,
    ): JsonResponse {
        $accessToken = $request
            ->user()
            ->personalAccessTokens()
            ->whereKey(
                $token,
            )
            ->firstOrFail();

        if ($accessToken->revoked_at === null) {
            $accessToken->forceFill([
                'revoked_at' => now(),
            ])->save();

            Log::info(
                'pat_revoked',
                [
                    'event' => 'pat_revoked',
                    'request_id' => $request->attributes->get('request_id'),
                    'user_id' => $request->user()->id,
                    'token_id' => $accessToken->id,
                    'token_identifier' => $accessToken->token_identifier,
                    'result' => 'success',
                ],
            );
        } else {
            Log::info(
                'pat_already_revoked',
                [
                    'event' => 'pat_already_revoked',
                    'request_id' => $request->attributes->get('request_id'),
                    'user_id' => $request->user()->id,
                    'token_id' => $accessToken->id,
                    'token_identifier' => $accessToken->token_identifier,
                    'result' => 'completed',
                ],
            );
        }

        return response()->json([
            'message' => 'Personal access token revoked successfully.',
        ]);
    }
}