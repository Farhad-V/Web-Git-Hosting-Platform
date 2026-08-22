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

class GitSmartHttpReadEndToEndTest extends TestCase
{
    private string $temporaryRoot;

    private string $temporaryDatabase;

    private string $temporaryStorageRoot;

    private string $temporaryCloneRoot;

    private int $serverPort;

    private string $serverBaseUrl;

    private ?Process $serverProcess = null;

    protected function setUp(): void
    {
        parent::setUp();

        $this->temporaryRoot = sys_get_temp_dir()
            .DIRECTORY_SEPARATOR
            .'git-smart-http-read-e2e-'
            .Str::uuid();

        $this->temporaryDatabase = $this->temporaryRoot
            .DIRECTORY_SEPARATOR
            .'testing.sqlite';

        $this->temporaryStorageRoot = $this->temporaryRoot
            .DIRECTORY_SEPARATOR
            .'git-storage';

        $this->temporaryCloneRoot = $this->temporaryRoot
            .DIRECTORY_SEPARATOR
            .'clones';

        File::ensureDirectoryExists(
            $this->temporaryRoot,
        );

        File::ensureDirectoryExists(
            $this->temporaryStorageRoot,
        );

        File::ensureDirectoryExists(
            $this->temporaryCloneRoot,
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

    public function test_real_git_client_can_clone_public_repository(): void
    {
        [$repository] = $this->createManagedRepository(
            visibility: RepositoryVisibility::PublicAccess,
        );

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

        $clonePath = $this->temporaryCloneRoot
            .DIRECTORY_SEPARATOR
            .'public-clone';

        $process = $this->runGit(
            [
                '-c',
                'credential.helper=',
                'clone',
                $this->repositoryUrl(
                    $repository,
                ),
                $clonePath,
            ],
        );

        $this->assertProcessSucceeded(
            $process,
            'Real public git clone failed.',
        );

        $this->assertFileExists(
            $clonePath
            .DIRECTORY_SEPARATOR
            .'README.md',
        );

        $clonedContent = $this->runGit(
            [
                '-C',
                $clonePath,
                'show',
                'HEAD:README.md',
            ],
        );

        $this->assertProcessSucceeded(
            $clonedContent,
            'Unable to read README.md from the cloned Git object.',
        );

        $this->assertSame(
            "initial\n",
            $clonedContent->getOutput(),
        );

        $checkedOutBranch = $this->runGit(
            [
                '-C',
                $clonePath,
                'branch',
                '--show-current',
            ],
        );

        $this->assertProcessSucceeded(
            $checkedOutBranch,
            'Unable to determine the checked-out clone branch.',
        );

        $this->assertSame(
            'main',
            trim(
                $checkedOutBranch->getOutput(),
            ),
        );
    }

    public function test_real_git_client_can_fetch_new_public_commit(): void
    {
        [$repository] = $this->createManagedRepository(
            visibility: RepositoryVisibility::PublicAccess,
        );

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

        $clonePath = $this->temporaryCloneRoot
            .DIRECTORY_SEPARATOR
            .'public-fetch';

        $clone = $this->runGit(
            [
                '-c',
                'credential.helper=',
                'clone',
                $this->repositoryUrl(
                    $repository,
                ),
                $clonePath,
            ],
        );

        $this->assertProcessSucceeded(
            $clone,
            'Initial public clone failed before fetch verification.',
        );

        $newCommit = $this->commitToMain(
            repositoryPath: $repositoryPath,
            content: "updated\n",
            message: 'Second commit',
            parentCommit: $initialCommit,
        );

        $fetch = $this->runGit(
            [
                '-C',
                $clonePath,
                '-c',
                'credential.helper=',
                'fetch',
                'origin',
            ],
        );

        $this->assertProcessSucceeded(
            $fetch,
            'Real public git fetch failed.',
        );

        $remoteMain = $this->runGit(
            [
                '-C',
                $clonePath,
                'rev-parse',
                'refs/remotes/origin/main',
            ],
        );

        $this->assertProcessSucceeded(
            $remoteMain,
            'Unable to resolve fetched origin/main.',
        );

        $this->assertSame(
            $newCommit,
            trim(
                $remoteMain->getOutput(),
            ),
        );

        $fetchedContent = $this->runGit(
            [
                '-C',
                $clonePath,
                'show',
                'refs/remotes/origin/main:README.md',
            ],
        );

        $this->assertProcessSucceeded(
            $fetchedContent,
            'Unable to read content from the fetched commit.',
        );

        $this->assertSame(
            "updated\n",
            $fetchedContent->getOutput(),
        );
    }

    public function test_real_git_client_can_clone_private_repository_with_valid_owner_pat(): void
    {
        [$repository, $owner] = $this->createManagedRepository(
            visibility: RepositoryVisibility::PrivateAccess,
        );

        $repositoryPath = app(
            RepositoryStorageService::class,
        )->resolveExisting(
            $repository,
        );

        $this->commitToMain(
            repositoryPath: $repositoryPath,
            content: "private\n",
            message: 'Private initial commit',
        );

        [$plainTextToken] = $this->createGitToken(
            $owner,
        );

        $clonePath = $this->temporaryCloneRoot
            .DIRECTORY_SEPARATOR
            .'private-valid-pat';

        $process = $this->runGit(
            [
                '-c',
                'credential.helper=',
                '-c',
                $this->authorizationConfig(
                    username: $owner->username,
                    token: $plainTextToken,
                ),
                'clone',
                $this->repositoryUrl(
                    $repository,
                ),
                $clonePath,
            ],
        );

        $this->assertProcessSucceeded(
            $process,
            'Private clone with a valid owner PAT failed.',
        );

        $clonedContent = $this->runGit(
            [
                '-C',
                $clonePath,
                'show',
                'HEAD:README.md',
            ],
        );

        $this->assertProcessSucceeded(
            $clonedContent,
            'Unable to read README.md from the private cloned Git object.',
        );

        $this->assertSame(
            "private\n",
            $clonedContent->getOutput(),
        );
    }

    public function test_real_git_client_rejects_private_clone_without_credentials(): void
    {
        [$repository] = $this->createManagedRepository(
            visibility: RepositoryVisibility::PrivateAccess,
        );

        $repositoryPath = app(
            RepositoryStorageService::class,
        )->resolveExisting(
            $repository,
        );

        $this->commitToMain(
            repositoryPath: $repositoryPath,
            content: "private\n",
            message: 'Private initial commit',
        );

        $clonePath = $this->temporaryCloneRoot
            .DIRECTORY_SEPARATOR
            .'private-no-credentials';

        $process = $this->runGit(
            [
                '-c',
                'credential.helper=',
                'clone',
                $this->repositoryUrl(
                    $repository,
                ),
                $clonePath,
            ],
        );

        $this->assertFalse(
            $process->isSuccessful(),
            'Private clone unexpectedly succeeded without credentials.',
        );

        $this->assertFalse(
            File::isDirectory(
                $clonePath
                .DIRECTORY_SEPARATOR
                .'.git',
            ),
            'A usable Git clone was created without credentials.',
        );
    }

    public function test_real_git_client_rejects_private_clone_with_invalid_pat(): void
    {
        [$repository, $owner] = $this->createManagedRepository(
            visibility: RepositoryVisibility::PrivateAccess,
        );

        $repositoryPath = app(
            RepositoryStorageService::class,
        )->resolveExisting(
            $repository,
        );

        $this->commitToMain(
            repositoryPath: $repositoryPath,
            content: "private\n",
            message: 'Private initial commit',
        );

        $clonePath = $this->temporaryCloneRoot
            .DIRECTORY_SEPARATOR
            .'private-invalid-pat';

        $process = $this->runGit(
            [
                '-c',
                'credential.helper=',
                '-c',
                $this->authorizationConfig(
                    username: $owner->username,
                    token: 'invalid-token',
                ),
                'clone',
                $this->repositoryUrl(
                    $repository,
                ),
                $clonePath,
            ],
        );

        $this->assertFalse(
            $process->isSuccessful(),
            'Private clone unexpectedly succeeded with an invalid PAT.',
        );

        $this->assertFalse(
            File::isDirectory(
                $clonePath
                .DIRECTORY_SEPARATOR
                .'.git',
            ),
            'Repository content was transferred using an invalid PAT.',
        );
    }

    public function test_real_git_client_rejects_private_clone_with_revoked_pat(): void
    {
        [$repository, $owner] = $this->createManagedRepository(
            visibility: RepositoryVisibility::PrivateAccess,
        );

        $repositoryPath = app(
            RepositoryStorageService::class,
        )->resolveExisting(
            $repository,
        );

        $this->commitToMain(
            repositoryPath: $repositoryPath,
            content: "private\n",
            message: 'Private initial commit',
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

        $clonePath = $this->temporaryCloneRoot
            .DIRECTORY_SEPARATOR
            .'private-revoked-pat';

        $process = $this->runGit(
            [
                '-c',
                'credential.helper=',
                '-c',
                $this->authorizationConfig(
                    username: $owner->username,
                    token: $plainTextToken,
                ),
                'clone',
                $this->repositoryUrl(
                    $repository,
                ),
                $clonePath,
            ],
        );

        $this->assertFalse(
            $process->isSuccessful(),
            'Private clone unexpectedly succeeded with a revoked PAT.',
        );

        $this->assertFalse(
            File::isDirectory(
                $clonePath
                .DIRECTORY_SEPARATOR
                .'.git',
            ),
            'Repository content was transferred using a revoked PAT.',
        );
    }

    /**
     * Create repository metadata and initialize real managed bare storage.
     *
     * @return array{Repository, User}
     */
    private function createManagedRepository(
        RepositoryVisibility $visibility,
    ): array {
        $owner = User::factory()->create([
            'username' => 'farhad',
        ]);

        $repository = Repository::factory()->create([
            'owner_id' => $owner->id,
            'name' => 'project',
            'visibility' => $visibility,
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
                'name' => 'Git E2E client',
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
            operation: 'test_e2e_seed_blob',
            arguments: [
                '--git-dir='.$repositoryPath,
                'hash-object',
                '-w',
                '--stdin',
            ],
            input: $content,
        );

        $tree = $git->run(
            operation: 'test_e2e_seed_tree',
            arguments: [
                '--git-dir='.$repositoryPath,
                'mktree',
            ],
            input: "100644 blob {$blob}\tREADME.md\n",
        );

        $arguments = [
            '-c',
            'user.name=GitForge E2E Test',
            '-c',
            'user.email=gitforge-e2e@example.test',
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
            operation: 'test_e2e_seed_commit',
            arguments: $arguments,
        );

        $git->run(
            operation: 'test_e2e_update_main',
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
