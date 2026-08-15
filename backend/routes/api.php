<?php

use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\PersonalAccessTokenController;
use App\Http\Controllers\Api\V1\RepositoryController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')
    ->name('api.v1.')
    ->group(function (): void {
        Route::middleware('throttle:5,1')
            ->group(function (): void {
                Route::post(
                    '/auth/register',
                    [AuthController::class, 'register'],
                )->name('auth.register');

                Route::post(
                    '/auth/login',
                    [AuthController::class, 'login'],
                )->name('auth.login');
            });

        Route::middleware([
            'auth:sanctum',
            'throttle:60,1',
        ])->group(function (): void {
            Route::get(
                '/auth/me',
                [AuthController::class, 'me'],
            )->name('auth.me');

            Route::post(
                '/auth/logout',
                [AuthController::class, 'logout'],
            )->name('auth.logout');

            Route::get(
                '/personal-access-tokens',
                [PersonalAccessTokenController::class, 'index'],
            )->name('personal-access-tokens.index');

            Route::post(
                '/personal-access-tokens',
                [PersonalAccessTokenController::class, 'store'],
            )->name('personal-access-tokens.store');

            Route::delete(
                '/personal-access-tokens/{token}',
                [PersonalAccessTokenController::class, 'destroy'],
            )
                ->whereNumber('token')
                ->name('personal-access-tokens.destroy');

            Route::get(
                '/repositories',
                [RepositoryController::class, 'index'],
            )->name('repositories.index');

            Route::post(
                '/repositories',
                [RepositoryController::class, 'store'],
            )->name('repositories.store');

            Route::get(
                '/repositories/{repository}',
                [RepositoryController::class, 'show'],
            )->name('repositories.show');

            Route::patch(
                '/repositories/{repository}',
                [RepositoryController::class, 'update'],
            )->name('repositories.update');

            Route::post(
                '/repositories/{repository}/archive',
                [RepositoryController::class, 'archive'],
            )->name('repositories.archive');

            Route::post(
                '/repositories/{repository}/unarchive',
                [RepositoryController::class, 'unarchive'],
            )->name('repositories.unarchive');

            Route::delete(
                '/repositories/{repository}',
                [RepositoryController::class, 'destroy'],
            )->name('repositories.destroy');
        });
    });
