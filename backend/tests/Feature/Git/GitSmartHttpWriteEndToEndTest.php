<?php

namespace Tests\Feature\Git;

use App\Enums\RepositoryVisibility;
use App\Models\PersonalAccessToken;
use App\Models\Repository;
use App\Models\User;
use App\Services\Git\GitCommandRunner;
use App\Services\Git\RepositoryStorageService;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Symfony\Component\Process\Process;
use Tests\TestCase;

class GitSmartHttpWriteEndToEndTest extends TestCase
{
    private string $temporaryRoot;

    private string $temporaryDatabase;

    private string $temporaryStorageRoot;

    private string $temporaryWorkspaceRoot;

    private int $serverPort;

    private string $serverBaseUrl;

    private ?Process $serverProcess = null;

    protected function setUp(): void
    {
        parent::setUp();

        $this->temporaryRoot = sys_get_temp_dir()
            .DIRECTORY_SEPARATOR
            .'git-smart-http-write-e2e-'
            .Str::uuid();

        $this->temporaryDatabase = $this->temporaryRoot
            .DIRECTORY_SEPARATOR
            .'testing.sqlite';

        $this->temporaryStorageRoot = $this->temporaryRoot
            .DIRECTORY_SEPARATOR
            .'git-storage';

        $this->temporaryWorkspaceRoot = $this->temporaryRoot
            .DIRECTORY_SEPARATOR
            .'workspaces';

        File::ensureDirectoryExists(
            $this->temporaryRoot,
        );

        File::ensureDirectoryExists(
            $this->temporaryStorageRoot,
        );

        File::ensureDirectoryExists(
            $this->temporaryWorkspaceRoot,
        );

        File::put(
            $this->temporaryDatabase,
            '',
        );

        config([
            'database.default' => 'sqlite',
            'database.connections.sqlite.database' => (
                $this->temporaryDatabase
            ),
            'git.binary' => 'git',
            'git.timeout_seconds' => 30,
            'git.max_request_bytes' => 104_857_600,
            'git.storage_root' => $this->temporaryStorageRoot,
            'git.repositories_directory' => 'repositories',
            'git.initial_branch' => 'main',
            'git.browser_max_blob_bytes' => 1_048_576,
        ]);

        DB::purge(
            'sqlite',
        );

        DB::reconnect(
            'sqlite',
        );

        Artisan::call(
            'migrate:fresh',
            [
                '--database' => 'sqlite',
                '--force' => true,
            ],
        );

        $this->serverPort = $this->findAvailablePort();

        $this->serverBaseUrl = sprintf(
            'http://127.0.0.1:%d',
            $this->serverPort,
        );

        $this->startServer();
    }

    protected function tearDown(): void
    {
        if (
            $this->serverProcess instanceof Process
            && $this->serverProcess->isRunning()
        ) {
            $this->serverProcess->stop(
                3,
            );
        }

        DB::purge(
            'sqlite',
        );

        if (
            isset($this->temporaryRoot)
            && File::isDirectory($this->temporaryRoot)
        ) {
            File::deleteDirectory(
                $this->temporaryRoot,
            );
        }

        parent::tearDown();
    }

    public function test_real_git_client_can_push_new_commit_with_valid_owner_pat(): void
    {
        [$repository, $owner] = $this->createManagedRepository();

        $repositoryPath = app(
            RepositoryStorageService::class,
        )->resolveExisting(
            $repository,
        );

        $initialCommit = $this->commitToMain(
            repositoryPath: $repositoryPath,
            content: "initial\n",
            message: 'Initial commit',
        );

        $workspace = $this->clonePublicRepository(
            repository: $repository,
            directoryName: 'successful-owner-push',
        );

        $this->assertSame(
            $initialCommit,
            $this->localHead(
                $workspace,
            ),
        );

        $newCommit = $this->commitInWorkspace(
            workspace: $workspace,
            content: "updated through smart http push\n",
            message: 'Smart HTTP write E2E commit',
        );

        [$plainTextToken] = $this->createGitToken(
            $owner,
        );

        $push = $this->pushMain(
            workspace: $workspace,
            authorizationConfig: $this->authorizationConfig(
                username: $owner->username,
                token: $plainTextToken,
            ),
        );

        $this->assertProcessSucceeded(
            $push,
            'Real git push with a valid owner PAT failed.',
        );

        $this->assertSame(
            $newCommit,
            $this->remoteMain(
                $repositoryPath,
            ),
        );
    }

