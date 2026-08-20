<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Git Executable
    |--------------------------------------------------------------------------
    |
    | GitForge invokes only a configured Git executable. Commands must be
    | supplied as structured argument arrays and must not pass through a shell.
    |
    */

    'binary' => env(
        'GITFORGE_GIT_BINARY',
        'git',
    ),

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
    */

    'browser_max_blob_bytes' => (int) env(
        'GIT_BROWSER_MAX_BLOB_BYTES',
        1_048_576,
    ),

    /*
    |--------------------------------------------------------------------------
    | Repository Storage
    |--------------------------------------------------------------------------
    |
    | Application metadata uses PostgreSQL repository IDs.
    |
    | Physical repository mapping:
    |
    | <storage-root>/repositories/<repository-id>.git
    |
    | Logical owner/repository namespaces are never authoritative filesystem
    | paths.
    |
    */

    'storage_root' => env(
        'GITFORGE_REPOSITORY_ROOT',
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