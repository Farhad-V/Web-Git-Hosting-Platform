<?php

use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\PersonalAccessTokenController;
use App\Http\Controllers\Api\V1\RepositoryBrowserController;
use App\Http\Controllers\Api\V1\RepositoryController;
use Illuminate\Support\Facades\Route;

Route::middleware('web')
    ->prefix('v1')
    ->name('api.v1.')
    ->group(function (): void {
        Route::get(
            '/auth/csrf-token',
            [AuthController::class, 'csrfToken'],
        )->name('auth.csrf-token');

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

        /*
         * Public repository metadata.
         *
         * Authorization remains policy-driven:
         * - public repository: guest or authenticated user may view
         * - private repository: owner only
         */
        Route::middleware('throttle:60,1')
            ->get(
                '/repositories/{repository}',
                [RepositoryController::class, 'show'],
            )
            ->name('repositories.show');

        Route::middleware([
            'auth',
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
                '/repositories/{repository}/branches',
                [RepositoryBrowserController::class, 'branches'],
            )->name('repositories.branches');

            Route::get(
                '/repositories/{repository}/commits',
                [RepositoryBrowserController::class, 'commits'],
            )->name('repositories.commits');

            Route::get(
                '/repositories/{repository}/tree',
                [RepositoryBrowserController::class, 'tree'],
            )->name('repositories.tree');

            Route::get(
                '/repositories/{repository}/blob',
                [RepositoryBrowserController::class, 'blob'],
            )->name('repositories.blob');

            Route::patch(
                '/repositories/{repository}',
                [RepositoryController::class, 'update'],
            )->name('repositories.update');
        });
    });