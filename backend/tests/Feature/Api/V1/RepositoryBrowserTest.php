<?php

namespace Tests\Feature\Api\V1;

use App\Models\Repository;
use App\Models\User;
use App\Services\Git\GitCommandRunner;
use App\Services\Git\RepositoryStorageService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Tests\TestCase;

class RepositoryBrowserTest extends TestCase
{
    use RefreshDatabase;

    private string $temporaryRoot;

    private string $storageRoot;

    private string $worktreePath;

    protected function setUp(): void
    {
        parent::setUp();

        $this->temporaryRoot = sys_get_temp_dir()
            .DIRECTORY_SEPARATOR
            .'gitforge-browser-api-tests-'
            .Str::uuid();

        $this->storageRoot = $this->temporaryRoot
            .DIRECTORY_SEPARATOR
            .'storage';

        $this->worktreePath = $this->temporaryRoot
            .DIRECTORY_SEPARATOR
            .'worktree';

        config([
            'git.binary' => 'git',
            'git.timeout_seconds' => 10,
            'git.storage_root' => $this->storageRoot,
            'git.repositories_directory' => 'repositories',
            'git.initial_branch' => 'main',
            'git.browser_max_blob_bytes' => 1048576,
        ]);
    }

    protected function tearDown(): void
    {
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

    public function test_owner_can_list_repository_branches(): void
    {
        $owner = User::factory()->create();

        $repository = $this->createRepositoryWithHistory(
            owner: $owner,
        );

        $this
            ->actingAs(
                $owner,
                'web',
            )
            ->getJson(
                route(
                    'api.v1.repositories.branches',
                    $repository,
                ),
            )
            ->assertOk()
            ->assertJsonPath(
                'default_branch',
                'main',
            )
            ->assertJsonCount(
                1,
                'data',
            )
            ->assertJsonPath(
                'data.0.name',
                'main',
            )
            ->assertJsonPath(
                'data.0.subject',
                'Initial repository content',
            );
    }

    public function test_owner_can_list_repository_commits(): void
    {
        $owner = User::factory()->create();

        $repository = $this->createRepositoryWithHistory(
            owner: $owner,
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
            ->assertJsonCount(
                1,
                'data',
            )
            ->assertJsonPath(
                'data.0.subject',
                'Initial repository content',
            )
            ->assertJsonPath(
                'data.0.author.name',
                'Repository Browser API Test',
            );
    }

    public function test_owner_can_browse_repository_tree(): void
    {
        $owner = User::factory()->create();

        $repository = $this->createRepositoryWithHistory(
            owner: $owner,
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
                'path',
                '',
            )
            ->assertJsonCount(
                2,
                'data',
            )
            ->assertJsonPath(
                'data.0.name',
                'src',
            )
            ->assertJsonPath(
                'data.0.type',
                'directory',
            )
            ->assertJsonPath(
                'data.1.name',
                'README.md',
            )
            ->assertJsonPath(
                'data.1.type',
                'file',
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
                        'path' => 'src',
                    ],
                ),
            )
            ->assertOk()
            ->assertJsonPath(
                'path',
                'src',
            )
            ->assertJsonCount(
                1,
                'data',
            )
            ->assertJsonPath(
                'data.0.path',
                'src/app.php',
            );
    }

    public function test_owner_can_read_repository_blob(): void
    {
        $owner = User::factory()->create();

        $repository = $this->createRepositoryWithHistory(
            owner: $owner,
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
                "# Repository Browser\n\nAPI verification.\n",
            );
    }

    public function test_git_native_default_branch_is_used_when_branch_is_omitted(): void
    {
        $owner = User::factory()->create();

        $repository = $this->createRepositoryWithHistory(
            owner: $owner,
        );

        $this
            ->actingAs(
                $owner,
                'web',
            )
            ->getJson(
                route(
                    'api.v1.repositories.commits',
                    $repository,
                ),
            )
            ->assertOk()
            ->assertJsonPath(
                'branch',
                'main',
            );
    }

