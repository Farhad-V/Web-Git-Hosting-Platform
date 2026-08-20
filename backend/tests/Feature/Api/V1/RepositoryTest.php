<?php

namespace Tests\Feature\Api\V1;

use App\Enums\RepositoryVisibility;
use App\Models\Repository;
use App\Models\User;
use App\Services\Git\RepositoryStorageService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Tests\TestCase;

class RepositoryTest extends TestCase
{
    use RefreshDatabase;

    private string $temporaryStorageRoot;

    protected function setUp(): void
    {
        parent::setUp();

        $this->temporaryStorageRoot = sys_get_temp_dir()
            .DIRECTORY_SEPARATOR
            .'gitforge-repository-api-tests-'
            .Str::uuid();

        config([
            'git.binary' => 'git',
            'git.timeout_seconds' => 10,
            'git.storage_root' => $this->temporaryStorageRoot,
            'git.repositories_directory' => 'repositories',
            'git.initial_branch' => 'main',
        ]);
    }

    protected function tearDown(): void
    {
        if (
            isset($this->temporaryStorageRoot)
            && File::isDirectory($this->temporaryStorageRoot)
        ) {
            File::deleteDirectory(
                $this->temporaryStorageRoot,
            );
        }

        parent::tearDown();
    }

    public function test_authenticated_user_can_create_repository(): void
    {
        $user = User::factory()->create([
            'username' => 'farhad',
        ]);

        $response = $this
            ->actingAs(
                $user,
                'web',
            )
            ->postJson(
                route('api.v1.repositories.store'),
                [
                    'name' => 'Git-Hosting-Platform',
                    'visibility' => 'private',
                ],
            );

        $response
            ->assertCreated()
            ->assertJson([
                'message' => 'Repository created successfully.',
                'repository' => [
                    'name' => 'git-hosting-platform',
                    'full_name' => 'farhad/git-hosting-platform',
                    'visibility' => 'private',
                    'owner' => [
                        'id' => $user->id,
                        'username' => 'farhad',
                    ],
                ],
            ]);

        $this->assertDatabaseHas(
            'repositories',
            [
                'owner_id' => $user->id,
                'name' => 'git-hosting-platform',
                'visibility' => (
                    RepositoryVisibility::PrivateAccess->value
                ),
            ],
        );

        $repository = Repository::query()
            ->firstOrFail();

        $expectedPath = $this->temporaryStorageRoot
            .DIRECTORY_SEPARATOR
            .'repositories'
            .DIRECTORY_SEPARATOR
            .$repository->id
            .'.git';

        $this->assertSame(
            $expectedPath,
            app(RepositoryStorageService::class)
                ->pathFor($repository),
        );

        $this->assertTrue(
            app(RepositoryStorageService::class)
                ->exists($repository),
        );
    }

    public function test_repository_creation_does_not_persist_deferred_metadata(): void
    {
        $user = User::factory()->create();

        $this
            ->actingAs(
                $user,
                'web',
            )
            ->postJson(
                route('api.v1.repositories.store'),
                [
                    'name' => 'project',
                    'visibility' => 'public',
                ],
            )
            ->assertCreated();

        $repository = Repository::query()
            ->firstOrFail();

        $attributes = $repository->getAttributes();

        $this->assertArrayNotHasKey(
            'slug',
            $attributes,
        );

        $this->assertArrayNotHasKey(
            'description',
            $attributes,
        );

        $this->assertArrayNotHasKey(
            'status',
            $attributes,
        );

        $this->assertArrayNotHasKey(
            'storage_uuid',
            $attributes,
        );

        $this->assertArrayNotHasKey(
            'default_branch',
            $attributes,
        );

        $this->assertArrayNotHasKey(
            'archived_at',
            $attributes,
        );

        $this->assertArrayNotHasKey(
            'deleted_at',
            $attributes,
        );
    }

    public function test_failed_storage_creation_rolls_back_repository_metadata(): void
    {
        config([
            'git.initial_branch' => 'invalid..branch',
        ]);

        $user = User::factory()->create();

        $this
            ->actingAs(
                $user,
                'web',
            )
            ->postJson(
                route('api.v1.repositories.store'),
                [
                    'name' => 'broken-repository',
                    'visibility' => 'private',
                ],
            )
            ->assertInternalServerError();

        $this->assertDatabaseCount(
            'repositories',
            0,
        );

        $repositoriesDirectory = $this->temporaryStorageRoot
            .DIRECTORY_SEPARATOR
            .'repositories';

        if (File::isDirectory($repositoriesDirectory)) {
            $this->assertCount(
                0,
                File::directories(
                    $repositoriesDirectory,
                ),
            );
        }
    }

