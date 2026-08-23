<?php

namespace Tests\Feature\Database;

use App\Enums\RepositoryVisibility;
use App\Models\PersonalAccessToken;
use App\Models\Repository;
use App\Models\User;
use App\Services\Git\RepositoryStorageService;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Tests\TestCase;

class DatabaseIntegrityTest extends TestCase
{
    use RefreshDatabase;

    private string $temporaryStorageRoot;

    protected function setUp(): void
    {
        parent::setUp();

        $this->temporaryStorageRoot = sys_get_temp_dir()
            .DIRECTORY_SEPARATOR
            .'gitforge-database-integrity-'
            .Str::uuid();

        File::ensureDirectoryExists(
            $this->temporaryStorageRoot,
        );

        config([
            'git.binary' => 'git',
            'git.timeout_seconds' => 30,
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

    public function test_required_database_migrations_are_applied(): void
    {
        $this->assertTrue(Schema::hasTable('users'));
        $this->assertTrue(Schema::hasTable('password_reset_tokens'));
        $this->assertTrue(Schema::hasTable('sessions'));
        $this->assertTrue(Schema::hasTable('personal_access_tokens'));
        $this->assertTrue(Schema::hasTable('repositories'));

        $this->assertTrue(
            Schema::hasColumns('users', [
                'id',
                'name',
                'username',
                'email',
                'email_verified_at',
                'password',
                'remember_token',
                'created_at',
                'updated_at',
            ]),
        );

        $this->assertTrue(
            Schema::hasColumns('personal_access_tokens', [
                'id',
                'user_id',
                'name',
                'token_identifier',
                'token_hash',
                'revoked_at',
                'created_at',
                'updated_at',
            ]),
        );

        $this->assertTrue(
            Schema::hasColumns('repositories', [
                'id',
                'owner_id',
                'name',
                'visibility',
                'created_at',
                'updated_at',
            ]),
        );
    }

    public function test_user_identity_constraints_are_enforced(): void
    {
        $firstUser = User::factory()->create([
            'name' => 'First User',
            'username' => 'identity-user-one',
            'email' => 'identity-one@example.test',
        ]);

        $this->assertDatabaseHas('users', [
            'id' => $firstUser->id,
            'username' => 'identity-user-one',
            'email' => 'identity-one@example.test',
        ]);

        try {
            User::factory()->create([
                'name' => 'Duplicate Email User',
                'username' => 'identity-user-two',
                'email' => 'identity-one@example.test',
            ]);

            $this->fail('Duplicate email was accepted.');
        } catch (QueryException) {
            $this->assertDatabaseCount('users', 1);
        }

        try {
            User::factory()->create([
                'name' => 'Duplicate Username User',
                'username' => 'identity-user-one',
                'email' => 'identity-two@example.test',
            ]);

            $this->fail('Duplicate username was accepted.');
        } catch (QueryException) {
            $this->assertDatabaseCount('users', 1);
        }
    }

    public function test_repository_owner_relationship_and_constraints_are_preserved(): void
    {
        $owner = User::factory()->create([
            'name' => 'Repository Owner',
            'username' => 'repository-owner',
            'email' => 'repository-owner@example.test',
        ]);

        $repository = Repository::factory()->create([
            'owner_id' => $owner->id,
            'name' => 'project',
            'visibility' => RepositoryVisibility::PrivateAccess,
        ]);

        $repository->refresh();

        $this->assertSame($owner->id, $repository->owner_id);
        $this->assertTrue($repository->owner->is($owner));

        try {
            Repository::factory()->create([
                'owner_id' => $owner->id,
                'name' => 'project',
                'visibility' => RepositoryVisibility::PublicAccess,
            ]);

            $this->fail('Duplicate repository name for the same owner was accepted.');
        } catch (QueryException) {
            $this->assertDatabaseCount('repositories', 1);
        }
    }

    public function test_personal_access_token_owner_relationship_and_constraints_are_preserved(): void
    {
        $owner = User::factory()->create([
            'name' => 'PAT Owner',
            'username' => 'pat-owner',
            'email' => 'pat-owner@example.test',
        ]);

        $identifier = bin2hex(random_bytes(16));

        $plainTextToken = sprintf(
            'gitforge_pat_%s_%s',
            $identifier,
            bin2hex(random_bytes(32)),
        );

        $token = $owner->personalAccessTokens()->create([
            'name' => 'Database integrity token',
            'token_identifier' => $identifier,
            'token_hash' => hash('sha256', $plainTextToken),
            'revoked_at' => null,
        ]);

        $token->refresh();

        $this->assertSame($owner->id, $token->user_id);
        $this->assertTrue($token->user->is($owner));

        $otherOwner = User::factory()->create([
            'name' => 'Other PAT Owner',
            'username' => 'other-pat-owner',
            'email' => 'other-pat-owner@example.test',
        ]);

        try {
            PersonalAccessToken::query()->create([
                'user_id' => $otherOwner->id,
                'name' => 'Duplicate identifier token',
                'token_identifier' => $identifier,
                'token_hash' => hash('sha256', 'different-token-value'),
                'revoked_at' => null,
            ]);

            $this->fail('Duplicate PAT identifier was accepted.');
        } catch (QueryException) {
            $this->assertDatabaseCount('personal_access_tokens', 1);
        }

        $owner->delete();

        $this->assertDatabaseMissing('personal_access_tokens', [
            'id' => $token->id,
        ]);
    }

    public function test_repository_metadata_and_managed_storage_remain_consistent(): void
    {
        $owner = User::factory()->create([
            'name' => 'Storage Owner',
            'username' => 'storage-owner',
            'email' => 'storage-owner@example.test',
        ]);

        $repository = Repository::factory()->create([
            'owner_id' => $owner->id,
            'name' => 'storage-project',
            'visibility' => RepositoryVisibility::PrivateAccess,
        ]);

        $storage = app(RepositoryStorageService::class);

        $storage->initialize($repository);

        $repository->refresh();

        $repositoryPath = $storage->resolveExisting($repository);

        $this->assertDatabaseHas('repositories', [
            'id' => $repository->id,
            'owner_id' => $owner->id,
            'name' => 'storage-project',
            'visibility' => RepositoryVisibility::PrivateAccess->value,
        ]);

        $this->assertDirectoryExists($repositoryPath);

        $this->assertFileExists(
            $repositoryPath
            .DIRECTORY_SEPARATOR
            .'HEAD',
        );

        $this->assertDirectoryExists(
            $repositoryPath
            .DIRECTORY_SEPARATOR
            .'objects',
        );

        $this->assertDirectoryExists(
            $repositoryPath
            .DIRECTORY_SEPARATOR
            .'refs',
        );

        $head = trim(
            File::get(
                $repositoryPath
                .DIRECTORY_SEPARATOR
                .'HEAD',
            ),
        );

        $this->assertSame(
            'ref: refs/heads/main',
            $head,
        );
    }
}