    public function test_successful_push_is_visible_through_repository_browser(): void
    {
        [$repository, $owner] = $this->createManagedRepository();

        $repositoryPath = app(
            RepositoryStorageService::class,
        )->resolveExisting(
            $repository,
        );

        $this->commitToMain(
            repositoryPath: $repositoryPath,
            content: "initial\n",
            message: 'Initial commit',
        );

        $workspace = $this->clonePublicRepository(
            repository: $repository,
            directoryName: 'browser-consistency',
        );

        $newContent = "visible through repository browser\n";

        $newCommit = $this->commitInWorkspace(
            workspace: $workspace,
            content: $newContent,
            message: 'Browser-visible pushed commit',
        );

        [$plainTextToken] = $this->createGitToken(
            $owner,
        );

        $push = $this->pushMain(
            workspace: $workspace,
            authorizationConfig: $this->authorizationConfig(
                username: $owner->username,
                token: $plainTextToken,
            ),
        );

        $this->assertProcessSucceeded(
            $push,
            'Push failed before repository-browser verification.',
        );

        $this->assertSame(
            $newCommit,
            $this->remoteMain(
                $repositoryPath,
            ),
        );

        $this
            ->actingAs(
                $owner,
                'web',
            )
            ->getJson(
                route(
                    'api.v1.repositories.commits',
                    [
                        'repository' => $repository,
                        'branch' => 'main',
                        'limit' => 10,
                    ],
                ),
            )
            ->assertOk()
            ->assertJsonPath(
                'branch',
                'main',
            )
            ->assertJsonPath(
                'data.0.hash',
                $newCommit,
            )
            ->assertJsonPath(
                'data.0.subject',
                'Browser-visible pushed commit',
            );

        $this
            ->actingAs(
                $owner,
                'web',
            )
            ->getJson(
                route(
                    'api.v1.repositories.commit',
                    [
                        'repository' => $repository,
                        'commit' => $newCommit,
                    ],
                ),
            )
            ->assertOk()
            ->assertJsonPath(
                'commit.id',
                $newCommit,
            )
            ->assertJsonPath(
                'commit.message',
                'Browser-visible pushed commit',
            );

        $this
            ->actingAs(
                $owner,
                'web',
            )
            ->getJson(
                route(
                    'api.v1.repositories.tree',
                    [
                        'repository' => $repository,
                        'branch' => 'main',
                    ],
                ),
            )
            ->assertOk()
            ->assertJsonPath(
                'branch',
                'main',
            )
            ->assertJsonPath(
                'data.0.name',
                'README.md',
            )
            ->assertJsonPath(
                'data.0.type',
                'file',
            );

        $this
            ->actingAs(
                $owner,
                'web',
            )
            ->getJson(
                route(
                    'api.v1.repositories.blob',
                    [
                        'repository' => $repository,
                        'branch' => 'main',
                        'path' => 'README.md',
                    ],
                ),
            )
            ->assertOk()
            ->assertJsonPath(
                'branch',
                'main',
            )
            ->assertJsonPath(
                'data.path',
                'README.md',
            )
            ->assertJsonPath(
                'data.encoding',
                'utf-8',
            )
            ->assertJsonPath(
                'data.content',
                $newContent,
            );
    }

    public function test_real_git_client_rejects_push_without_credentials(): void
    {
        [$repository] = $this->createManagedRepository();

        $repositoryPath = app(
            RepositoryStorageService::class,
        )->resolveExisting(
            $repository,
        );

        $this->commitToMain(
            repositoryPath: $repositoryPath,
            content: "initial\n",
            message: 'Initial commit',
        );

        $workspace = $this->clonePublicRepository(
            repository: $repository,
            directoryName: 'push-without-credentials',
        );

        $this->commitInWorkspace(
            workspace: $workspace,
            content: "unauthorized change\n",
            message: 'Unauthorized push attempt',
        );

        $refsBefore = $this->showRefs(
            $repositoryPath,
        );

        $push = $this->pushMain(
            workspace: $workspace,
        );

        $this->assertFalse(
            $push->isSuccessful(),
            'Push unexpectedly succeeded without credentials.',
        );

        $this->assertSame(
            $refsBefore,
            $this->showRefs(
                $repositoryPath,
            ),
            'Repository refs changed after an unauthenticated push attempt.',
        );
    }

