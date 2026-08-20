<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\LoginRequest;
use App\Http\Requests\Api\V1\RegisterRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    /**
     * Register a new user and establish an application session.
     */
    public function register(
        RegisterRequest $request,
    ): JsonResponse {
        $validated = $request->validated();

        $user = DB::transaction(
            static function () use ($validated): User {
                return User::query()->create([
                    'name' => $validated['name'],
                    'username' => $validated['username'],
                    'email' => $validated['email'],
                    'password' => $validated['password'],
                ]);
            },
        );

        Auth::guard('web')->login($user);

        $request->session()->regenerate();

        return response()->json([
            'message' => 'Registration successful.',
            'user' => new UserResource($user),
        ], 201);
    }

    /**
     * Authenticate a user using Laravel session authentication.
     *
     * @throws ValidationException
     */
    public function login(
        LoginRequest $request,
    ): JsonResponse {
        $validated = $request->validated();

        $authenticated = Auth::guard('web')->attempt([
            'email' => $validated['email'],
            'password' => $validated['password'],
        ]);

        if (! $authenticated) {
            throw ValidationException::withMessages([
                'email' => [
                    'The provided credentials are incorrect.',
                ],
            ]);
        }

        $request->session()->regenerate();

        return response()->json([
            'message' => 'Login successful.',
            'user' => new UserResource(
                $request->user(),
            ),
        ]);
    }

    /**
     * Return the authenticated application user.
     */
    public function me(
        Request $request,
    ): JsonResponse {
        return response()->json([
            'user' => new UserResource(
                $request->user(),
            ),
        ]);
    }

    /**
     * End the authenticated application session.
     */
    public function logout(
        Request $request,
    ): JsonResponse {
        Auth::guard('web')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return response()->json([
            'message' => 'Logout successful.',
        ]);
    }
}