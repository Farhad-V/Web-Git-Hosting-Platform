<?php

use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\Observability\HealthController;
use App\Http\Controllers\Api\V1\PersonalAccessTokenController;
use App\Http\Controllers\Api\V1\RepositoryBrowserController;
use App\Http\Controllers\Api\V1\RepositoryCollaborationController;
use App\Http\Controllers\Api\V1\RepositoryController;
use Illuminate\Support\Facades\Route;

/*
 * Readiness intentionally remains outside the web/session middleware group.
 *
 * A failed database dependency must be reportable as a controlled 503 rather
 * than failing first while attempting to initialize a database-backed session.
 */
Route::prefix('v1')
    ->name('api.v1.')
    ->group(function (): void {
        Route::get(
            '/health/readiness',
            [HealthController::class, 'readiness'],
        )->name('health.readiness');
    });

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
         * Repository read endpoints are policy-controlled.
         *
         * Public repositories may be read anonymously.
         * Private repositories remain owner-only.
         */
        Route::middleware('throttle:60,1')
            ->group(function (): void {
                Route::get(
                    '/repositories/{repository}',
                    [RepositoryController::class, 'show'],
                )->name('repositories.show');

                Route::get(
                    '/repositories/{repository}/branches',
                    [RepositoryBrowserController::class, 'branches'],
                )->name('repositories.branches');
                Route::get('/repositories/{repository}/pulls', [RepositoryCollaborationController::class, 'pulls'])->name('repositories.pulls');
                Route::get('/repositories/{repository}/pulls/{pull}', [RepositoryCollaborationController::class, 'pull'])->whereNumber('pull')->name('repositories.pulls.show');
                Route::get('/repositories/{repository}/pulls/{pull}/comments', [RepositoryCollaborationController::class, 'comments'])->whereNumber('pull')->name('repositories.pulls.comments');
                Route::get('/repositories/{repository}/actions', [RepositoryCollaborationController::class, 'actions'])->name('repositories.actions');
                Route::get('/repositories/{repository}/actions/{run}', [RepositoryCollaborationController::class, 'action'])->whereNumber('run')->name('repositories.actions.show');
                Route::get('/repositories/{repository}/tags', [RepositoryBrowserController::class, 'tags'])->name('repositories.tags');

                Route::get(
                    '/repositories/{repository}/commits',
                    [RepositoryBrowserController::class, 'commits'],
                )->name('repositories.commits');

                Route::get(
                    '/repositories/{repository}/commits/{commit}',
                    [RepositoryBrowserController::class, 'commit'],
                )->name('repositories.commit');

                Route::get(
                    '/repositories/{repository}/tree',
                    [RepositoryBrowserController::class, 'tree'],
                )->name('repositories.tree');

                Route::get(
                    '/repositories/{repository}/blob',
                    [RepositoryBrowserController::class, 'blob'],
                )->name('repositories.blob');
                Route::get('/repositories/{repository}/pdf', [RepositoryBrowserController::class, 'pdf'])->name('repositories.pdf');
            });

        Route::middleware([
            'auth',
            'throttle:60,1',
        ])->group(function (): void {
            Route::post('/repositories/{repository}/branches', [RepositoryCollaborationController::class, 'createBranch'])->name('repositories.branches.store');
            Route::patch('/repositories/{repository}/default-branch', [RepositoryCollaborationController::class, 'defaultBranch'])->name('repositories.branches.default');
            Route::post('/repositories/{repository}/pulls', [RepositoryCollaborationController::class, 'createPull'])->name('repositories.pulls.store');
            Route::patch('/repositories/{repository}/pulls/{pull}', [RepositoryCollaborationController::class, 'updatePull'])->whereNumber('pull')->name('repositories.pulls.update');
            Route::post('/repositories/{repository}/pulls/{pull}/merge', [RepositoryCollaborationController::class, 'mergePull'])->whereNumber('pull')->name('repositories.pulls.merge');
            Route::post('/repositories/{repository}/pulls/{pull}/comments', [RepositoryCollaborationController::class, 'comment'])->whereNumber('pull')->name('repositories.pulls.comments.store');
            Route::post('/repositories/{repository}/actions', [RepositoryCollaborationController::class, 'runAction'])->name('repositories.actions.store');
            Route::post('/repositories/{repository}/actions/{run}/cancel', [RepositoryCollaborationController::class, 'cancelAction'])->whereNumber('run')->name('repositories.actions.cancel');
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

            Route::patch(
                '/repositories/{repository}',
                [RepositoryController::class, 'update'],
            )->name('repositories.update');
        });
    });