    public function test_real_git_client_rejects_push_with_invalid_pat(): void
    {
        [$repository, $owner] = $this->createManagedRepository();

        $repositoryPath = app(
            RepositoryStorageService::class,
        )->resolveExisting(
            $repository,
        );

        $this->commitToMain(
            repositoryPath: $repositoryPath,
            content: "initial\n",
            message: 'Initial commit',
        );

        $workspace = $this->clonePublicRepository(
            repository: $repository,
            directoryName: 'push-invalid-pat',
        );

        $this->commitInWorkspace(
            workspace: $workspace,
            content: "invalid PAT change\n",
            message: 'Invalid PAT push attempt',
        );

        $refsBefore = $this->showRefs(
            $repositoryPath,
        );

        $push = $this->pushMain(
            workspace: $workspace,
            authorizationConfig: $this->authorizationConfig(
                username: $owner->username,
                token: 'invalid-token',
            ),
        );

        $this->assertFalse(
            $push->isSuccessful(),
            'Push unexpectedly succeeded with an invalid PAT.',
        );

        $this->assertSame(
            $refsBefore,
            $this->showRefs(
                $repositoryPath,
            ),
            'Repository refs changed after an invalid PAT push attempt.',
        );
    }

    public function test_real_git_client_rejects_push_with_revoked_pat(): void
    {
        [$repository, $owner] = $this->createManagedRepository();

        $repositoryPath = app(
            RepositoryStorageService::class,
        )->resolveExisting(
            $repository,
        );

        $this->commitToMain(
            repositoryPath: $repositoryPath,
            content: "initial\n",
            message: 'Initial commit',
        );

        $workspace = $this->clonePublicRepository(
            repository: $repository,
            directoryName: 'push-revoked-pat',
        );

        $this->commitInWorkspace(
            workspace: $workspace,
            content: "revoked PAT change\n",
            message: 'Revoked PAT push attempt',
        );

        [
            $plainTextToken,
            $token,
        ] = $this->createGitToken(
            $owner,
        );

        $token->forceFill([
            'revoked_at' => now(),
        ])->save();

        $refsBefore = $this->showRefs(
            $repositoryPath,
        );

        $push = $this->pushMain(
            workspace: $workspace,
            authorizationConfig: $this->authorizationConfig(
                username: $owner->username,
                token: $plainTextToken,
            ),
        );

        $this->assertFalse(
            $push->isSuccessful(),
            'Push unexpectedly succeeded with a revoked PAT.',
        );

        $this->assertSame(
            $refsBefore,
            $this->showRefs(
                $repositoryPath,
            ),
            'Repository refs changed after a revoked PAT push attempt.',
        );
    }

    public function test_real_git_client_rejects_push_by_authenticated_non_owner(): void
    {
        [$repository] = $this->createManagedRepository();

        $repositoryPath = app(
            RepositoryStorageService::class,
        )->resolveExisting(
            $repository,
        );

        $this->commitToMain(
            repositoryPath: $repositoryPath,
            content: "initial\n",
            message: 'Initial commit',
        );

        $workspace = $this->clonePublicRepository(
            repository: $repository,
            directoryName: 'push-non-owner',
        );

        $this->commitInWorkspace(
            workspace: $workspace,
            content: "non-owner change\n",
            message: 'Non-owner push attempt',
        );

        $otherUser = User::factory()->create([
            'username' => 'other-user',
        ]);

        [$plainTextToken] = $this->createGitToken(
            $otherUser,
        );

        $refsBefore = $this->showRefs(
            $repositoryPath,
        );

        $push = $this->pushMain(
            workspace: $workspace,
            authorizationConfig: $this->authorizationConfig(
                username: $otherUser->username,
                token: $plainTextToken,
            ),
        );

        $this->assertFalse(
            $push->isSuccessful(),
            'Push unexpectedly succeeded for an authenticated non-owner.',
        );

        $this->assertSame(
            $refsBefore,
            $this->showRefs(
                $repositoryPath,
            ),
            'Repository refs changed after a non-owner push attempt.',
        );
    }

