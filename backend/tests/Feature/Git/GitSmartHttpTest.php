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

class GitSmartHttpTest extends TestCase
{
    use RefreshDatabase;

    private string $temporaryStorageRoot;

    protected function setUp(): void
    {
        parent::setUp();

        $this->temporaryStorageRoot = sys_get_temp_dir()
            .DIRECTORY_SEPARATOR
            .'git-smart-http-tests-'
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

    public function test_public_repository_advertises_upload_pack_without_authentication(): void
    {
        [$repository] = $this->createManagedRepository(
            visibility: RepositoryVisibility::PublicAccess,
        );

        $response = $this->get(
            route('git.info-refs', [
                'username' => $repository->owner->username,
                'repository' => $repository->slug,
                'service' => 'git-upload-pack',
            ]),
        );

        $response
            ->assertOk()
            ->assertHeader(
                'Content-Type',
                'application/x-git-upload-pack-advertisement',
            )
            ->assertHeader(
                'X-Content-Type-Options',
                'nosniff',
            );

        $this->assertStringContainsString(
            "# service=git-upload-pack\n",
            $response->getContent(),
        );

        $this->assertStringContainsString(
            '0000',
            $response->getContent(),
        );
    }

    public function test_private_repository_requires_authentication_for_upload_pack(): void
    {
        [$repository] = $this->createManagedRepository(
            visibility: RepositoryVisibility::PrivateAccess,
        );

        $response = $this->get(
            route('git.info-refs', [
                'username' => $repository->owner->username,
                'repository' => $repository->slug,
                'service' => 'git-upload-pack',
            ]),
        );

        $response
            ->assertUnauthorized()
            ->assertHeader(
                'WWW-Authenticate',
                'Basic realm="Git Hosting Platform", charset="UTF-8"',
            );
    }

    public function test_owner_token_can_access_private_upload_pack(): void
    {
        [$repository, $owner] = $this->createManagedRepository(
            visibility: RepositoryVisibility::PrivateAccess,
        );

        $response = $this
            ->withHeaders(
                $this->basicAuthHeaders($owner),
            )
            ->get(
                route('git.info-refs', [
                    'username' => $owner->username,
                    'repository' => $repository->slug,
                    'service' => 'git-upload-pack',
                ]),
            );

        $response
            ->assertOk()
            ->assertHeader(
                'Content-Type',
                'application/x-git-upload-pack-advertisement',
            );
    }

    public function test_invalid_token_cannot_access_private_repository(): void
    {
        [$repository, $owner] = $this->createManagedRepository(
            visibility: RepositoryVisibility::PrivateAccess,
        );

        $credentials = base64_encode(
            "{$owner->username}:invalid-token",
        );

        $this
            ->withHeader(
                'Authorization',
                "Basic {$credentials}",
            )
            ->get(
                route('git.info-refs', [
                    'username' => $owner->username,
                    'repository' => $repository->slug,
                    'service' => 'git-upload-pack',
                ]),
            )
            ->assertUnauthorized();
    }

    public function test_owner_token_can_advertise_receive_pack(): void
    {
        [$repository, $owner] = $this->createManagedRepository(
            visibility: RepositoryVisibility::PrivateAccess,
        );

        $response = $this
            ->withHeaders(
                $this->basicAuthHeaders($owner),
            )
            ->get(
                route('git.info-refs', [
                    'username' => $owner->username,
                    'repository' => $repository->slug,
                    'service' => 'git-receive-pack',
                ]),
            );

        $response
            ->assertOk()
            ->assertHeader(
                'Content-Type',
                'application/x-git-receive-pack-advertisement',
            );

        $this->assertStringContainsString(
            "# service=git-receive-pack\n",
            $response->getContent(),
        );
    }

    public function test_archived_repository_rejects_receive_pack(): void
    {
        [$repository, $owner] = $this->createManagedRepository(
            visibility: RepositoryVisibility::PrivateAccess,
            status: RepositoryStatus::Archived,
        );

        $this
            ->withHeaders(
                $this->basicAuthHeaders($owner),
            )
            ->get(
                route('git.info-refs', [
                    'username' => $owner->username,
                    'repository' => $repository->slug,
                    'service' => 'git-receive-pack',
                ]),
            )
            ->assertForbidden();
    }

    public function test_non_owner_cannot_advertise_receive_pack(): void
    {
        [$repository, $owner] = $this->createManagedRepository(
            visibility: RepositoryVisibility::PublicAccess,
        );

        $otherUser = User::factory()->create([
            'username' => 'other-user',
        ]);

        $this
            ->withHeaders(
                $this->basicAuthHeaders($otherUser),
            )
            ->get(
                route('git.info-refs', [
                    'username' => $owner->username,
                    'repository' => $repository->slug,
                    'service' => 'git-receive-pack',
                ]),
            )
            ->assertForbidden();
    }

    public function test_upload_pack_rejects_incorrect_content_type(): void
    {
        [$repository, $owner] = $this->createManagedRepository(
            visibility: RepositoryVisibility::PublicAccess,
        );

        $this->call(
            'POST',
            route('git.upload-pack', [
                'username' => $owner->username,
                'repository' => $repository->slug,
            ]),
            [],
            [],
            [],
            [
                'CONTENT_TYPE' => 'application/json',
            ],
            '0000',
        )->assertUnsupportedMediaType();
    }

    public function test_receive_pack_requires_authentication(): void
    {
        [$repository, $owner] = $this->createManagedRepository(
            visibility: RepositoryVisibility::PublicAccess,
        );

        $this->call(
            'POST',
            route('git.receive-pack', [
                'username' => $owner->username,
                'repository' => $repository->slug,
            ]),
            [],
            [],
            [],
            [
                'CONTENT_TYPE' => (
                    'application/x-git-receive-pack-request'
                ),
            ],
            '0000',
        )->assertUnauthorized();
    }

    public function test_unsupported_git_service_is_rejected(): void
    {
        [$repository, $owner] = $this->createManagedRepository(
            visibility: RepositoryVisibility::PublicAccess,
        );

        $this->get(
            route('git.info-refs', [
                'username' => $owner->username,
                'repository' => $repository->slug,
                'service' => 'git-unsupported-pack',
            ]),
        )->assertBadRequest();
    }

    /**
     * Create repository metadata and real managed bare storage.
     *
     * @return array{Repository, User}
     */
    private function createManagedRepository(
        RepositoryVisibility $visibility,
        RepositoryStatus $status = RepositoryStatus::Active,
    ): array {
        $owner = User::factory()->create([
            'username' => 'farhad',
        ]);

        $repository = Repository::factory()->create([
            'owner_id' => $owner->id,
            'name' => 'Project',
            'slug' => 'project',
            'visibility' => $visibility,
            'status' => $status,
            'archived_at' => (
                $status === RepositoryStatus::Archived
                    ? now()
                    : null
            ),
        ]);

        app(RepositoryStorageService::class)
            ->initialize($repository);

        return [
            $repository->load('owner'),
            $owner,
        ];
    }

    /**
     * Return Git-compatible HTTP Basic authentication headers.
     *
     * @return array<string, string>
     */
    private function basicAuthHeaders(User $user): array
    {
        $plainTextToken = $user
            ->createToken('git-client')
            ->plainTextToken;

        return [
            'Authorization' => 'Basic '.base64_encode(
                "{$user->username}:{$plainTextToken}",
            ),
        ];
    }
}
