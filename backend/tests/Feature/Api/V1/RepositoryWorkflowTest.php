<?php

namespace Tests\Feature\Api\V1;

use App\Exceptions\GitCommandException;
use App\Jobs\RunRepositoryChecks;
use App\Models\PullRequest;
use App\Models\Repository;
use App\Models\RepositoryActionRun;
use App\Models\User;
use App\Services\Git\GitCommandRunner;
use App\Services\Git\RepositoryBrowserService;
use App\Services\Git\RepositoryStorageService;
use App\Services\Git\RepositoryWorkflowService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Str;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Tests\TestCase;

class RepositoryWorkflowTest extends TestCase
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

    private function git(array $arguments): string
    {
        return app(GitCommandRunner::class)->run(operation: 'workflow-test', arguments: $arguments, workingDirectory: $this->worktreePath);
    }

    private function propose(User $owner, Repository $repository): array
    {
        $this->git(['checkout', '-b', 'feature']);
        File::put($this->worktreePath.DIRECTORY_SEPARATOR.'README.md', "Feature content\n");
        $this->git(['add', 'README.md']);
        $this->git(['commit', '-m', 'Feature content']);
        $this->git(['push', 'origin', 'feature']);
        $response = $this->actingAs($owner, 'web')->postJson(route('api.v1.repositories.pulls.store', $repository), ['title' => 'Improve documentation', 'description' => 'A useful change', 'base_branch' => 'main', 'head_branch' => 'feature'])->assertCreated();

        return $response->json('data');
    }

    public function test_owner_can_create_branch_and_change_git_default(): void
    {
        $owner = User::factory()->create();
        $repository = $this->createRepositoryWithHistory($owner);
        $this->actingAs($owner, 'web')->postJson(route('api.v1.repositories.branches.store', $repository), ['name' => 'feature/new-ui', 'source' => 'main'])->assertCreated();
        $this->postJson(route('api.v1.repositories.branches.store', $repository), ['name' => 'feature/new-ui', 'source' => 'main'])->assertConflict();
        $this->patchJson(route('api.v1.repositories.branches.default', $repository), ['name' => 'feature/new-ui'])->assertOk();
        $this->getJson(route('api.v1.repositories.branches', $repository))->assertOk()->assertJsonPath('default_branch', 'feature/new-ui')->assertJsonCount(2, 'data');
        $this->postJson(route('api.v1.repositories.branches.store', $repository), ['name' => '../unsafe', 'source' => 'main'])->assertUnprocessable();
        $this->actingAs(User::factory()->create(), 'web')->postJson(route('api.v1.repositories.branches.store', $repository), ['name' => 'other', 'source' => 'main'])->assertForbidden();
    }

    public function test_pull_request_comparison_discussion_and_real_merge(): void
    {
        $owner = User::factory()->create();
        $repository = $this->createRepositoryWithHistory($owner);
        $pull = $this->propose($owner, $repository);
        $url = route('api.v1.repositories.pulls.show', [$repository, $pull['id']]);
        $comparison = $this->getJson($url)->assertOk()->assertJsonPath('comparison.commit_count', 1)->json('comparison');
        $this->assertStringContainsString('+Feature content', $comparison['diff']);
        $this->postJson(route('api.v1.repositories.pulls.comments.store', [$repository, $pull['id']]), ['body' => 'Ready for review'])->assertCreated();
        $this->getJson(route('api.v1.repositories.pulls.comments', [$repository, $pull['id']]))->assertOk()->assertJsonPath('data.0.body', 'Ready for review');
        $this->postJson(route('api.v1.repositories.pulls.store', $repository), ['title' => 'Duplicate', 'base_branch' => 'main', 'head_branch' => 'feature'])->assertConflict();
        $response = $this->postJson(route('api.v1.repositories.pulls.merge', [$repository, $pull['id']]), ['expected_base' => $comparison['base_commit'], 'expected_head' => $comparison['head_commit']])->assertOk()->assertJsonPath('data.status', 'merged');
        $hash = $response->json('data.merge_commit');
        $commit = app(RepositoryBrowserService::class)->commit($repository, $hash);
        $this->assertSame([$comparison['base_commit'], $comparison['head_commit']], $commit['parents']);
        $this->assertSame($hash, array_column(app(RepositoryBrowserService::class)->branches($repository), 'commit', 'name')['main']);
        $this->patchJson(route('api.v1.repositories.pulls.update', [$repository, $pull['id']]), ['status' => 'open'])->assertConflict();
        // Simulate the database write being interrupted after the Git transaction.
        PullRequest::findOrFail($pull['id'])->update(['status' => 'merging', 'merged_at' => null]);
        $this->getJson($url)->assertOk()->assertJsonPath('data.status', 'merged');
    }

    public function test_unreviewed_branch_changes_and_conflicts_do_not_move_base(): void
    {
        $owner = User::factory()->create();
        $repository = $this->createRepositoryWithHistory($owner);
        $pull = $this->propose($owner, $repository);
        $url = route('api.v1.repositories.pulls.show', [$repository, $pull['id']]);
        $comparison = $this->getJson($url)->json('comparison');
        $this->git(['commit', '--allow-empty', '-m', 'Unreviewed change']);
        $this->git(['push', 'origin', 'feature']);
        $mergeUrl = route('api.v1.repositories.pulls.merge', [$repository, $pull['id']]);
        $this->postJson($mergeUrl, ['expected_base' => $comparison['base_commit'], 'expected_head' => $comparison['head_commit']])->assertConflict();
        $this->git(['checkout', 'main']);
        File::put($this->worktreePath.DIRECTORY_SEPARATOR.'README.md', "Conflicting base content\n");
        $this->git(['add', 'README.md']);
        $this->git(['commit', '-m', 'Base change']);
        $this->git(['push', 'origin', 'main']);
        $comparison = $this->getJson($url)->json('comparison');
        $this->postJson($mergeUrl, ['expected_base' => $comparison['base_commit'], 'expected_head' => $comparison['head_commit']])->assertConflict();
        $this->assertSame('open', PullRequest::findOrFail($pull['id'])->status);
        $this->assertSame($comparison['base_commit'], array_column(app(RepositoryBrowserService::class)->branches($repository), 'commit', 'name')['main']);
    }

    public function test_public_contributors_can_discuss_but_only_owner_can_merge(): void
    {
        $owner = User::factory()->create();
        $repository = $this->createRepositoryWithHistory($owner, public: true);
        $pull = $this->propose($owner, $repository);
        $this->actingAs(User::factory()->create(), 'web');
        $this->postJson(route('api.v1.repositories.pulls.comments.store', [$repository, $pull['id']]), ['body' => 'Public review'])->assertCreated();
        $this->postJson(route('api.v1.repositories.pulls.merge', [$repository, $pull['id']]), [])->assertForbidden();
        $this->patchJson(route('api.v1.repositories.pulls.update', [$repository, $pull['id']]), ['status' => 'closed'])->assertForbidden();
        $this->actingAs($owner, 'web')->patchJson(route('api.v1.repositories.pulls.update', [$repository, $pull['id']]), ['status' => 'closed'])->assertOk();
        $this->postJson(route('api.v1.repositories.pulls.merge', [$repository, $pull['id']]), ['expected_base' => str_repeat('a', 40), 'expected_head' => str_repeat('b', 40)])->assertConflict();
        $this->patchJson(route('api.v1.repositories.pulls.update', [$repository, $pull['id']]), ['status' => 'open'])->assertOk();
        $other = Repository::factory()->publicRepository()->create(['owner_id' => $owner->id]);
        $this->getJson(route('api.v1.repositories.pulls.comments', [$other, $pull['id']]))->assertNotFound();
    }

    public function test_actions_pin_commit_and_persist_real_checks(): void
    {
        Queue::fake();
        $owner = User::factory()->create();
        $repository = $this->createRepositoryWithHistory($owner);
        $run = $this->actingAs($owner, 'web')->postJson(route('api.v1.repositories.actions.store', $repository), ['branch' => 'main'])->assertAccepted()->assertJsonPath('data.status', 'queued')->json('data');
        Queue::assertPushed(RunRepositoryChecks::class, fn ($job) => $job->runId === $run['id'] && $job->queue === 'gitforge-actions');
        $this->postJson(route('api.v1.repositories.actions.store', $repository), ['branch' => 'main'])->assertConflict();
        $this->git(['commit', '--allow-empty', '-m', 'Later commit']);
        $this->git(['push', 'origin', 'main']);
        (new RunRepositoryChecks($run['id']))->handle(app(GitCommandRunner::class), app(RepositoryStorageService::class));
        $this->getJson(route('api.v1.repositories.actions.show', [$repository, $run['id']]))->assertOk()->assertJsonPath('data.status', 'success')->assertJsonPath('data.commit', $run['commit'])->assertJsonCount(3, 'data.steps');
        $this->getJson(route('api.v1.repositories.actions', $repository))->assertOk()->assertJsonCount(1, 'data');
    }

    public function test_cancelled_and_failed_actions_are_not_reported_as_success(): void
    {
        Queue::fake();
        $owner = User::factory()->create();
        $repository = $this->createRepositoryWithHistory($owner);
        $run = $this->actingAs($owner, 'web')->postJson(route('api.v1.repositories.actions.store', $repository), ['branch' => 'main'])->assertAccepted()->json('data');
        $this->postJson(route('api.v1.repositories.actions.cancel', [$repository, $run['id']]))->assertOk();
        (new RunRepositoryChecks($run['id']))->handle(app(GitCommandRunner::class), app(RepositoryStorageService::class));
        $this->assertSame('cancelled', RepositoryActionRun::findOrFail($run['id'])->status);
        $failed = RepositoryActionRun::create(['repository_id' => $repository->id, 'actor_id' => $owner->id, 'branch' => 'main', 'commit' => str_repeat('a', 40)]);
        (new RunRepositoryChecks($failed->id))->handle(app(GitCommandRunner::class), app(RepositoryStorageService::class));
        $this->assertSame('failure', $failed->fresh()->status);
        $this->assertStringNotContainsString($this->storageRoot, $failed->fresh()->log);
        $this->actingAs(User::factory()->create(), 'web')->postJson(route('api.v1.repositories.actions.store', $repository), ['branch' => 'main'])->assertForbidden();
    }

    public function test_private_workflow_reads_are_concealed_and_anonymous_writes_denied(): void
    {
        $repository = Repository::factory()->create();
        foreach (['repositories.pulls', 'repositories.actions'] as $route) {
            $this->getJson(route('api.v1.'.$route, $repository))->assertNotFound();
        }
        $this->postJson(route('api.v1.repositories.pulls.store', $repository), ['title' => 'No'])->assertUnauthorized();
        $this->postJson(route('api.v1.repositories.actions.store', $repository), ['branch' => 'main'])->assertUnauthorized();
    }

    public function test_closing_or_pushing_during_merge_cannot_update_base(): void
    {
        $owner = User::factory()->create();
        $repository = $this->createRepositoryWithHistory($owner);
        $data = $this->propose($owner, $repository);
        $pull = PullRequest::findOrFail($data['id']);
        $service = app(RepositoryWorkflowService::class);
        $comparison = $service->comparison($repository, 'main', 'feature');
        $storage = app(RepositoryStorageService::class);
        $path = $storage->resolveExisting($repository);
        foreach (['close', 'push'] as $race) {
            $pull->refresh()->update(['status' => 'open']);
            $runner = new class($race, $pull->id, $path, $comparison['base_commit']) extends GitCommandRunner
            {
                private bool $triggered = false;

                public function __construct(private string $race, private int $pullId, private string $path, private string $base) {}

                public function run(string $operation, array $arguments, ?string $workingDirectory = null, mixed $input = null): string
                {
                    $result = parent::run($operation, $arguments, $workingDirectory, $input);
                    if (! $this->triggered && in_array('merge-tree', $arguments, true)) {
                        $this->triggered = true;
                        if ($this->race === 'close') {
                            PullRequest::whereKey($this->pullId)->update(['status' => 'closed']);
                        } else {
                            parent::run('concurrent-push-test', ['--git-dir='.$this->path, 'update-ref', 'refs/heads/feature', $this->base]);
                        }
                    }

                    return $result;
                }
            };
            try {
                (new RepositoryWorkflowService($runner, $storage))->merge($repository, $pull, $owner, $comparison['base_commit'], $comparison['head_commit']);
                $this->fail('A concurrent change must prevent merging.');
            } catch (HttpException $exception) {
                $this->assertSame(409, $exception->getStatusCode());
            }
            $this->assertSame($comparison['base_commit'], array_column(app(RepositoryBrowserService::class)->branches($repository), 'commit', 'name')['main']);
            $this->assertSame($race === 'close' ? 'closed' : 'open', $pull->fresh()->status);
        }
    }

    public function test_large_non_utf8_diff_has_a_safe_bounded_preview(): void
    {
        $owner = User::factory()->create();
        $repository = $this->createRepositoryWithHistory($owner);
        $pull = $this->propose($owner, $repository);
        File::put($this->worktreePath.DIRECTORY_SEPARATOR.'README.md', str_repeat("Text \xff\n", 45000));
        $this->git(['add', 'README.md']);
        $this->git(['commit', '-m', 'Large text change']);
        $this->git(['push', 'origin', 'feature']);
        $response = $this->getJson(route('api.v1.repositories.pulls.show', [$repository, $pull['id']]))->assertOk()->assertJsonPath('comparison.diff_truncated', true);
        $this->assertTrue(mb_check_encoding($response->json('comparison.diff'), 'UTF-8'));
    }

    public function test_cancelling_a_running_check_preserves_cancelled_status(): void
    {
        $owner = User::factory()->create();
        $repository = $this->createRepositoryWithHistory($owner);
        $hash = $this->git(['rev-parse', 'HEAD']);
        $run = RepositoryActionRun::create(['repository_id' => $repository->id, 'actor_id' => $owner->id, 'branch' => 'main', 'commit' => $hash]);
        $runner = new class($run->id) extends GitCommandRunner
        {
            public function __construct(private int $runId) {}

            public function run(string $operation, array $arguments, ?string $workingDirectory = null, mixed $input = null): string
            {
                $result = parent::run($operation, $arguments, $workingDirectory, $input);
                if ($operation === 'repository-action-check') {
                    RepositoryActionRun::whereKey($this->runId)->update(['status' => 'cancelled', 'finished_at' => now(), 'log' => 'Cancelled by owner.']);
                }

                return $result;
            }
        };
        (new RunRepositoryChecks($run->id))->handle($runner, app(RepositoryStorageService::class));
        $this->assertSame('cancelled', $run->fresh()->status);
        $this->assertSame('Cancelled by owner.', $run->fresh()->log);
    }

    public function test_a_lost_git_response_after_commit_is_reconciled_as_merged(): void
    {
        $owner = User::factory()->create();
        $repository = $this->createRepositoryWithHistory($owner);
        $data = $this->propose($owner, $repository);
        $pull = PullRequest::findOrFail($data['id']);
        $service = app(RepositoryWorkflowService::class);
        $comparison = $service->comparison($repository, 'main', 'feature');
        $runner = new class extends GitCommandRunner
        {
            public function run(string $operation, array $arguments, ?string $workingDirectory = null, mixed $input = null): string
            {
                $result = parent::run($operation, $arguments, $workingDirectory, $input);
                if (in_array('update-ref', $arguments, true) && in_array('--stdin', $arguments, true)) {
                    throw new GitCommandException('simulated-lost-response', 124, '');
                }

                return $result;
            }
        };
        $merged = (new RepositoryWorkflowService($runner, app(RepositoryStorageService::class)))->merge($repository, $pull, $owner, $comparison['base_commit'], $comparison['head_commit']);
        $this->assertSame('merged', $merged->status);
        $this->assertSame($merged->merge_commit, array_column(app(RepositoryBrowserService::class)->branches($repository), 'commit', 'name')['main']);
    }

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