    /**
     * Create repository metadata and initialize real managed bare storage.
     *
     * @return array{Repository, User}
     */
    private function createManagedRepository(): array
    {
        $owner = User::factory()->create([
            'username' => 'farhad',
        ]);

        $repository = Repository::factory()->create([
            'owner_id' => $owner->id,
            'name' => 'project',
            'visibility' => RepositoryVisibility::PublicAccess,
        ]);

        app(
            RepositoryStorageService::class,
        )->initialize(
            $repository,
        );

        return [
            $repository->load(
                'owner',
            ),
            $owner,
        ];
    }

    /**
     * Create a GitForge PAT and return its plaintext and persisted model.
     *
     * @return array{string, PersonalAccessToken}
     */
    private function createGitToken(
        User $user,
    ): array {
        $identifier = bin2hex(
            random_bytes(16),
        );

        $secret = bin2hex(
            random_bytes(32),
        );

        $plainTextToken = sprintf(
            'gitforge_pat_%s_%s',
            $identifier,
            $secret,
        );

        $token = $user
            ->personalAccessTokens()
            ->create([
                'name' => 'Git write E2E client',
                'token_identifier' => $identifier,
                'token_hash' => hash(
                    'sha256',
                    $plainTextToken,
                ),
                'revoked_at' => null,
            ]);

        return [
            $plainTextToken,
            $token,
        ];
    }

    /**
     * Create or advance refs/heads/main and return the new commit ID.
     */
    private function commitToMain(
        string $repositoryPath,
        string $content,
        string $message,
        ?string $parentCommit = null,
    ): string {
        $git = app(
            GitCommandRunner::class,
        );

        $blob = $git->run(
            operation: 'test_write_e2e_seed_blob',
            arguments: [
                '--git-dir='.$repositoryPath,
                'hash-object',
                '-w',
                '--stdin',
            ],
            input: $content,
        );

        $tree = $git->run(
            operation: 'test_write_e2e_seed_tree',
            arguments: [
                '--git-dir='.$repositoryPath,
                'mktree',
            ],
            input: "100644 blob {$blob}\tREADME.md\n",
        );

        $arguments = [
            '-c',
            'user.name=GitForge Write E2E Test',
            '-c',
            'user.email=gitforge-write-e2e@example.test',
            '--git-dir='.$repositoryPath,
            'commit-tree',
            $tree,
        ];

        if ($parentCommit !== null) {
            $arguments[] = '-p';
            $arguments[] = $parentCommit;
        }

        $arguments[] = '-m';
        $arguments[] = $message;

        $commit = $git->run(
            operation: 'test_write_e2e_seed_commit',
            arguments: $arguments,
        );

        $git->run(
            operation: 'test_write_e2e_update_main',
            arguments: [
                '--git-dir='.$repositoryPath,
                'update-ref',
                'refs/heads/main',
                $commit,
            ],
        );

        return $commit;
    }

    /**
     * Clone the public repository into an isolated local workspace.
     */
    private function clonePublicRepository(
        Repository $repository,
        string $directoryName,
    ): string {
        $workspace = $this->temporaryWorkspaceRoot
            .DIRECTORY_SEPARATOR
            .$directoryName;

        $clone = $this->runGit(
            [
                '-c',
                'credential.helper=',
                'clone',
                $this->repositoryUrl(
                    $repository,
                ),
                $workspace,
            ],
        );

        $this->assertProcessSucceeded(
            $clone,
            'Unable to create local clone before write testing.',
        );

        return $workspace;
    }

    /**
     * Create a new commit in a local test workspace.
     */
    private function commitInWorkspace(
        string $workspace,
        string $content,
        string $message,
    ): string {
        File::put(
            $workspace
            .DIRECTORY_SEPARATOR
            .'README.md',
            $content,
        );

        $add = $this->runGit(
            [
                '-C',
                $workspace,
                'add',
                'README.md',
            ],
        );

        $this->assertProcessSucceeded(
            $add,
            'Unable to stage README.md in the local write workspace.',
        );

        $commit = $this->runGit(
            [
                '-C',
                $workspace,
                '-c',
                'user.name=GitForge Write E2E Client',
                '-c',
                'user.email=gitforge-write-client@example.test',
                'commit',
                '-m',
                $message,
            ],
        );

        $this->assertProcessSucceeded(
            $commit,
            'Unable to create the local commit used for write testing.',
        );

        return $this->localHead(
            $workspace,
        );
    }