    public function test_empty_repository_returns_controlled_responses(): void
    {
        $owner = User::factory()->create();

        $repository = Repository::factory()->create([
            'owner_id' => $owner->id,
        ]);

        app(RepositoryStorageService::class)
            ->initialize($repository);

        $this
            ->actingAs(
                $owner,
                'web',
            )
            ->getJson(
                route(
                    'api.v1.repositories.branches',
                    $repository,
                ),
            )
            ->assertOk()
            ->assertJsonCount(
                0,
                'data',
            )
            ->assertJsonPath(
                'default_branch',
                null,
            );

        $this
            ->actingAs(
                $owner,
                'web',
            )
            ->getJson(
                route(
                    'api.v1.repositories.tree',
                    $repository,
                ),
            )
            ->assertConflict()
            ->assertJson([
                'message' => 'The repository is empty.',
                'error' => 'empty_repository',
            ]);

        $this
            ->actingAs(
                $owner,
                'web',
            )
            ->getJson(
                route(
                    'api.v1.repositories.commits',
                    $repository,
                ),
            )
            ->assertConflict()
            ->assertJsonPath(
                'error',
                'empty_repository',
            );
    }

    public function test_private_repository_cannot_be_browsed_by_another_user(): void
    {
        $owner = User::factory()->create();

        $viewer = User::factory()->create();

        $repository = $this->createRepositoryWithHistory(
            owner: $owner,
        );

        $this
            ->actingAs(
                $viewer,
                'web',
            )
            ->getJson(
                route(
                    'api.v1.repositories.branches',
                    $repository,
                ),
            )
            ->assertForbidden();

        $this
            ->actingAs(
                $viewer,
                'web',
            )
            ->getJson(
                route(
                    'api.v1.repositories.tree',
                    $repository,
                ),
            )
            ->assertForbidden();

        $this
            ->actingAs(
                $viewer,
                'web',
            )
            ->getJson(
                route(
                    'api.v1.repositories.commits',
                    $repository,
                ),
            )
            ->assertForbidden();

        $this
            ->actingAs(
                $viewer,
                'web',
            )
            ->getJson(
                route(
                    'api.v1.repositories.blob',
                    [
                        'repository' => $repository,
                        'path' => 'README.md',
                    ],
                ),
            )
            ->assertForbidden();
    }

    public function test_authenticated_user_can_browse_public_repository(): void
    {
        $owner = User::factory()->create();

        $viewer = User::factory()->create();

        $repository = $this->createRepositoryWithHistory(
            owner: $owner,
            public: true,
        );

        $this
            ->actingAs(
                $viewer,
                'web',
            )
            ->getJson(
                route(
                    'api.v1.repositories.tree',
                    $repository,
                ),
            )
            ->assertOk()
            ->assertJsonPath(
                'branch',
                'main',
            );
    }

