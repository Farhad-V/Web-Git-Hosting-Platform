<?php

use App\Http\Controllers\GitSmartHttpController;
use Illuminate\Support\Facades\Route;

Route::get(
    '/{username}/{repository}.git/info/refs',
    [GitSmartHttpController::class, 'infoRefs'],
)
    ->where([
        'username' => '[a-z0-9](?:[a-z0-9-]{1,37}[a-z0-9])?',
        'repository' => '[a-z0-9](?:[a-z0-9._-]{0,98}[a-z0-9])?',
    ])
    ->name('git.info-refs');

Route::post(
    '/{username}/{repository}.git/git-upload-pack',
    [GitSmartHttpController::class, 'uploadPack'],
)
    ->where([
        'username' => '[a-z0-9](?:[a-z0-9-]{1,37}[a-z0-9])?',
        'repository' => '[a-z0-9](?:[a-z0-9._-]{0,98}[a-z0-9])?',
    ])
    ->name('git.upload-pack');

Route::post(
    '/{username}/{repository}.git/git-receive-pack',
    [GitSmartHttpController::class, 'receivePack'],
)
    ->where([
        'username' => '[a-z0-9](?:[a-z0-9-]{1,37}[a-z0-9])?',
        'repository' => '[a-z0-9](?:[a-z0-9._-]{0,98}[a-z0-9])?',
    ])
    ->name('git.receive-pack');