    /**
     * Push local main through Git Smart HTTP.
     */
    private function pushMain(
        string $workspace,
        ?string $authorizationConfig = null,
    ): Process {
        $arguments = [
            '-C',
            $workspace,
            '-c',
            'credential.helper=',
        ];

        if ($authorizationConfig !== null) {
            $arguments[] = '-c';
            $arguments[] = $authorizationConfig;
        }

        $arguments[] = 'push';
        $arguments[] = 'origin';
        $arguments[] = 'main';

        return $this->runGit(
            $arguments,
        );
    }

    /**
     * Resolve the current local HEAD commit.
     */
    private function localHead(
        string $workspace,
    ): string {
        $process = $this->runGit(
            [
                '-C',
                $workspace,
                'rev-parse',
                'HEAD',
            ],
        );

        $this->assertProcessSucceeded(
            $process,
            'Unable to resolve local HEAD.',
        );

        return trim(
            $process->getOutput(),
        );
    }

    /**
     * Resolve refs/heads/main directly from managed bare storage.
     */
    private function remoteMain(
        string $repositoryPath,
    ): string {
        $process = $this->runGit(
            [
                '--git-dir='.$repositoryPath,
                'rev-parse',
                'refs/heads/main',
            ],
        );

        $this->assertProcessSucceeded(
            $process,
            'Unable to resolve remote refs/heads/main.',
        );

        return trim(
            $process->getOutput(),
        );
    }

    /**
     * Return the complete ref snapshot for repository-integrity assertions.
     */
    private function showRefs(
        string $repositoryPath,
    ): string {
        $process = $this->runGit(
            [
                '--git-dir='.$repositoryPath,
                'show-ref',
            ],
        );

        if (
            ! $process->isSuccessful()
            && $process->getExitCode() !== 1
        ) {
            $this->fail(
                'Unable to inspect repository refs.'
                .PHP_EOL
                .'Exit code: '
                .$process->getExitCode()
                .PHP_EOL
                .'STDOUT:'
                .PHP_EOL
                .$process->getOutput()
                .PHP_EOL
                .'STDERR:'
                .PHP_EOL
                .$process->getErrorOutput(),
            );
        }

        return $process->getOutput();
    }

    /**
     * Build the Smart HTTP repository URL used by a real Git client.
     */
    private function repositoryUrl(
        Repository $repository,
    ): string {
        return sprintf(
            '%s/%s/%s.git',
            $this->serverBaseUrl,
            $repository->owner->username,
            $repository->name,
        );
    }

    /**
     * Build a Git http.extraHeader configuration value.
     */
    private function authorizationConfig(
        string $username,
        string $token,
    ): string {
        return 'http.extraHeader=Authorization: Basic '
            .base64_encode(
                "{$username}:{$token}",
            );
    }

    /**
     * Execute the real system Git client without interactive credential prompts.
     *
     * @param  array<int, string>  $arguments
     */
    private function runGit(
        array $arguments,
    ): Process {
        $environment = array_merge(
            $this->currentEnvironment(),
            [
                'GIT_TERMINAL_PROMPT' => '0',
            ],
        );

        $process = new Process(
            command: [
                'git',
                ...$arguments,
            ],
            cwd: base_path(),
            env: $environment,
        );

        $process->setTimeout(
            45,
        );

        $process->run();

        return $process;
    }

    /**
     * Assert that a real Git process completed successfully.
     */
    private function assertProcessSucceeded(
        Process $process,
        string $message,
    ): void {
        $this->assertTrue(
            $process->isSuccessful(),
            $message
            .PHP_EOL
            .'Exit code: '
            .$process->getExitCode()
            .PHP_EOL
            .'STDOUT:'
            .PHP_EOL
            .$process->getOutput()
            .PHP_EOL
            .'STDERR:'
            .PHP_EOL
            .$process->getErrorOutput(),
        );
    }