    public function test_browser_query_parameters_are_validated(): void
    {
        $owner = User::factory()->create();

        $repository = $this->createRepositoryWithHistory(
            owner: $owner,
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
                        'limit' => 101,
                    ],
                ),
            )
            ->assertUnprocessable()
            ->assertJsonValidationErrors(
                'limit',
            );

        $this
            ->actingAs(
                $owner,
                'web',
            )
            ->getJson(
                route(
                    'api.v1.repositories.blob',
                    $repository,
                ),
            )
            ->assertUnprocessable()
            ->assertJsonPath(
                'error',
                'path_required',
            );
    }

    public function test_unknown_branch_and_path_return_not_found(): void
    {
        $owner = User::factory()->create();

        $repository = $this->createRepositoryWithHistory(
            owner: $owner,
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
                        'branch' => 'missing',
                    ],
                ),
            )
            ->assertNotFound()
            ->assertJsonPath(
                'error',
                'branch_not_found',
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
                        'path' => 'missing.txt',
                    ],
                ),
            )
            ->assertNotFound()
            ->assertJsonPath(
                'error',
                'path_not_found',
            );
    }

    public function test_missing_repository_storage_fails_closed(): void
    {
        $owner = User::factory()->create();

        $repository = Repository::factory()->create([
            'owner_id' => $owner->id,
        ]);

        $this
            ->actingAs(
                $owner,
                'web',
            )
            ->getJson(
                route(
                    'api.v1.repositories.branches',
                    $repository,
                ),
            )
            ->assertInternalServerError()
            ->assertJson([
                'message' => 'The repository could not be browsed.',
                'error' => 'storage_not_found',
            ]);
    }

    public function test_unauthenticated_user_cannot_browse_repositories(): void
    {
        $repository = Repository::factory()->create();

        $this
            ->getJson(
                route(
                    'api.v1.repositories.branches',
                    $repository,
                ),
            )
            ->assertUnauthorized();

        $this
            ->getJson(
                route(
                    'api.v1.repositories.tree',
                    $repository,
                ),
            )
            ->assertUnauthorized();

        $this
            ->getJson(
                route(
                    'api.v1.repositories.commits',
                    $repository,
                ),
            )
            ->assertUnauthorized();

        $this
            ->getJson(
                route(
                    'api.v1.repositories.blob',
                    [
                        'repository' => $repository,
                        'path' => 'README.md',
                    ],
                ),
            )
            ->assertUnauthorized();
    }

    /**
     * Create a managed repository containing one commit.
     */
    private function createRepositoryWithHistory(
        User $owner,
        bool $public = false,
    ): Repository {
        $factory = Repository::factory();

        if ($public) {
            $factory = $factory->publicRepository();
        } else {
            $factory = $factory->privateRepository();
        }

        $repository = $factory->create([
            'owner_id' => $owner->id,
        ]);

        $storage = app(
            RepositoryStorageService::class,
        );

        $runner = app(
            GitCommandRunner::class,
        );

        $barePath = $storage->initialize(
            $repository,
        );

        File::ensureDirectoryExists(
            $this->worktreePath,
        );

        $runner->run(
            operation: 'initialize-browser-api-worktree',
            arguments: [
                'init',
                '--initial-branch=main',
            ],
            workingDirectory: $this->worktreePath,
        );

        $runner->run(
            operation: 'configure-browser-api-user-name',
            arguments: [
                'config',
                'user.name',
                'Repository Browser API Test',
            ],
            workingDirectory: $this->worktreePath,
        );

        $runner->run(
            operation: 'configure-browser-api-user-email',
            arguments: [
                'config',
                'user.email',
                'browser-api@example.test',
            ],
            workingDirectory: $this->worktreePath,
        );

        File::put(
            $this->worktreePath
                .DIRECTORY_SEPARATOR
                .'README.md',
            "# Repository Browser\n\nAPI verification.\n",
        );

        File::ensureDirectoryExists(
            $this->worktreePath
                .DIRECTORY_SEPARATOR
                .'src',
        );

        File::put(
            $this->worktreePath
                .DIRECTORY_SEPARATOR
                .'src'
                .DIRECTORY_SEPARATOR
                .'app.php',
            "<?php\n\necho 'GitForge';\n",
        );

        $runner->run(
            operation: 'stage-browser-api-files',
            arguments: [
                'add',
                'README.md',
                'src/app.php',
            ],
            workingDirectory: $this->worktreePath,
        );

        $runner->run(
            operation: 'commit-browser-api-files',
            arguments: [
                'commit',
                '-m',
                'Initial repository content',
            ],
            workingDirectory: $this->worktreePath,
        );

        $runner->run(
            operation: 'add-browser-api-remote',
            arguments: [
                'remote',
                'add',
                'origin',
                $barePath,
            ],
            workingDirectory: $this->worktreePath,
        );

        $runner->run(
            operation: 'push-browser-api-history',
            arguments: [
                'push',
                'origin',
                'main',
            ],
            workingDirectory: $this->worktreePath,
        );

        return $repository->fresh();
    }
}