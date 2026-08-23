<?php

namespace Tests\Feature\Git;

use App\Enums\RepositoryVisibility;
use App\Models\PersonalAccessToken;
use App\Models\Repository;
use App\Models\User;
use App\Services\Git\GitCommandRunner;
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
            route(
                'git.info-refs',
                [
                    'username' => $repository->owner->username,
                    'repository' => $repository->name,
                    'service' => 'git-upload-pack',
                ],
            ),
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

        $cacheControl = (string) $response->headers->get(
            'Cache-Control',
        );

        $this->assertStringContainsString(
            'no-cache',
            $cacheControl,
        );

        $this->assertStringContainsString(
            'max-age=0',
            $cacheControl,
        );

        $this->assertStringContainsString(
            'must-revalidate',
            $cacheControl,
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
            route(
                'git.info-refs',
                [
                    'username' => $repository->owner->username,
                    'repository' => $repository->name,
                    'service' => 'git-upload-pack',
                ],
            ),
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
                $this->basicAuthHeaders(
                    $owner,
                ),
            )
            ->get(
                route(
                    'git.info-refs',
                    [
                        'username' => $owner->username,
                        'repository' => $repository->name,
                        'service' => 'git-upload-pack',
                    ],
                ),
            );

        $response
            ->assertOk()
            ->assertHeader(
                'Content-Type',
                'application/x-git-upload-pack-advertisement',
            );
    }

    public function test_public_repository_accepts_upload_pack_rpc_without_authentication(): void
    {
        [$repository, $owner] = $this->createManagedRepository(
            visibility: RepositoryVisibility::PublicAccess,
        );

        $response = $this->call(
            'POST',
            route(
                'git.upload-pack',
                [
                    'username' => $owner->username,
                    'repository' => $repository->name,
                ],
            ),
            [],
            [],
            [],
            [
                'CONTENT_TYPE' => (
                    'application/x-git-upload-pack-request'
                ),
            ],
            '0000',
        );

        $response
            ->assertOk()
            ->assertHeader(
                'Content-Type',
                'application/x-git-upload-pack-result',
            )
            ->assertHeader(
                'X-Content-Type-Options',
                'nosniff',
            );
    }

    public function test_private_upload_pack_rpc_requires_authentication(): void
    {
        [$repository, $owner] = $this->createManagedRepository(
            visibility: RepositoryVisibility::PrivateAccess,
        );

        $response = $this->call(
            'POST',
            route(
                'git.upload-pack',
                [
                    'username' => $owner->username,
                    'repository' => $repository->name,
                ],
            ),
            [],
            [],
            [],
            [
                'CONTENT_TYPE' => (
                    'application/x-git-upload-pack-request'
                ),
            ],
            '0000',
        );

        $response
            ->assertUnauthorized()
            ->assertHeader(
                'WWW-Authenticate',
                'Basic realm="Git Hosting Platform", charset="UTF-8"',
            );
    }

    public function test_owner_token_can_execute_private_upload_pack_rpc(): void
    {
        [$repository, $owner] = $this->createManagedRepository(
            visibility: RepositoryVisibility::PrivateAccess,
        );

        $headers = $this->basicAuthHeaders(
            $owner,
        );

        $response = $this->call(
            'POST',
            route(
                'git.upload-pack',
                [
                    'username' => $owner->username,
                    'repository' => $repository->name,
                ],
            ),
            [],
            [],
            [],
            [
                'CONTENT_TYPE' => (
                    'application/x-git-upload-pack-request'
                ),
                'HTTP_AUTHORIZATION' => $headers['Authorization'],
            ],
            '0000',
        );

        $response
            ->assertOk()
            ->assertHeader(
                'Content-Type',
                'application/x-git-upload-pack-result',
            );
    }

    public function test_oversized_upload_pack_rpc_is_rejected(): void
    {
        config([
            'git.max_request_bytes' => 4,
        ]);

        [$repository, $owner] = $this->createManagedRepository(
            visibility: RepositoryVisibility::PublicAccess,
        );

        $response = $this->call(
            'POST',
            route(
                'git.upload-pack',
                [
                    'username' => $owner->username,
                    'repository' => $repository->name,
                ],
            ),
            [],
            [],
            [],
            [
                'CONTENT_TYPE' => (
                    'application/x-git-upload-pack-request'
                ),
            ],
            '12345',
        );

        $response
            ->assertStatus(413)
            ->assertHeader(
                'Content-Type',
                'text/plain; charset=UTF-8',
            );

        $this->assertSame(
            'Git request body exceeds the configured limit.',
            $response->getContent(),
        );
    }

    public function test_corrupt_repository_storage_fails_closed_for_upload_pack_advertisement(): void
    {
        [$repository, $owner] = $this->createManagedRepository(
            visibility: RepositoryVisibility::PublicAccess,
        );

        $repositoryPath = app(
            RepositoryStorageService::class,
        )->repositoryPath(
            $repository,
        );

        File::delete(
            $repositoryPath
                .DIRECTORY_SEPARATOR
                .'config',
        );

        $response = $this->get(
            route(
                'git.info-refs',
                [
                    'username' => $owner->username,
                    'repository' => $repository->name,
                    'service' => 'git-upload-pack',
                ],
            ),
        );

        $response
            ->assertInternalServerError()
            ->assertHeader(
                'Content-Type',
                'text/plain; charset=UTF-8',
            );

        $this->assertSame(
            'Git transport operation failed.',
            $response->getContent(),
        );

        $this->assertStringNotContainsString(
            $repositoryPath,
            $response->getContent(),
        );
    }

    public function test_git_transport_failure_does_not_expose_git_stderr(): void
    {
        [$repository, $owner] = $this->createManagedRepository(
            visibility: RepositoryVisibility::PublicAccess,
        );

        $response = $this->call(
            'POST',
            route(
                'git.upload-pack',
                [
                    'username' => $owner->username,
                    'repository' => $repository->name,
                ],
            ),
            [],
            [],
            [],
            [
                'CONTENT_TYPE' => (
                    'application/x-git-upload-pack-request'
                ),
            ],
            'garbage',
        );

        $response
            ->assertInternalServerError()
            ->assertHeader(
                'Content-Type',
                'text/plain; charset=UTF-8',
            );

        $body = $response->getContent();

        $this->assertSame(
            'Git transport operation failed.',
            $body,
        );

        $this->assertStringNotContainsString(
            'fatal:',
            mb_strtolower($body),
        );

        $this->assertStringNotContainsString(
            'protocol error',
            mb_strtolower($body),
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
                route(
                    'git.info-refs',
                    [
                        'username' => $owner->username,
                        'repository' => $repository->name,
                        'service' => 'git-upload-pack',
                    ],
                ),
            )
            ->assertUnauthorized();
    }

    public function test_revoked_token_cannot_access_private_repository(): void
    {
        [$repository, $owner] = $this->createManagedRepository(
            visibility: RepositoryVisibility::PrivateAccess,
        );

        [$plainTextToken, $token] = $this->createGitToken(
            $owner,
        );

        $token->forceFill([
            'revoked_at' => now(),
        ])->save();

        $this
            ->withHeader(
                'Authorization',
                $this->basicAuthorization(
                    $owner,
                    $plainTextToken,
                ),
            )
            ->get(
                route(
                    'git.info-refs',
                    [
                        'username' => $owner->username,
                        'repository' => $repository->name,
                        'service' => 'git-upload-pack',
                    ],
                ),
            )
            ->assertUnauthorized();
    }

    public function test_token_username_must_match_token_owner_namespace(): void
    {
        [$repository, $owner] = $this->createManagedRepository(
            visibility: RepositoryVisibility::PrivateAccess,
        );

        $otherUser = User::factory()->create([
            'username' => 'other-user',
        ]);

        [$plainTextToken] = $this->createGitToken(
            $owner,
        );

        $this
            ->withHeader(
                'Authorization',
                $this->basicAuthorization(
                    $otherUser,
                    $plainTextToken,
                ),
            )
            ->get(
                route(
                    'git.info-refs',
                    [
                        'username' => $owner->username,
                        'repository' => $repository->name,
                        'service' => 'git-upload-pack',
                    ],
                ),
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
                $this->basicAuthHeaders(
                    $owner,
                ),
            )
            ->get(
                route(
                    'git.info-refs',
                    [
                        'username' => $owner->username,
                        'repository' => $repository->name,
                        'service' => 'git-receive-pack',
                    ],
                ),
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
                $this->basicAuthHeaders(
                    $otherUser,
                ),
            )
            ->get(
                route(
                    'git.info-refs',
                    [
                        'username' => $owner->username,
                        'repository' => $repository->name,
                        'service' => 'git-receive-pack',
                    ],
                ),
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
            route(
                'git.upload-pack',
                [
                    'username' => $owner->username,
                    'repository' => $repository->name,
                ],
            ),
            [],
            [],
            [],
            [
                'CONTENT_TYPE' => 'application/json',
            ],
            '0000',
        )->assertUnsupportedMediaType();
    }

    public function test_owner_token_can_execute_receive_pack_rpc(): void
    {
        [$repository, $owner] = $this->createManagedRepository(
            visibility: RepositoryVisibility::PublicAccess,
        );

        $headers = $this->basicAuthHeaders(
            $owner,
        );

        $response = $this->call(
            'POST',
            route(
                'git.receive-pack',
                [
                    'username' => $owner->username,
                    'repository' => $repository->name,
                ],
            ),
            [],
            [],
            [],
            [
                'CONTENT_TYPE' => (
                    'application/x-git-receive-pack-request'
                ),
                'HTTP_AUTHORIZATION' => $headers['Authorization'],
            ],
            '0000',
        );

        $response
            ->assertOk()
            ->assertHeader(
                'Content-Type',
                'application/x-git-receive-pack-result',
            )
            ->assertHeader(
                'X-Content-Type-Options',
                'nosniff',
            );
    }

    public function test_non_owner_cannot_execute_receive_pack_rpc(): void
    {
        [$repository, $owner] = $this->createManagedRepository(
            visibility: RepositoryVisibility::PublicAccess,
        );

        $repositoryPath = app(
            RepositoryStorageService::class,
        )->repositoryPath(
            $repository,
        );

        $this->seedMainRef(
            $repositoryPath,
        );

        $refsBefore = $this->showRefs(
            $repositoryPath,
        );

        $otherUser = User::factory()->create([
            'username' => 'other-user',
        ]);

        $headers = $this->basicAuthHeaders(
            $otherUser,
        );

        $response = $this->call(
            'POST',
            route(
                'git.receive-pack',
                [
                    'username' => $owner->username,
                    'repository' => $repository->name,
                ],
            ),
            [],
            [],
            [],
            [
                'CONTENT_TYPE' => (
                    'application/x-git-receive-pack-request'
                ),
                'HTTP_AUTHORIZATION' => $headers['Authorization'],
            ],
            '0000',
        );

        $response->assertForbidden();

        $refsAfter = $this->showRefs(
            $repositoryPath,
        );

        $this->assertSame(
            $refsBefore,
            $refsAfter,
        );
    }

    public function test_receive_pack_rejects_incorrect_content_type(): void
    {
        [$repository, $owner] = $this->createManagedRepository(
            visibility: RepositoryVisibility::PublicAccess,
        );

        $headers = $this->basicAuthHeaders(
            $owner,
        );

        $response = $this->call(
            'POST',
            route(
                'git.receive-pack',
                [
                    'username' => $owner->username,
                    'repository' => $repository->name,
                ],
            ),
            [],
            [],
            [],
            [
                'CONTENT_TYPE' => 'application/json',
                'HTTP_AUTHORIZATION' => $headers['Authorization'],
            ],
            '0000',
        );

        $response->assertUnsupportedMediaType();
    }

    public function test_oversized_receive_pack_rpc_is_rejected(): void
    {
        config([
            'git.max_request_bytes' => 4,
        ]);

        [$repository, $owner] = $this->createManagedRepository(
            visibility: RepositoryVisibility::PublicAccess,
        );

        $headers = $this->basicAuthHeaders(
            $owner,
        );

        $response = $this->call(
            'POST',
            route(
                'git.receive-pack',
                [
                    'username' => $owner->username,
                    'repository' => $repository->name,
                ],
            ),
            [],
            [],
            [],
            [
                'CONTENT_TYPE' => (
                    'application/x-git-receive-pack-request'
                ),
                'HTTP_AUTHORIZATION' => $headers['Authorization'],
            ],
            '12345',
        );

        $response
            ->assertStatus(413)
            ->assertHeader(
                'Content-Type',
                'text/plain; charset=UTF-8',
            );

        $this->assertSame(
            'Git request body exceeds the configured limit.',
            $response->getContent(),
        );
    }

    public function test_corrupt_repository_storage_fails_closed_for_receive_pack_rpc(): void
    {
        [$repository, $owner] = $this->createManagedRepository(
            visibility: RepositoryVisibility::PublicAccess,
        );

        $repositoryPath = app(
            RepositoryStorageService::class,
        )->repositoryPath(
            $repository,
        );

        File::delete(
            $repositoryPath
                .DIRECTORY_SEPARATOR
                .'config',
        );

        $headers = $this->basicAuthHeaders(
            $owner,
        );

        $response = $this->call(
            'POST',
            route(
                'git.receive-pack',
                [
                    'username' => $owner->username,
                    'repository' => $repository->name,
                ],
            ),
            [],
            [],
            [],
            [
                'CONTENT_TYPE' => (
                    'application/x-git-receive-pack-request'
                ),
                'HTTP_AUTHORIZATION' => $headers['Authorization'],
            ],
            '0000',
        );

        $response
            ->assertInternalServerError()
            ->assertHeader(
                'Content-Type',
                'text/plain; charset=UTF-8',
            );

        $this->assertSame(
            'Git transport operation failed.',
            $response->getContent(),
        );

        $this->assertStringNotContainsString(
            $repositoryPath,
            $response->getContent(),
        );
    }

    public function test_receive_pack_requires_authentication(): void
    {
        [$repository, $owner] = $this->createManagedRepository(
            visibility: RepositoryVisibility::PublicAccess,
        );

        $this->call(
            'POST',
            route(
                'git.receive-pack',
                [
                    'username' => $owner->username,
                    'repository' => $repository->name,
                ],
            ),
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
            route(
                'git.info-refs',
                [
                    'username' => $owner->username,
                    'repository' => $repository->name,
                    'service' => 'git-unsupported-pack',
                ],
            ),
        )->assertBadRequest();
    }

    public function test_repository_name_with_period_and_underscore_is_routable(): void
    {
        $owner = User::factory()->create([
            'username' => 'farhad',
        ]);

        $repository = Repository::factory()
            ->publicRepository()
            ->create([
                'owner_id' => $owner->id,
                'name' => 'project.api_v1',
            ]);

        app(RepositoryStorageService::class)
            ->initialize(
                $repository,
            );

        $this
            ->get(
                route(
                    'git.info-refs',
                    [
                        'username' => $owner->username,
                        'repository' => $repository->name,
                        'service' => 'git-upload-pack',
                    ],
                ),
            )
            ->assertOk();
    }

    /**
     * Create repository metadata and real managed bare storage.
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

        app(RepositoryStorageService::class)
            ->initialize(
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
     * Create a GitForge PAT and return the plaintext and persisted token.
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
                'name' => 'Git client',
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
     * Seed the bare repository with one known main branch ref.
     */
    private function seedMainRef(
        string $repositoryPath,
    ): void {
        $git = app(
            GitCommandRunner::class,
        );

        $blob = $git->run(
            operation: 'test_seed_blob',
            arguments: [
                '--git-dir='.$repositoryPath,
                'hash-object',
                '-w',
                '--stdin',
            ],
            input: "initial\n",
        );

        $tree = $git->run(
            operation: 'test_seed_tree',
            arguments: [
                '--git-dir='.$repositoryPath,
                'mktree',
            ],
            input: "100644 blob {$blob}\tREADME.md\n",
        );

        $commit = $git->run(
            operation: 'test_seed_commit',
            arguments: [
                '-c',
                'user.name=GitForge Test',
                '-c',
                'user.email=gitforge@example.test',
                '--git-dir='.$repositoryPath,
                'commit-tree',
                $tree,
                '-m',
                'Initial commit',
            ],
        );

        $git->run(
            operation: 'test_seed_main_ref',
            arguments: [
                '--git-dir='.$repositoryPath,
                'update-ref',
                'refs/heads/main',
                $commit,
            ],
        );
    }

    /**
     * Return the complete repository ref state for integrity comparison.
     */
    private function showRefs(
        string $repositoryPath,
    ): string {
        return app(
            GitCommandRunner::class,
        )->run(
            operation: 'test_show_refs',
            arguments: [
                '--git-dir='.$repositoryPath,
                'show-ref',
            ],
        );
    }

    /**
     * Return Git-compatible HTTP Basic authentication headers.
     *
     * @return array<string, string>
     */
    private function basicAuthHeaders(
        User $user,
    ): array {
        [$plainTextToken] = $this->createGitToken(
            $user,
        );

        return [
            'Authorization' => $this->basicAuthorization(
                $user,
                $plainTextToken,
            ),
        ];
    }

    /**
     * Build a Git-compatible Basic authorization value.
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
