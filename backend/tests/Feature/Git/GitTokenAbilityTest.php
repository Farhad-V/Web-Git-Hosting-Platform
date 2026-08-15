<?php

namespace Tests\Feature\Git;

use App\Enums\RepositoryStatus;
use App\Enums\RepositoryVisibility;
use App\Models\Repository;
use App\Models\User;
use App\Services\Git\RepositoryStorageService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Tests\TestCase;

class GitTokenAbilityTest extends TestCase
{
    use RefreshDatabase;

    private string $temporaryStorageRoot;

    protected function setUp(): void
    {
        parent::setUp();

        $this->temporaryStorageRoot = sys_get_temp_dir()
            .DIRECTORY_SEPARATOR
            .'git-token-ability-tests-'
            .Str::uuid();

        config([
            'git.binary' => 'git',
            'git.timeout_seconds' => 10,
            'git.max_request_bytes' => 104_857_600,
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

    public function test_api_session_token_cannot_clone_private_repository(): void
    {
        [$repository, $owner] = $this->createManagedRepository();

        $token = $owner
            ->createToken('web', ['api'])
            ->plainTextToken;

        $this
            ->withHeader(
                'Authorization',
                $this->basicAuthorization($owner, $token),
            )
            ->get($this->infoRefsUrl(
                repository: $repository,
                service: 'git-upload-pack',
            ))
            ->assertUnauthorized()
            ->assertHeader(
                'WWW-Authenticate',
                'Basic realm="Git Hosting Platform", charset="UTF-8"',
            );
    }

    public function test_read_token_can_clone_private_repository(): void
    {
        [$repository, $owner] = $this->createManagedRepository();

        $createdToken = $owner->createToken(
            'Read token',
            ['repository:read'],
        );

        $this
            ->withHeader(
                'Authorization',
                $this->basicAuthorization(
                    $owner,
                    $createdToken->plainTextToken,
                ),
            )
            ->get($this->infoRefsUrl(
                repository: $repository,
                service: 'git-upload-pack',
            ))
            ->assertOk()
            ->assertHeader(
                'Content-Type',
                'application/x-git-upload-pack-advertisement',
            );

        $this->assertNotNull(
            $createdToken
                ->accessToken
                ->fresh()
                ?->last_used_at,
        );
    }

    public function test_read_only_token_cannot_push(): void
    {
        [$repository, $owner] = $this->createManagedRepository();

        $token = $owner
            ->createToken(
                'Read token',
                ['repository:read'],
            )
            ->plainTextToken;

        $this
            ->withHeader(
                'Authorization',
                $this->basicAuthorization($owner, $token),
            )
            ->get($this->infoRefsUrl(
                repository: $repository,
                service: 'git-receive-pack',
            ))
            ->assertUnauthorized();
    }

    public function test_write_token_can_push_to_active_repository(): void
    {
        [$repository, $owner] = $this->createManagedRepository();

        $token = $owner
            ->createToken(
                'Write token',
                ['repository:write'],
            )
            ->plainTextToken;

        $this
            ->withHeader(
                'Authorization',
                $this->basicAuthorization($owner, $token),
            )
            ->get($this->infoRefsUrl(
                repository: $repository,
                service: 'git-receive-pack',
            ))
            ->assertOk()
            ->assertHeader(
                'Content-Type',
                'application/x-git-receive-pack-advertisement',
            );
    }

    public function test_expired_repository_token_is_rejected(): void
    {
        [$repository, $owner] = $this->createManagedRepository();

        $token = $owner
            ->createToken(
                'Expired token',
                ['repository:read'],
                now()->subMinute(),
            )
            ->plainTextToken;

        $this
            ->withHeader(
                'Authorization',
                $this->basicAuthorization($owner, $token),
            )
            ->get($this->infoRefsUrl(
                repository: $repository,
                service: 'git-upload-pack',
            ))
            ->assertUnauthorized();
    }

    /**
     * Create private repository metadata and managed bare storage.
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
            'name' => 'Project',
            'slug' => 'project',
            'visibility' => RepositoryVisibility::PrivateAccess,
            'status' => RepositoryStatus::Active,
        ]);

        app(RepositoryStorageService::class)
            ->initialize($repository);

        return [
            $repository->load('owner'),
            $owner,
        ];
    }

    /**
     * Build the Git reference-advertisement URL.
     */
    private function infoRefsUrl(
        Repository $repository,
        string $service,
    ): string {
        return route('git.info-refs', [
            'username' => $repository->owner->username,
            'repository' => $repository->slug,
            'service' => $service,
        ]);
    }

    /**
     * Build a Git-compatible HTTP Basic authorization value.
     */
    private function basicAuthorization(
        User $user,
        string $plainTextToken,
    ): string {
        return 'Basic '.base64_encode(
            "{$user->username}:{$plainTextToken}",
        );
    }
}