    public function test_repository_name_is_normalized_to_lowercase(): void
    {
        $user = User::factory()->create();

        $this
            ->actingAs(
                $user,
                'web',
            )
            ->postJson(
                route('api.v1.repositories.store'),
                [
                    'name' => 'My.Project_Name',
                    'visibility' => 'private',
                ],
            )
            ->assertCreated()
            ->assertJsonPath(
                'repository.name',
                'my.project_name',
            );

        $this->assertDatabaseHas(
            'repositories',
            [
                'owner_id' => $user->id,
                'name' => 'my.project_name',
            ],
        );
    }

    public function test_repository_name_rejects_unsafe_values(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs(
                $user,
                'web',
            )
            ->postJson(
                route('api.v1.repositories.store'),
                [
                    'name' => '../project',
                    'visibility' => 'private',
                ],
            );

        $response
            ->assertUnprocessable()
            ->assertJsonValidationErrors(
                'name',
            );

        $this->assertDatabaseCount(
            'repositories',
            0,
        );
    }

    public function test_repository_name_must_be_unique_within_owner_namespace(): void
    {
        $user = User::factory()->create();

        Repository::factory()->create([
            'owner_id' => $user->id,
            'name' => 'project',
        ]);

        $response = $this
            ->actingAs(
                $user,
                'web',
            )
            ->postJson(
                route('api.v1.repositories.store'),
                [
                    'name' => 'PROJECT',
                    'visibility' => 'private',
                ],
            );

        $response
            ->assertUnprocessable()
            ->assertJsonValidationErrors(
                'name',
            );

        $this->assertDatabaseCount(
            'repositories',
            1,
        );
    }

    public function test_different_owners_can_use_same_repository_name(): void
    {
        $firstOwner = User::factory()->create();

        $secondOwner = User::factory()->create();

        Repository::factory()->create([
            'owner_id' => $firstOwner->id,
            'name' => 'project',
        ]);

        $this
            ->actingAs(
                $secondOwner,
                'web',
            )
            ->postJson(
                route('api.v1.repositories.store'),
                [
                    'name' => 'project',
                    'visibility' => 'private',
                ],
            )
            ->assertCreated();

        $this->assertDatabaseCount(
            'repositories',
            2,
        );
    }

    public function test_repository_index_lists_only_owned_repositories(): void
    {
        $user = User::factory()->create();

        $otherUser = User::factory()->create();

        $ownedPrivate = Repository::factory()
            ->privateRepository()
            ->create([
                'owner_id' => $user->id,
                'name' => 'owned-private',
            ]);

        $ownedPublic = Repository::factory()
            ->publicRepository()
            ->create([
                'owner_id' => $user->id,
                'name' => 'owned-public',
            ]);

        $otherPublic = Repository::factory()
            ->publicRepository()
            ->create([
                'owner_id' => $otherUser->id,
                'name' => 'other-public',
            ]);

        $response = $this
            ->actingAs(
                $user,
                'web',
            )
            ->getJson(
                route('api.v1.repositories.index'),
            );

        $response
            ->assertOk()
            ->assertJsonFragment([
                'id' => $ownedPrivate->id,
            ])
            ->assertJsonFragment([
                'id' => $ownedPublic->id,
            ])
            ->assertJsonMissing([
                'id' => $otherPublic->id,
            ]);
    }

