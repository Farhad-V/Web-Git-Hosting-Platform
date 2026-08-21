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
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    /**
     * Bootstrap CSRF protection for the browser application.
     */
    public function csrfToken(
        Request $request,
    ): JsonResponse {
        return response()->json([
            'csrf_token' => $request->session()->token(),
        ]);
    }

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

        Log::info(
            'authentication_succeeded',
            [
                'event' => 'authentication_succeeded',
                'request_id' => $request->attributes->get('request_id'),
                'user_id' => $user->id,
                'authentication_type' => 'session',
                'operation' => 'register',
                'result' => 'success',
            ],
        );

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
            Log::warning(
                'authentication_failed',
                [
                    'event' => 'authentication_failed',
                    'request_id' => $request->attributes->get('request_id'),
                    'authentication_type' => 'session',
                    'operation' => 'login',
                    'result' => 'failure',
                    'failure_category' => 'authentication_failure',
                ],
            );

            throw ValidationException::withMessages([
                'email' => [
                    'The provided credentials are incorrect.',
                ],
            ]);
        }

        $request->session()->regenerate();

        Log::info(
            'authentication_succeeded',
            [
                'event' => 'authentication_succeeded',
                'request_id' => $request->attributes->get('request_id'),
                'user_id' => $request->user()?->id,
                'authentication_type' => 'session',
                'operation' => 'login',
                'result' => 'success',
            ],
        );

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
        $userId = $request->user()?->id;

        Auth::guard('web')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        Log::info(
            'logout_succeeded',
            [
                'event' => 'logout_succeeded',
                'request_id' => $request->attributes->get('request_id'),
                'user_id' => $userId,
                'authentication_type' => 'session',
                'result' => 'success',
            ],
        );

        return response()->json([
            'message' => 'Logout successful.',
        ]);
    }
}
