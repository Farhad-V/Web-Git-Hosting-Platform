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
    | Process Limits
    |--------------------------------------------------------------------------
    */

    'timeout_seconds' => (int) env(
        'GIT_PROCESS_TIMEOUT_SECONDS',
        30,
    ),

    'max_request_bytes' => (int) env(
        'GIT_MAX_REQUEST_BYTES',
        104_857_600,
    ),

    /*
    |--------------------------------------------------------------------------
    | Repository Browser Limits
    |--------------------------------------------------------------------------
    |
    | Repository files larger than this value are not returned through the
    | JSON browsing API. Git clone and fetch operations are unaffected.
    |
    */

    'browser_max_blob_bytes' => (int) env(
        'GIT_BROWSER_MAX_BLOB_BYTES',
        1_048_576,
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