    public function test_anonymous_visitor_can_view_public_repository(): void
    {
        $owner = User::factory()->create([
            'username' => 'owner-user',
        ]);

        $repository = Repository::factory()
            ->publicRepository()
            ->create([
                'owner_id' => $owner->id,
                'name' => 'public-project',
            ]);

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
                'owner-user/public-project',
            )
            ->assertJsonPath(
                'data.visibility',
                'public',
            );
    }

    public function test_authenticated_user_can_view_public_repository(): void
    {
        $owner = User::factory()->create();

        $viewer = User::factory()->create();

        $repository = Repository::factory()
            ->publicRepository()
            ->create([
                'owner_id' => $owner->id,
            ]);

        $this
            ->actingAs(
                $viewer,
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
                'public',
            );
    }

    public function test_owner_can_view_private_repository(): void
    {
        $owner = User::factory()->create();

        $repository = Repository::factory()
            ->privateRepository()
            ->create([
                'owner_id' => $owner->id,
            ]);

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
            );
    }

    public function test_non_owner_cannot_view_private_repository(): void
    {
        $owner = User::factory()->create();

        $viewer = User::factory()->create();

        $repository = Repository::factory()
            ->privateRepository()
            ->create([
                'owner_id' => $owner->id,
            ]);

        $this
            ->actingAs(
                $viewer,
                'web',
            )
            ->getJson(
                route(
                    'api.v1.repositories.show',
                    $repository,
                ),
            )
            ->assertForbidden();
    }

    public function test_anonymous_visitor_cannot_view_private_repository(): void
    {
        $repository = Repository::factory()
            ->privateRepository()
            ->create();

        $this
            ->getJson(
                route(
                    'api.v1.repositories.show',
                    $repository,
                ),
            )
            ->assertForbidden();
    }

    public function test_owner_can_update_repository_visibility(): void
    {
        $owner = User::factory()->create();

        $repository = Repository::factory()
            ->privateRepository()
            ->create([
                'owner_id' => $owner->id,
                'name' => 'project',
            ]);

        $response = $this
            ->actingAs(
                $owner,
                'web',
            )
            ->patchJson(
                route(
                    'api.v1.repositories.update',
                    $repository,
                ),
                [
                    'visibility' => 'public',
                ],
            );

        $response
            ->assertOk()
            ->assertJson([
                'message' => 'Repository visibility updated successfully.',
                'repository' => [
                    'id' => $repository->id,
                    'name' => 'project',
                    'visibility' => 'public',
                ],
            ]);

        $repository->refresh();

        $this->assertSame(
            RepositoryVisibility::PublicAccess,
            $repository->visibility,
        );
    }

    public function test_repository_update_does_not_allow_rename(): void
    {
        $owner = User::factory()->create();

        $repository = Repository::factory()->create([
            'owner_id' => $owner->id,
            'name' => 'original-name',
        ]);

        $this
            ->actingAs(
                $owner,
                'web',
            )
            ->patchJson(
                route(
                    'api.v1.repositories.update',
                    $repository,
                ),
                [
                    'name' => 'new-name',
                    'visibility' => 'public',
                ],
            )
            ->assertOk();

        $repository->refresh();

        $this->assertSame(
            'original-name',
            $repository->name,
        );

        $this->assertSame(
            RepositoryVisibility::PublicAccess,
            $repository->visibility,
        );
    }

    public function test_non_owner_cannot_update_repository(): void
    {
        $owner = User::factory()->create();

        $otherUser = User::factory()->create();

        $repository = Repository::factory()->create([
            'owner_id' => $owner->id,
        ]);

        $this
            ->actingAs(
                $otherUser,
                'web',
            )
            ->patchJson(
                route(
                    'api.v1.repositories.update',
                    $repository,
                ),
                [
                    'visibility' => 'public',
                ],
            )
            ->assertForbidden();

        $repository->refresh();

        $this->assertSame(
            RepositoryVisibility::PrivateAccess,
            $repository->visibility,
        );
    }

    public function test_unauthenticated_user_cannot_manage_repositories(): void
    {
        $repository = Repository::factory()
            ->publicRepository()
            ->create();

        $this
            ->getJson(
                route('api.v1.repositories.index'),
            )
            ->assertUnauthorized();

        $this
            ->postJson(
                route('api.v1.repositories.store'),
                [
                    'name' => 'project',
                    'visibility' => 'private',
                ],
            )
            ->assertUnauthorized();

        /*
         * Repository metadata for a public repository is intentionally
         * readable without an authenticated session.
         */
        $this
            ->getJson(
                route(
                    'api.v1.repositories.show',
                    $repository,
                ),
            )
            ->assertOk();

        $this
            ->patchJson(
                route(
                    'api.v1.repositories.update',
                    $repository,
                ),
                [
                    'visibility' => 'private',
                ],
            )
            ->assertUnauthorized();
    }
}