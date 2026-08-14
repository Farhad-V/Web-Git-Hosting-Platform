<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Git Executable
    |--------------------------------------------------------------------------
    |
    | Use only a configured executable. Commands are passed as argument arrays,
    | never concatenated into shell command strings.
    |
    */

    'binary' => env('GIT_BINARY', 'git'),

    /*
    |--------------------------------------------------------------------------
    | Process Timeout
    |--------------------------------------------------------------------------
    */

    'timeout_seconds' => (int) env(
        'GIT_PROCESS_TIMEOUT_SECONDS',
        30,
    ),

    /*
    |--------------------------------------------------------------------------
    | Managed Storage
    |--------------------------------------------------------------------------
    |
    | Production:
    | /var/lib/git-host/repositories/{repository-uuid}.git
    |
    | Local default:
    | storage/app/git/repositories/{repository-uuid}.git
    |
    */

    'storage_root' => env(
        'GIT_STORAGE_ROOT',
        storage_path('app/git'),
    ),

    'repositories_directory' => 'repositories',

    /*
    |--------------------------------------------------------------------------
    | Repository Initialization
    |--------------------------------------------------------------------------
    */

    'initial_branch' => env(
        'GIT_INITIAL_BRANCH',
        'main',
    ),
];
