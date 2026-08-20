<?php

namespace Tests\Feature\Observability;

use App\Models\PersonalAccessToken;
use App\Models\Repository;
use App\Models\User;
use App\Services\Auth\PersonalAccessTokenService;
use App\Services\Git\GitCredentialAuthenticator;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Mockery;
use Tests\TestCase;

class ApplicationObservabilityTest extends TestCase
{
    use RefreshDatabase;

    private string $temporaryStorageRoot;

    protected function setUp(): void
    {
        parent::setUp();

        $this->temporaryStorageRoot = sys_get_temp_dir()
            .DIRECTORY_SEPARATOR
            .'gitforge-observability-tests-'
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

    public function test_failed_login_logs_classified_failure_without_credentials(): void
    {
        User::factory()->create([
            'email' => 'farhad@example.com',
            'password' => 'StrongPassword1!',
        ]);

        Log::spy();

        $email = 'farhad@example.com';
        $password = 'WrongPassword1!';

        $this->postJson(
            route('api.v1.auth.login'),
            [
                'email' => $email,
                'password' => $password,
            ],
        )->assertUnprocessable();

        Log::shouldHaveReceived(
            'warning',
        )->with(
            'authentication_failed',
            Mockery::on(
                function (array $context) use (
                    $email,
                    $password,
                ): bool {
                    $serialized = json_encode(
                        $context,
                    );

                    return $context['event'] === 'authentication_failed'
                        && is_string($context['request_id'])
                        && $context['request_id'] !== ''
                        && $context['authentication_type'] === 'session'
                        && $context['operation'] === 'login'
                        && $context['result'] === 'failure'
                        && $context['failure_category'] === 'authentication_failure'
                        && ! str_contains(
                            $serialized,
                            $email,
                        )
                        && ! str_contains(
                            $serialized,
                            $password,
                        )
                        && ! array_key_exists(
                            'password',
                            $context,
                        )
                        && ! array_key_exists(
                            'email',
                            $context,
                        );
                },
            ),
        )->once();
    }

    public function test_pat_creation_log_never_contains_plaintext_or_hash(): void
    {
        $user = User::factory()->create();

        Log::spy();

        $response = $this
            ->actingAs(
                $user,
                'web',
            )
            ->postJson(
                route('api.v1.personal-access-tokens.store'),
                [
                    'name' => 'Observability laptop',
                ],
            );

        $response->assertCreated();

        $plainTextToken = $response->json(
            'plain_text_token',
        );

        $storedToken = PersonalAccessToken::query()
            ->where(
                'user_id',
                $user->id,
            )
            ->firstOrFail();

        Log::shouldHaveReceived(
            'info',
        )->with(
            'pat_created',
            Mockery::on(
                function (array $context) use (
                    $user,
                    $storedToken,
                    $plainTextToken,
                ): bool {
                    $serialized = json_encode(
                        $context,
                    );

                    return $context['event'] === 'pat_created'
                        && $context['user_id'] === $user->id
                        && $context['token_id'] === $storedToken->id
                        && $context['token_identifier']
                            === $storedToken->token_identifier
                        && $context['result'] === 'success'
                        && ! str_contains(
                            $serialized,
                            $plainTextToken,
                        )
                        && ! str_contains(
                            $serialized,
                            $storedToken->token_hash,
                        )
                        && ! array_key_exists(
                            'plain_text_token',
                            $context,
                        )
                        && ! array_key_exists(
                            'token_hash',
                            $context,
                        );
                },
            ),
        )->once();
    }

    public function test_pat_authentication_failure_never_logs_basic_credentials(): void
    {
        $username = 'farhad';
        $plainTextToken = (
            'gitforge_pat_'
            .str_repeat('a', 32)
            .'_'
            .str_repeat('b', 64)
        );

        $request = Request::create(
            '/git/repository/info/refs',
            'GET',
            [],
            [],
            [],
            [
                'PHP_AUTH_USER' => $username,
                'PHP_AUTH_PW' => $plainTextToken,
            ],
        );

        $request->attributes->set(
            'request_id',
            'pat-failure-request-id',
        );

        Log::spy();

        try {
            app(
                GitCredentialAuthenticator::class,
            )->authenticate(
                $request,
            );

            $this->fail(
                'Invalid PAT authentication unexpectedly succeeded.',
            );
        } catch (\App\Exceptions\GitAuthenticationException) {
            //
        }

        Log::shouldHaveReceived(
            'warning',
        )->with(
            'pat_authentication_failed',
            Mockery::on(
                function (array $context) use (
                    $username,
                    $plainTextToken,
                ): bool {
                    $serialized = json_encode(
                        $context,
                    );

                    return $context['event'] === 'pat_authentication_failed'
                        && $context['request_id']
                            === 'pat-failure-request-id'
                        && $context['authentication_type'] === 'pat'
                        && $context['result'] === 'failure'
                        && $context['failure_category']
                            === 'authentication_failure'
                        && ! str_contains(
                            $serialized,
                            $username,
                        )
                        && ! str_contains(
                            $serialized,
                            $plainTextToken,
                        )
                        && ! array_key_exists(
                            'authorization',
                            $context,
                        )
                        && ! array_key_exists(
                            'password',
                            $context,
                        );
                },
            ),
        )->once();
    }

    public function test_repository_creation_logs_success_only_after_complete_creation(): void
    {
        $user = User::factory()->create([
            'username' => 'farhad',
        ]);

        Log::spy();

        $this
            ->actingAs(
                $user,
                'web',
            )
            ->postJson(
                route('api.v1.repositories.store'),
                [
                    'name' => 'observable-project',
                    'visibility' => 'private',
                ],
            )
            ->assertCreated();

        $repository = Repository::query()
            ->where(
                'owner_id',
                $user->id,
            )
            ->where(
                'name',
                'observable-project',
            )
            ->firstOrFail();

        $this->assertTrue(
            app(
                \App\Services\Git\RepositoryStorageService::class,
            )->exists(
                $repository,
            ),
        );

        Log::shouldHaveReceived(
            'info',
        )->with(
            'repository_create_succeeded',
            Mockery::on(
                function (array $context) use (
                    $repository,
                    $user,
                ): bool {
                    return $context['event']
                            === 'repository_create_succeeded'
                        && $context['repository_id']
                            === $repository->id
                        && $context['user_id']
                            === $user->id
                        && $context['result']
                            === 'success';
                },
            ),
        )->once();

        Log::shouldNotHaveReceived(
            'repository_create_failed',
        );
    }

    public function test_failed_repository_storage_never_logs_repository_success(): void
    {
        config([
            'git.initial_branch' => 'invalid..branch',
        ]);

        $user = User::factory()->create();

        Log::spy();

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

        Log::shouldHaveReceived(
            'error',
        )->with(
            'repository_create_failed',
            Mockery::on(
                function (array $context) use (
                    $user,
                ): bool {
                    return $context['event']
                            === 'repository_create_failed'
                        && $context['user_id']
                            === $user->id
                        && $context['result']
                            === 'failure'
                        && $context['failure_category']
                            === 'repository_consistency_failure';
                },
            ),
        )->once();

        Log::shouldNotHaveReceived(
            'repository_create_succeeded',
        );
    }

    public function test_visibility_change_logs_scalar_old_and_new_values(): void
    {
        $owner = User::factory()->create();

        $repository = Repository::factory()
            ->privateRepository()
            ->create([
                'owner_id' => $owner->id,
                'name' => 'visibility-project',
            ]);

        Log::spy();

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
                    'visibility' => 'public',
                ],
            )
            ->assertOk();

        Log::shouldHaveReceived(
            'info',
        )->with(
            'repository_visibility_changed',
            Mockery::on(
                function (array $context) use (
                    $repository,
                    $owner,
                ): bool {
                    return $context['event']
                            === 'repository_visibility_changed'
                        && $context['repository_id']
                            === $repository->id
                        && $context['user_id']
                            === $owner->id
                        && $context['old_visibility']
                            === 'private'
                        && $context['new_visibility']
                            === 'public'
                        && $context['result']
                            === 'success';
                },
            ),
        )->once();
    }
}