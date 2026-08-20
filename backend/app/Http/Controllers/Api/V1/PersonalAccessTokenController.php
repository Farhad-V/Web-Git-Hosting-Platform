<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\StorePersonalAccessTokenRequest;
use App\Http\Resources\PersonalAccessTokenResource;
use App\Models\PersonalAccessToken;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Symfony\Component\HttpFoundation\Response;

class PersonalAccessTokenController extends Controller
{
    private const TOKEN_PREFIX = 'gitforge_pat';

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
        $identifier = bin2hex(
            random_bytes(16),
        );

        $secret = bin2hex(
            random_bytes(32),
        );

        $plainTextToken = sprintf(
            '%s_%s_%s',
            self::TOKEN_PREFIX,
            $identifier,
            $secret,
        );

        $token = $request
            ->user()
            ->personalAccessTokens()
            ->create([
                'name' => $request->validated()['name'],
                'token_identifier' => $identifier,
                'token_hash' => hash(
                    'sha256',
                    $plainTextToken,
                ),
                'revoked_at' => null,
            ]);

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
            ->whereKey($token)
            ->firstOrFail();

        if ($accessToken->revoked_at === null) {
            $accessToken->forceFill([
                'revoked_at' => now(),
            ])->save();
        }

        return response()->json([
            'message' => 'Personal access token revoked successfully.',
        ]);
    }
}