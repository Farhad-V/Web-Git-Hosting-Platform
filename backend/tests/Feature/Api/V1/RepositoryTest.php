<?php

namespace Tests\Feature\Api\V1;

use App\Enums\RepositoryStatus;
use App\Enums\RepositoryVisibility;
use App\Jobs\DeleteRepositoryStorage;
use App\Models\Repository;
use App\Models\User;
use App\Services\Git\RepositoryStorageService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Str;
use Laravel\Sanctum\Sanctum;
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
            .'git-hosting-platform-api-tests-'
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

        Sanctum::actingAs($user);

        $response = $this->postJson(
            route('api.v1.repositories.store'),
            [
                'name' => 'Git-Hosting-Platform',
                'description' => 'A self-hosted Git platform.',
                'visibility' => 'private',
            ],
        );

        $response
            ->assertCreated()
            ->assertJson([
                'message' => 'Repository created successfully.',
                'repository' => [
                    'name' => 'Git-Hosting-Platform',
                    'slug' => 'git-hosting-platform',
                    'full_name' => 'farhad/git-hosting-platform',
                    'description' => 'A self-hosted Git platform.',
                    'visibility' => 'private',
                    'status' => 'active',
                    'default_branch' => null,
                    'is_empty' => true,
                    'is_writable' => true,
                    'is_archived' => false,
                    'owner' => [
                        'id' => $user->id,
                        'username' => 'farhad',
                    ],
                ],
            ]);

        $this->assertDatabaseHas('repositories', [
            'owner_id' => $user->id,
            'name' => 'Git-Hosting-Platform',
            'slug' => 'git-hosting-platform',
            'visibility' => RepositoryVisibility::PrivateAccess->value,
            'status' => RepositoryStatus::Active->value,
            'default_branch' => null,
        ]);

        $repository = Repository::query()->firstOrFail();

        $this->assertNotEmpty($repository->storage_uuid);

        $this->assertSame(
            "repositories/{$repository->storage_uuid}.git",
            $repository->storageRelativePath(),
        );

        $this->assertTrue(
            app(RepositoryStorageService::class)
                ->exists($repository),
        );
    }

    public function test_failed_storage_creation_rolls_back_metadata(): void
    {
        config([
            'git.initial_branch' => 'invalid..branch',
        ]);

        $user = User::factory()->create();

        Sanctum::actingAs($user);

        $this->postJson(
            route('api.v1.repositories.store'),
            [
                'name' => 'Broken-Repository',
                'visibility' => 'private',
            ],
        )->assertInternalServerError();

        $this->assertDatabaseCount('repositories', 0);

        $repositoriesDirectory = $this->temporaryStorageRoot
            .DIRECTORY_SEPARATOR
            .'repositories';

        if (File::isDirectory($repositoriesDirectory)) {
            $this->assertCount(
                0,
                File::directories($repositoriesDirectory),
            );
        }
    }

    public function test_repository_name_must_be_unique_within_owner_namespace(): void
    {
        $user = User::factory()->create();

        Repository::factory()->create([
            'owner_id' => $user->id,
            'name' => 'Project',
            'slug' => 'project',
        ]);

        Sanctum::actingAs($user);

        $this->postJson(
            route('api.v1.repositories.store'),
            [
                'name' => 'PROJECT',
                'visibility' => 'private',
            ],
        )
            ->assertUnprocessable()
            ->assertJsonValidationErrors('slug');

        $this->assertDatabaseCount('repositories', 1);
    }

    public function test_different_owners_can_use_same_repository_name(): void
    {
        $firstOwner = User::factory()->create();
        $secondOwner = User::factory()->create();

        Repository::factory()->create([
            'owner_id' => $firstOwner->id,
            'name' => 'Project',
            'slug' => 'project',
        ]);

        Sanctum::actingAs($secondOwner);

        $this->postJson(
            route('api.v1.repositories.store'),
            [
                'name' => 'Project',
                'visibility' => 'private',
            ],
        )->assertCreated();

        $this->assertDatabaseCount('repositories', 2);
    }

    public function test_user_sees_public_and_owned_repositories_only(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();

        $ownedPrivate = Repository::factory()->create([
            'owner_id' => $user->id,
            'name' => 'Owned Private',
            'slug' => 'owned-private',
        ]);

        $publicRepository = Repository::factory()
            ->publicRepository()
            ->create([
                'owner_id' => $otherUser->id,
                'name' => 'Public Project',
                'slug' => 'public-project',
            ]);

        $hiddenPrivate = Repository::factory()
            ->privateRepository()
            ->create([
                'owner_id' => $otherUser->id,
                'name' => 'Hidden Project',
                'slug' => 'hidden-project',
            ]);

        Sanctum::actingAs($user);

        $response = $this->getJson(
            route('api.v1.repositories.index'),
        );

        $response
            ->assertOk()
            ->assertJsonFragment([
                'id' => $ownedPrivate->id,
            ])
            ->assertJsonFragment([
                'id' => $publicRepository->id,
            ])
            ->assertJsonMissing([
                'id' => $hiddenPrivate->id,
            ]);
    }

    public function test_user_can_view_public_repository(): void
    {
        $owner = User::factory()->create();
        $viewer = User::factory()->create();

        $repository = Repository::factory()
            ->publicRepository()
            ->create([
                'owner_id' => $owner->id,
            ]);

        Sanctum::actingAs($viewer);

        $this->getJson(
            route('api.v1.repositories.show', $repository),
        )
            ->assertOk()
            ->assertJson([
                'data' => [
                    'id' => $repository->id,
                    'visibility' => 'public',
                ],
            ]);
    }

    public function test_user_cannot_view_another_users_private_repository(): void
    {
        $owner = User::factory()->create();
        $viewer = User::factory()->create();

        $repository = Repository::factory()
            ->privateRepository()
            ->create([
                'owner_id' => $owner->id,
            ]);

        Sanctum::actingAs($viewer);

        $this->getJson(
            route('api.v1.repositories.show', $repository),
        )->assertForbidden();
    }

    public function test_owner_can_update_repository_metadata(): void
    {
        $owner = User::factory()->create();

        $repository = Repository::factory()->create([
            'owner_id' => $owner->id,
            'name' => 'Old-Name',
            'slug' => 'old-name',
        ]);

        $storageUuid = $repository->storage_uuid;

        Sanctum::actingAs($owner);

        $this->patchJson(
            route('api.v1.repositories.update', $repository),
            [
                'name' => 'New-Name',
                'description' => 'Updated description.',
                'visibility' => 'public',
            ],
        )
            ->assertOk()
            ->assertJson([
                'message' => 'Repository updated successfully.',
                'repository' => [
                    'name' => 'New-Name',
                    'slug' => 'new-name',
                    'description' => 'Updated description.',
                    'visibility' => 'public',
                ],
            ]);

        $repository->refresh();

        $this->assertSame('new-name', $repository->slug);
        $this->assertSame($storageUuid, $repository->storage_uuid);
    }

    public function test_non_owner_cannot_update_repository(): void
    {
        $owner = User::factory()->create();
        $otherUser = User::factory()->create();

        $repository = Repository::factory()->create([
            'owner_id' => $owner->id,
        ]);

        Sanctum::actingAs($otherUser);

        $this->patchJson(
            route('api.v1.repositories.update', $repository),
            [
                'description' => 'Unauthorized change.',
            ],
        )->assertForbidden();
    }

    public function test_owner_can_archive_and_unarchive_repository(): void
    {
        $owner = User::factory()->create();

        $repository = Repository::factory()->create([
            'owner_id' => $owner->id,
        ]);

        Sanctum::actingAs($owner);

        $this->postJson(
            route('api.v1.repositories.archive', $repository),
        )
            ->assertOk()
            ->assertJson([
                'repository' => [
                    'status' => 'archived',
                    'is_writable' => false,
                    'is_archived' => true,
                ],
            ]);

        $repository->refresh();

        $this->assertSame(
            RepositoryStatus::Archived,
            $repository->status,
        );

        $this->assertNotNull($repository->archived_at);

        $this->patchJson(
            route('api.v1.repositories.update', $repository),
            [
                'description' => 'Should not change.',
            ],
        )->assertForbidden();

        $this->postJson(
            route('api.v1.repositories.unarchive', $repository),
        )
            ->assertOk()
            ->assertJson([
                'repository' => [
                    'status' => 'active',
                    'is_writable' => true,
                    'is_archived' => false,
                ],
            ]);

        $repository->refresh();

        $this->assertSame(
            RepositoryStatus::Active,
            $repository->status,
        );

        $this->assertNull($repository->archived_at);
    }

    public function test_owner_can_schedule_repository_deletion(): void
    {
        Queue::fake();

        $owner = User::factory()->create();

        $repository = Repository::factory()->create([
            'owner_id' => $owner->id,
        ]);

        Sanctum::actingAs($owner);

        $this->deleteJson(
            route('api.v1.repositories.destroy', $repository),
        )
            ->assertAccepted()
            ->assertJson([
                'message' => 'Repository deletion scheduled.',
            ]);

        $repository->refresh();

        $this->assertSame(
            RepositoryStatus::DeletionPending,
            $repository->status,
        );

        $this->assertNotNull(
            $repository->deletion_requested_at,
        );

        $this->assertDatabaseHas('repositories', [
            'id' => $repository->id,
            'deleted_at' => null,
        ]);

        Queue::assertPushed(
            DeleteRepositoryStorage::class,
            fn (DeleteRepositoryStorage $job): bool => (
                $job->repositoryId === $repository->id
            ),
        );
    }

    public function test_unauthenticated_user_cannot_manage_repositories(): void
    {
        $this->getJson(
            route('api.v1.repositories.index'),
        )->assertUnauthorized();

        $this->postJson(
            route('api.v1.repositories.store'),
            [
                'name' => 'Project',
                'visibility' => 'private',
            ],
        )->assertUnauthorized();
    }
}