    /**
     * Start an isolated Laravel HTTP server that shares the temporary database
     * and repository storage with the PHPUnit process.
     */
    private function startServer(): void
    {
        $environment = array_merge(
            $this->currentEnvironment(),
            [
                'APP_ENV' => 'testing',
                'APP_DEBUG' => 'false',
                'APP_CONFIG_CACHE' => (
                    $this->temporaryRoot
                    .DIRECTORY_SEPARATOR
                    .'config.php'
                ),
                'DB_CONNECTION' => 'sqlite',
                'DB_DATABASE' => $this->temporaryDatabase,
                'DB_FOREIGN_KEYS' => 'true',
                'CACHE_STORE' => 'array',
                'SESSION_DRIVER' => 'array',
                'QUEUE_CONNECTION' => 'sync',
                'GITFORGE_GIT_BINARY' => 'git',
                'GIT_PROCESS_TIMEOUT_SECONDS' => '30',
                'GIT_MAX_REQUEST_BYTES' => '104857600',
                'GIT_BROWSER_MAX_BLOB_BYTES' => '1048576',
                'GITFORGE_REPOSITORY_ROOT' => (
                    $this->temporaryStorageRoot
                ),
                'GIT_INITIAL_BRANCH' => 'main',
            ],
        );

        $this->serverProcess = new Process(
            command: [
                PHP_BINARY,
                'artisan',
                'serve',
                '--host=127.0.0.1',
                '--port='.$this->serverPort,
            ],
            cwd: base_path(),
            env: $environment,
        );

        $this->serverProcess->setTimeout(
            null,
        );

        $this->serverProcess->start();

        $this->waitForServer();
    }

    /**
     * Wait until the temporary Laravel server accepts TCP connections.
     */
    private function waitForServer(): void
    {
        $deadline = microtime(
            true,
        ) + 15;

        do {
            if (
                $this->serverProcess instanceof Process
                && ! $this->serverProcess->isRunning()
            ) {
                $this->fail(
                    'Temporary Laravel server stopped during startup.'
                    .PHP_EOL
                    .$this->serverProcess->getErrorOutput()
                    .PHP_EOL
                    .$this->serverProcess->getOutput(),
                );
            }

            $connection = @fsockopen(
                '127.0.0.1',
                $this->serverPort,
                $errorCode,
                $errorMessage,
                0.2,
            );

            if (is_resource($connection)) {
                fclose(
                    $connection,
                );

                return;
            }

            usleep(
                100_000,
            );
        } while (
            microtime(
                true,
            ) < $deadline
        );

        $this->fail(
            sprintf(
                'Temporary Laravel server did not start on port %d.',
                $this->serverPort,
            )
            .PHP_EOL
            .'Error: '
            .$errorCode
            .' '
            .$errorMessage,
        );
    }

    /**
     * Reserve an available localhost TCP port for the temporary server.
     */
    private function findAvailablePort(): int
    {
        $socket = @stream_socket_server(
            'tcp://127.0.0.1:0',
            $errorCode,
            $errorMessage,
        );

        if ($socket === false) {
            $this->fail(
                'Unable to allocate a temporary HTTP port: '
                .$errorCode
                .' '
                .$errorMessage,
            );
        }

        $address = stream_socket_get_name(
            $socket,
            false,
        );

        fclose(
            $socket,
        );

        if (
            ! is_string(
                $address,
            )
            || ! str_contains(
                $address,
                ':',
            )
        ) {
            $this->fail(
                'Unable to determine the temporary HTTP port.',
            );
        }

        $port = (int) substr(
            $address,
            strrpos(
                $address,
                ':',
            ) + 1,
        );

        if ($port < 1) {
            $this->fail(
                'The allocated temporary HTTP port is invalid.',
            );
        }

        return $port;
    }

    /**
     * Return the current process environment as string values.
     *
     * @return array<string, string>
     */
    private function currentEnvironment(): array
    {
        $environment = getenv();

        if (! is_array($environment)) {
            return [];
        }

        return array_filter(
            $environment,
            static fn ($value): bool => is_string(
                $value,
            ),
        );
    }
}
