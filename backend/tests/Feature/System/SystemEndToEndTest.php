<?php

namespace Tests\Feature\System;

use App\Enums\RepositoryVisibility;
use App\Models\PersonalAccessToken;
use App\Models\Repository;
use App\Models\User;
use App\Services\Git\RepositoryStorageService;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Symfony\Component\Process\Process;
use Tests\TestCase;

class SystemEndToEndTest extends TestCase
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
            .'gitforge-system-e2e-'
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

    /**
     * TC-E2E-001
     *
     * Registration -> authenticated session -> repository creation ->
     * metadata/storage verification -> repository retrieval.
     */
    public function test_registration_to_repository_creation_flow(): void
    {
        $register = $this->postJson(
            route('api.v1.auth.register'),
            [
                'name' => 'System E2E Owner',
                'username' => 'system-owner',
                'email' => 'system-owner@example.test',
                'password' => 'StrongPassword1!',
                'password_confirmation' => 'StrongPassword1!',
            ],
        );

        $register
            ->assertCreated()
            ->assertJson([
                'message' => 'Registration successful.',
                'user' => [
                    'name' => 'System E2E Owner',
                    'username' => 'system-owner',
                    'email' => 'system-owner@example.test',
                ],
            ]);

        $owner = User::query()
            ->where(
                'username',
                'system-owner',
            )
            ->firstOrFail();

        $this->assertAuthenticatedAs(
            $owner,
            'web',
        );

        $this
            ->getJson(
                route('api.v1.auth.me'),
            )
            ->assertOk()
            ->assertJsonPath(
                'user.id',
                $owner->id,
            );

        $create = $this->postJson(
            route('api.v1.repositories.store'),
            [
                'name' => 'First-Repository',
                'visibility' => 'private',
            ],
        );

        $create
            ->assertCreated()
            ->assertJson([
                'message' => 'Repository created successfully.',
                'repository' => [
                    'name' => 'first-repository',
                    'full_name' => 'system-owner/first-repository',
                    'visibility' => 'private',
                ],
            ]);

        $repository = Repository::query()
            ->where(
                'owner_id',
                $owner->id,
            )
            ->where(
                'name',
                'first-repository',
            )
            ->firstOrFail();

        $this->assertSame(
            RepositoryVisibility::PrivateAccess,
            $repository->visibility,
        );

        $this->assertTrue(
            app(
                RepositoryStorageService::class,
            )->exists(
                $repository,
            ),
        );

        $this
            ->getJson(
                route(
                    'api.v1.repositories.show',
                    $repository,
                ),
            )
            ->assertOk()
            ->assertJsonPath(
                'data.id',
                $repository->id,
            )
            ->assertJsonPath(
                'data.full_name',
                'system-owner/first-repository',
            )
            ->assertJsonPath(
                'data.visibility',
                'private',
            );
    }

    /**
     * TC-E2E-002
     *
     * Public repository creation -> PAT creation -> real Git clone/push ->
     * repository browser verification -> anonymous clone.
     */
    public function test_public_repository_create_push_browse_clone_flow(): void
    {
        $owner = User::factory()->create([
            'username' => 'public-e2e-owner',
        ]);

        $repository = $this->createRepositoryViaApi(
            owner: $owner,
            name: 'public-e2e-project',
            visibility: 'public',
        );

        [
            $plainTextToken,
        ] = $this->createPatViaApi(
            user: $owner,
            name: 'Public E2E Git client',
        );

        $workspace = $this->cloneRepositorySuccessfully(
            repository: $repository,
            directoryName: 'public-owner-workspace',
        );

        $content = "public system e2e content\n";

        $commit = $this->commitInWorkspace(
            workspace: $workspace,
            content: $content,
            message: 'Public system E2E initial commit',
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
            'Public repository push failed.',
        );

        $repositoryPath = app(
            RepositoryStorageService::class,
        )->resolveExisting(
            $repository,
        );

        $this->assertSame(
            $commit,
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
                'data.0.hash',
                $commit,
            )
            ->assertJsonPath(
                'data.0.subject',
                'Public system E2E initial commit',
            );

        $anonymousClone = $this->cloneRepositorySuccessfully(
            repository: $repository,
            directoryName: 'public-anonymous-clone',
        );

        $this->assertSame(
            $content,
            $this->fileAtHead(
                workspace: $anonymousClone,
                path: 'README.md',
            ),
        );

        $this->assertSame(
            $commit,
            $this->localHead(
                $anonymousClone,
            ),
        );
    }

    /**
     * TC-E2E-003
     *
     * Private repository creation -> PAT creation -> authenticated push ->
     * authenticated browser access -> authenticated clone.
     */
    public function test_private_repository_owner_workflow(): void
    {
        $owner = User::factory()->create([
            'username' => 'private-e2e-owner',
        ]);

        $repository = $this->createRepositoryViaApi(
            owner: $owner,
            name: 'private-e2e-project',
            visibility: 'private',
        );

        [
            $plainTextToken,
        ] = $this->createPatViaApi(
            user: $owner,
            name: 'Private E2E Git client',
        );

        $authorizationConfig = $this->authorizationConfig(
            username: $owner->username,
            token: $plainTextToken,
        );

        $workspace = $this->cloneRepositorySuccessfully(
            repository: $repository,
            directoryName: 'private-owner-workspace',
            authorizationConfig: $authorizationConfig,
        );

        $content = "private owner system e2e content\n";

        $commit = $this->commitInWorkspace(
            workspace: $workspace,
            content: $content,
            message: 'Private owner system E2E commit',
        );

        $push = $this->pushMain(
            workspace: $workspace,
            authorizationConfig: $authorizationConfig,
        );

        $this->assertProcessSucceeded(
            $push,
            'Private repository owner push failed.',
        );

        $this
            ->actingAs(
                $owner,
                'web',
            )
            ->getJson(
                route(
                    'api.v1.repositories.show',
                    $repository,
                ),
            )
            ->assertOk()
            ->assertJsonPath(
                'data.id',
                $repository->id,
            )
            ->assertJsonPath(
                'data.visibility',
                'private',
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
                'data.content',
                $content,
            );

        $clone = $this->cloneRepositorySuccessfully(
            repository: $repository,
            directoryName: 'private-owner-verification-clone',
            authorizationConfig: $authorizationConfig,
        );

        $this->assertSame(
            $commit,
            $this->localHead(
                $clone,
            ),
        );

        $this->assertSame(
            $content,
            $this->fileAtHead(
                workspace: $clone,
                path: 'README.md',
            ),
        );
    }

    /**
     * TC-E2E-004
     *
     * A second authenticated user attempts metadata read, repository-browser
     * access, clone, and push against another user's private repository.
     */
    public function test_private_repository_unauthorized_user_workflow(): void
    {
        $owner = User::factory()->create([
            'username' => 'private-resource-owner',
        ]);

        $repository = $this->createRepositoryViaApi(
            owner: $owner,
            name: 'concealed-private-project',
            visibility: 'private',
        );

        [
            $ownerToken,
        ] = $this->createPatViaApi(
            user: $owner,
            name: 'Private repository seed client',
        );

        $ownerWorkspace = $this->cloneRepositorySuccessfully(
            repository: $repository,
            directoryName: 'private-resource-owner-workspace',
            authorizationConfig: $this->authorizationConfig(
                username: $owner->username,
                token: $ownerToken,
            ),
        );

        $initialContent = "private protected content\n";

        $initialCommit = $this->commitInWorkspace(
            workspace: $ownerWorkspace,
            content: $initialContent,
            message: 'Private protected initial commit',
        );

        $ownerPush = $this->pushMain(
            workspace: $ownerWorkspace,
            authorizationConfig: $this->authorizationConfig(
                username: $owner->username,
                token: $ownerToken,
            ),
        );

        $this->assertProcessSucceeded(
            $ownerPush,
            'Unable to seed the private repository.',
        );

        $otherUser = User::factory()->create([
            'username' => 'unauthorized-user',
        ]);

        [
            $otherToken,
        ] = $this->createPatViaApi(
            user: $otherUser,
            name: 'Unauthorized E2E client',
        );

        $this
            ->actingAs(
                $otherUser,
                'web',
            )
            ->getJson(
                route(
                    'api.v1.repositories.show',
                    $repository,
                ),
            )
            ->assertNotFound();

        $this
            ->actingAs(
                $otherUser,
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
            ->assertNotFound();

        $unauthorizedClonePath = $this->temporaryWorkspaceRoot
            .DIRECTORY_SEPARATOR
            .'unauthorized-private-clone';

        $clone = $this->runGit(
            [
                '-c',
                'credential.helper=',
                '-c',
                $this->authorizationConfig(
                    username: $otherUser->username,
                    token: $otherToken,
                ),
                'clone',
                $this->repositoryUrl(
                    $repository,
                ),
                $unauthorizedClonePath,
            ],
        );

        $this->assertFalse(
            $clone->isSuccessful(),
            'A non-owner unexpectedly cloned a private repository.',
        );

        $this->assertFalse(
            File::isDirectory(
                $unauthorizedClonePath
                .DIRECTORY_SEPARATOR
                .'.git',
            ),
            'A usable private clone was created for a non-owner.',
        );

        $repositoryPath = app(
            RepositoryStorageService::class,
        )->resolveExisting(
            $repository,
        );

        $refsBefore = $this->showRefs(
            $repositoryPath,
        );

        $unauthorizedWorkspace = $this->createStandaloneWorkspace(
            directoryName: 'unauthorized-private-push',
            content: "unauthorized private change\n",
            message: 'Unauthorized private push attempt',
            remoteUrl: $this->repositoryUrl(
                $repository,
            ),
        );

        $push = $this->pushMain(
            workspace: $unauthorizedWorkspace,
            authorizationConfig: $this->authorizationConfig(
                username: $otherUser->username,
                token: $otherToken,
            ),
        );

        $this->assertFalse(
            $push->isSuccessful(),
            'A non-owner unexpectedly pushed to a private repository.',
        );

        $this->assertSame(
            $refsBefore,
            $this->showRefs(
                $repositoryPath,
            ),
            'Private repository refs changed after an unauthorized push.',
        );

        $this->assertSame(
            $initialCommit,
            $this->remoteMain(
                $repositoryPath,
            ),
        );
    }

    /**
     * TC-E2E-005
     *
     * Create PAT -> prove real Git access -> revoke through API ->
     * repeat Git access using the same PAT.
     */
    public function test_pat_revocation_end_to_end(): void
    {
        $owner = User::factory()->create([
            'username' => 'revocation-owner',
        ]);

        $repository = $this->createRepositoryViaApi(
            owner: $owner,
            name: 'revocation-project',
            visibility: 'private',
        );

        [
            $plainTextToken,
            $token,
        ] = $this->createPatViaApi(
            user: $owner,
            name: 'Revocation E2E client',
        );

        $authorizationConfig = $this->authorizationConfig(
            username: $owner->username,
            token: $plainTextToken,
        );

        $workspace = $this->cloneRepositorySuccessfully(
            repository: $repository,
            directoryName: 'revocation-before',
            authorizationConfig: $authorizationConfig,
        );

        $content = "token valid before revocation\n";

        $commit = $this->commitInWorkspace(
            workspace: $workspace,
            content: $content,
            message: 'PAT revocation E2E seed commit',
        );

        $push = $this->pushMain(
            workspace: $workspace,
            authorizationConfig: $authorizationConfig,
        );

        $this->assertProcessSucceeded(
            $push,
            'Git access failed before PAT revocation.',
        );

        $this->assertSame(
            $commit,
            $this->localHead(
                $workspace,
            ),
        );

        $this
            ->actingAs(
                $owner,
                'web',
            )
            ->deleteJson(
                route(
                    'api.v1.personal-access-tokens.destroy',
                    [
                        'token' => $token->id,
                    ],
                ),
            )
            ->assertOk()
            ->assertJson([
                'message' => (
                    'Personal access token revoked successfully.'
                ),
            ]);

        $token->refresh();

        $this->assertNotNull(
            $token->revoked_at,
        );

        $fetch = $this->runGit(
            [
                '-C',
                $workspace,
                '-c',
                'credential.helper=',
                '-c',
                $authorizationConfig,
                'fetch',
                'origin',
            ],
        );

        $this->assertFalse(
            $fetch->isSuccessful(),
            'Git access unexpectedly remained valid after PAT revocation.',
        );

        $revokedClonePath = $this->temporaryWorkspaceRoot
            .DIRECTORY_SEPARATOR
            .'revocation-after';

        $revokedClone = $this->runGit(
            [
                '-c',
                'credential.helper=',
                '-c',
                $authorizationConfig,
                'clone',
                $this->repositoryUrl(
                    $repository,
                ),
                $revokedClonePath,
            ],
        );

        $this->assertFalse(
            $revokedClone->isSuccessful(),
            'Private clone unexpectedly succeeded after PAT revocation.',
        );

        $this->assertFalse(
            File::isDirectory(
                $revokedClonePath
                .DIRECTORY_SEPARATOR
                .'.git',
            ),
            'A usable clone was created with a revoked PAT.',
        );
    }

    /**
     * TC-E2E-006
     *
     * Push a new commit and verify that exactly the same state is exposed by
     * commit history, commit detail, tree, and blob browser interfaces.
     */
    public function test_push_to_browser_consistency(): void
    {
        $owner = User::factory()->create([
            'username' => 'consistency-owner',
        ]);

        $repository = $this->createRepositoryViaApi(
            owner: $owner,
            name: 'consistency-project',
            visibility: 'public',
        );

        [
            $plainTextToken,
        ] = $this->createPatViaApi(
            user: $owner,
            name: 'Consistency E2E client',
        );

        $workspace = $this->cloneRepositorySuccessfully(
            repository: $repository,
            directoryName: 'consistency-workspace',
        );

        $content = "push to browser consistency\n";
        $message = 'Push-to-browser consistency commit';

        $commit = $this->commitInWorkspace(
            workspace: $workspace,
            content: $content,
            message: $message,
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
            'Push failed before consistency verification.',
        );

        $repositoryPath = app(
            RepositoryStorageService::class,
        )->resolveExisting(
            $repository,
        );

        $this->assertSame(
            $commit,
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
                $commit,
            )
            ->assertJsonPath(
                'data.0.subject',
                $message,
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
                        'commit' => $commit,
                    ],
                ),
            )
            ->assertOk()
            ->assertJsonPath(
                'commit.id',
                $commit,
            )
            ->assertJsonPath(
                'commit.message',
                $message,
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
                $content,
            );
    }

    /**
     * TC-E2E-007
     *
     * Login -> protected access -> logout -> protected access again.
     */
    public function test_logout_terminates_browser_session_access(): void
    {
        $user = User::factory()->create([
            'username' => 'logout-e2e-user',
            'email' => 'logout-e2e@example.test',
            'password' => 'StrongPassword1!',
        ]);

        $this
            ->postJson(
                route('api.v1.auth.login'),
                [
                    'email' => 'logout-e2e@example.test',
                    'password' => 'StrongPassword1!',
                ],
            )
            ->assertOk()
            ->assertJson([
                'message' => 'Login successful.',
                'user' => [
                    'id' => $user->id,
                    'email' => 'logout-e2e@example.test',
                ],
            ]);

        $this->assertAuthenticatedAs(
            $user,
            'web',
        );

        $this
            ->getJson(
                route('api.v1.auth.me'),
            )
            ->assertOk()
            ->assertJsonPath(
                'user.id',
                $user->id,
            );

        $this
            ->postJson(
                route('api.v1.auth.logout'),
            )
            ->assertOk()
            ->assertJson([
                'message' => 'Logout successful.',
            ]);

        $this->assertGuest(
            'web',
        );

        $this
            ->getJson(
                route('api.v1.auth.me'),
            )
            ->assertUnauthorized();

        $this
            ->postJson(
                route('api.v1.repositories.store'),
                [
                    'name' => 'must-not-be-created',
                    'visibility' => 'private',
                ],
            )
            ->assertUnauthorized();

        $this->assertDatabaseMissing(
            'repositories',
            [
                'name' => 'must-not-be-created',
            ],
        );
    }

    /**
     * Create a repository through the V1 API and return its persisted model.
     */
    private function createRepositoryViaApi(
        User $owner,
        string $name,
        string $visibility,
    ): Repository {
        $response = $this
            ->actingAs(
                $owner,
                'web',
            )
            ->postJson(
                route('api.v1.repositories.store'),
                [
                    'name' => $name,
                    'visibility' => $visibility,
                ],
            );

        $response
            ->assertCreated()
            ->assertJson([
                'message' => 'Repository created successfully.',
                'repository' => [
                    'name' => $name,
                    'full_name' => $owner->username.'/'.$name,
                    'visibility' => $visibility,
                ],
            ]);

        $repository = Repository::query()
            ->where(
                'owner_id',
                $owner->id,
            )
            ->where(
                'name',
                $name,
            )
            ->firstOrFail();

        $this->assertTrue(
            app(
                RepositoryStorageService::class,
            )->exists(
                $repository,
            ),
            'Repository metadata was created without managed Git storage.',
        );

        return $repository->load(
            'owner',
        );
    }

    /**
     * Create a PAT through the V1 API.
     *
     * @return array{string, PersonalAccessToken}
     */
    private function createPatViaApi(
        User $user,
        string $name,
    ): array {
        $response = $this
            ->actingAs(
                $user,
                'web',
            )
            ->postJson(
                route('api.v1.personal-access-tokens.store'),
                [
                    'name' => $name,
                ],
            );

        $response
            ->assertCreated()
            ->assertJson([
                'message' => (
                    'Personal access token created successfully.'
                ),
                'token' => [
                    'name' => $name,
                    'revoked_at' => null,
                ],
            ])
            ->assertJsonStructure([
                'token' => [
                    'id',
                ],
                'plain_text_token',
            ]);

        $plainTextToken = $response->json(
            'plain_text_token',
        );

        $this->assertIsString(
            $plainTextToken,
        );

        $this->assertMatchesRegularExpression(
            '/\Agitforge_pat_[0-9a-f]{32}_[0-9a-f]{64}\z/',
            $plainTextToken,
        );

        $token = PersonalAccessToken::query()
            ->findOrFail(
                $response->json(
                    'token.id',
                ),
            );

        $this->assertSame(
            hash(
                'sha256',
                $plainTextToken,
            ),
            $token->token_hash,
        );

        $this->assertNull(
            $token->revoked_at,
        );

        return [
            $plainTextToken,
            $token,
        ];
    }

    /**
     * Clone a repository with a real Git client.
     */
    private function cloneRepositorySuccessfully(
        Repository $repository,
        string $directoryName,
        ?string $authorizationConfig = null,
    ): string {
        $workspace = $this->temporaryWorkspaceRoot
            .DIRECTORY_SEPARATOR
            .$directoryName;

        $arguments = [
            '-c',
            'credential.helper=',
        ];

        if ($authorizationConfig !== null) {
            $arguments[] = '-c';
            $arguments[] = $authorizationConfig;
        }

        $arguments[] = 'clone';
        $arguments[] = $this->repositoryUrl(
            $repository,
        );
        $arguments[] = $workspace;

        $clone = $this->runGit(
            $arguments,
        );

        $this->assertProcessSucceeded(
            $clone,
            'Real Git clone failed.',
        );

        $this->assertTrue(
            File::isDirectory(
                $workspace
                .DIRECTORY_SEPARATOR
                .'.git',
            ),
            'Successful Git clone did not create a working repository.',
        );

        return $workspace;
    }

    /**
     * Create a commit in an existing local Git workspace.
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
            'Unable to stage README.md in the E2E workspace.',
        );

        $commit = $this->runGit(
            [
                '-C',
                $workspace,
                '-c',
                'user.name=GitForge System E2E Client',
                '-c',
                'user.email=gitforge-system-e2e@example.test',
                'commit',
                '-m',
                $message,
            ],
        );

        $this->assertProcessSucceeded(
            $commit,
            'Unable to create the E2E workspace commit.',
        );

        return $this->localHead(
            $workspace,
        );
    }

    /**
     * Create an independent Git workspace for an attempted direct push.
     */
    private function createStandaloneWorkspace(
        string $directoryName,
        string $content,
        string $message,
        string $remoteUrl,
    ): string {
        $workspace = $this->temporaryWorkspaceRoot
            .DIRECTORY_SEPARATOR
            .$directoryName;

        $init = $this->runGit(
            [
                'init',
                '--initial-branch=main',
                $workspace,
            ],
        );

        $this->assertProcessSucceeded(
            $init,
            'Unable to initialize standalone E2E workspace.',
        );

        $this->commitInWorkspace(
            workspace: $workspace,
            content: $content,
            message: $message,
        );

        $remote = $this->runGit(
            [
                '-C',
                $workspace,
                'remote',
                'add',
                'origin',
                $remoteUrl,
            ],
        );

        $this->assertProcessSucceeded(
            $remote,
            'Unable to configure standalone E2E remote.',
        );

        return $workspace;
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
        $arguments[] = 'HEAD:refs/heads/main';

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
     * Read a file directly from the current local Git object.
     */
    private function fileAtHead(
        string $workspace,
        string $path,
    ): string {
        $process = $this->runGit(
            [
                '-C',
                $workspace,
                'show',
                'HEAD:'.$path,
            ],
        );

        $this->assertProcessSucceeded(
            $process,
            'Unable to read a file from the cloned Git object.',
        );

        return $process->getOutput();
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
     * Return the complete ref snapshot for integrity assertions.
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
     * Build the Git HTTP Basic Authorization header configuration.
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
     * and managed repository storage with the PHPUnit process.
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
