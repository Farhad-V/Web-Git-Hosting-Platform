<?php

namespace Tests\Feature\Performance;

use App\Models\Repository;
use App\Models\User;
use App\Services\Git\GitCommandRunner;
use App\Services\Git\RepositoryStorageService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Tests\TestCase;

class PerformanceTest extends TestCase
{
    use RefreshDatabase;

    private const SAMPLE_SIZE = 20;

    private const WEB_RESPONSE_THRESHOLD_SECONDS = 2.0;

    private const BROWSER_RESPONSE_THRESHOLD_SECONDS = 3.0;

    private string $temporaryRoot;

    private string $storageRoot;

    private string $worktreePath;

    protected function setUp(): void
    {
        parent::setUp();

        $this->temporaryRoot = sys_get_temp_dir()
            .DIRECTORY_SEPARATOR
            .'gitforge-performance-tests-'
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

    /**
     * TC-PERF-001
     *
     * NFR-023 — Interactive Web Response Time
     * NFR-024 — Repository Metadata Response Time
     *
     * Measure representative public repository metadata requests under
     * controlled local test conditions and verify that the 95th percentile
     * completes within the approved two-second threshold.
     */
    public function test_repository_metadata_response_p95_meets_approved_threshold(): void
    {
        $owner = User::factory()->create([
            'username' => 'performance-owner',
        ]);

        $repository = Repository::factory()
            ->publicRepository()
            ->create([
                'owner_id' => $owner->id,
                'name' => 'performance-project',
            ]);

        app(
            RepositoryStorageService::class,
        )->initialize(
            $repository,
        );

        $durations = [];

        for ($iteration = 1; $iteration <= self::SAMPLE_SIZE; $iteration++) {
            $startedAt = hrtime(true);

            $response = $this->getJson(
                route(
                    'api.v1.repositories.show',
                    $repository,
                ),
            );

            $elapsedSeconds = (
                hrtime(true) - $startedAt
            ) / 1_000_000_000;

            $response
                ->assertOk()
                ->assertJsonPath(
                    'data.id',
                    $repository->id,
                );

            $durations[] = $elapsedSeconds;
        }

        $p95 = $this->percentile95(
            $durations,
        );

        fwrite(
            STDOUT,
            sprintf(
                PHP_EOL
                .'TC-PERF-001 metadata: samples=%d p95=%.6fs threshold=%.1fs'
                .PHP_EOL,
                self::SAMPLE_SIZE,
                $p95,
                self::WEB_RESPONSE_THRESHOLD_SECONDS,
            ),
        );

        $this->assertLessThanOrEqual(
            self::WEB_RESPONSE_THRESHOLD_SECONDS,
            $p95,
            sprintf(
                'Repository metadata p95 response time %.6fs exceeded the approved %.1fs threshold.',
                $p95,
                self::WEB_RESPONSE_THRESHOLD_SECONDS,
            ),
        );
    }

    /**
     * TC-PERF-001
     *
     * NFR-025 — Repository Browser Response Time
     *
     * Measure a representative repository-browser operation against an
     * ordinary portfolio-scale repository fixture and verify that the 95th
     * percentile completes within the approved three-second threshold.
     */
    public function test_repository_browser_response_p95_meets_approved_threshold(): void
    {
        $owner = User::factory()->create([
            'username' => 'browser-performance-owner',
        ]);

        $repository = $this->createPublicRepositoryWithHistory(
            owner: $owner,
        );

        $durations = [];

        for ($iteration = 1; $iteration <= self::SAMPLE_SIZE; $iteration++) {
            $startedAt = hrtime(true);

            $response = $this->getJson(
                route(
                    'api.v1.repositories.commits',
                    [
                        'repository' => $repository,
                        'branch' => 'main',
                        'limit' => 10,
                    ],
                ),
            );

            $elapsedSeconds = (
                hrtime(true) - $startedAt
            ) / 1_000_000_000;

            $response
                ->assertOk()
                ->assertJsonPath(
                    'branch',
                    'main',
                )
                ->assertJsonPath(
                    'data.0.subject',
                    'Initial performance repository content',
                );

            $durations[] = $elapsedSeconds;
        }

        $p95 = $this->percentile95(
            $durations,
        );

        fwrite(
            STDOUT,
            sprintf(
                PHP_EOL
                .'TC-PERF-001 browser: samples=%d p95=%.6fs threshold=%.1fs'
                .PHP_EOL,
                self::SAMPLE_SIZE,
                $p95,
                self::BROWSER_RESPONSE_THRESHOLD_SECONDS,
            ),
        );

        $this->assertLessThanOrEqual(
            self::BROWSER_RESPONSE_THRESHOLD_SECONDS,
            $p95,
            sprintf(
                'Repository browser p95 response time %.6fs exceeded the approved %.1fs threshold.',
                $p95,
                self::BROWSER_RESPONSE_THRESHOLD_SECONDS,
            ),
        );
    }

    /**
     * @param  array<int, float>  $durations
     */
    private function percentile95(
        array $durations,
    ): float {
        sort(
            $durations,
            SORT_NUMERIC,
        );

        $index = (int) ceil(
            count($durations) * 0.95,
        ) - 1;

        return $durations[
            max(
                0,
                $index,
            )
        ];
    }

    private function createPublicRepositoryWithHistory(
        User $owner,
    ): Repository {
        $repository = Repository::factory()
            ->publicRepository()
            ->create([
                'owner_id' => $owner->id,
                'name' => 'browser-performance-project',
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
            operation: 'initialize-performance-worktree',
            arguments: [
                'init',
                '--initial-branch=main',
            ],
            workingDirectory: $this->worktreePath,
        );

        $runner->run(
            operation: 'configure-performance-user-name',
            arguments: [
                'config',
                'user.name',
                'GitForge Performance Test',
            ],
            workingDirectory: $this->worktreePath,
        );

        $runner->run(
            operation: 'configure-performance-user-email',
            arguments: [
                'config',
                'user.email',
                'performance@example.test',
            ],
            workingDirectory: $this->worktreePath,
        );

        File::put(
            $this->worktreePath
                .DIRECTORY_SEPARATOR
                .'README.md',
            "# GitForge Performance Fixture\n\nRepresentative repository content.\n",
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
            operation: 'stage-performance-files',
            arguments: [
                'add',
                'README.md',
                'src/app.php',
            ],
            workingDirectory: $this->worktreePath,
        );

        $runner->run(
            operation: 'commit-performance-files',
            arguments: [
                'commit',
                '-m',
                'Initial performance repository content',
            ],
            workingDirectory: $this->worktreePath,
        );

        $runner->run(
            operation: 'add-performance-remote',
            arguments: [
                'remote',
                'add',
                'origin',
                $barePath,
            ],
            workingDirectory: $this->worktreePath,
        );

        $runner->run(
            operation: 'push-performance-history',
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
