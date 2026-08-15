<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\StorePersonalAccessTokenRequest;
use App\Http\Resources\PersonalAccessTokenResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Laravel\Sanctum\PersonalAccessToken;
use Symfony\Component\HttpFoundation\Response;

class PersonalAccessTokenController extends Controller
{
    /**
     * List repository-access tokens owned by the authenticated user.
     */
    public function index(
        Request $request,
    ): AnonymousResourceCollection {
        $tokens = $request
            ->user()
            ->tokens()
            ->latest()
            ->get()
            ->filter(
                fn (PersonalAccessToken $token): bool => $this->isRepositoryToken($token),
            )
            ->values();

        return PersonalAccessTokenResource::collection($tokens);
    }

    /**
     * Create a repository-access token.
     *
     * The plain-text token is returned only in this response.
     */
    public function store(
        StorePersonalAccessTokenRequest $request,
    ): JsonResponse {
        $validated = $request->validated();

        $expiresAt = isset($validated['expires_at'])
            ? now()->parse($validated['expires_at'])
            : null;

        $createdToken = $request
            ->user()
            ->createToken(
                name: $validated['name'],
                abilities: $validated['abilities'],
                expiresAt: $expiresAt,
            );

        return response()->json([
            'message' => 'Personal access token created successfully.',
            'token' => new PersonalAccessTokenResource(
                $createdToken->accessToken,
            ),
            'plain_text_token' => $createdToken->plainTextToken,
        ], Response::HTTP_CREATED);
    }

    /**
     * Revoke one repository-access token owned by the current user.
     */
    public function destroy(
        Request $request,
        int $token,
    ): JsonResponse {
        $accessToken = $request
            ->user()
            ->tokens()
            ->whereKey($token)
            ->firstOrFail();

        abort_unless(
            $this->isRepositoryToken($accessToken),
            Response::HTTP_NOT_FOUND,
        );

        $accessToken->delete();

        return response()->json([
            'message' => 'Personal access token revoked successfully.',
        ]);
    }

    /**
     * Determine whether a token is dedicated to repository access.
     */
    private function isRepositoryToken(
        PersonalAccessToken $token,
    ): bool {
        $abilities = $token->abilities ?? [];

        return in_array(
            'repository:read',
            $abilities,
            true,
        ) || in_array(
            'repository:write',
            $abilities,
            true,
        );
    }
}
