<?php

namespace Tests\Feature\Security;

use App\Exceptions\RepositoryBrowserException;
use App\Models\Repository;
use App\Services\Git\GitCommandRunner;
use App\Services\Git\RepositoryBrowserService;
use App\Services\Git\RepositoryStorageService;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Mockery;
use Tests\TestCase;

class ProcessInjectionSecurityTest extends TestCase
{
    private string $temporaryDirectory;

    protected function setUp(): void
    {
        parent::setUp();

        $this->temporaryDirectory = sys_get_temp_dir()
            .DIRECTORY_SEPARATOR
            .'gitforge-process-security-'
            .Str::uuid();

        File::ensureDirectoryExists(
            $this->temporaryDirectory,
        );
    }

    protected function tearDown(): void
    {
        if (
            isset($this->temporaryDirectory)
            && File::isDirectory($this->temporaryDirectory)
        ) {
            File::deleteDirectory(
                $this->temporaryDirectory,
            );
        }

        parent::tearDown();
    }

    public function test_process_arguments_are_not_interpreted_by_a_shell(): void
    {
        config([
            'git.binary' => PHP_BINARY,
            'git.timeout_seconds' => 10,
        ]);

        $marker = $this->temporaryDirectory
            .DIRECTORY_SEPARATOR
            .'command-injection-marker.txt';

        $payload = 'safe-value && echo compromised > "'
            .$marker
            .'"';

        $output = app(
            GitCommandRunner::class,
        )->run(
            operation: 'command-injection-security-test',
            arguments: [
                '-r',
                'echo $argv[1];',
                $payload,
            ],
        );

        $this->assertSame(
            $payload,
            $output,
        );

        $this->assertFileDoesNotExist(
            $marker,
        );
    }

    public function test_branch_name_cannot_be_used_as_git_option(): void
    {
        $repository = new Repository;

        $repository->forceFill([
            'id' => 501,
        ]);

        $git = Mockery::mock(
            GitCommandRunner::class,
        );

        $git->shouldNotReceive(
            'run',
        );

        $git->shouldNotReceive(
            'runRaw',
        );

        $storage = Mockery::mock(
            RepositoryStorageService::class,
        );

        $storage->shouldReceive(
            'exists',
        )
            ->once()
            ->with($repository)
            ->andReturn(true);

        $storage->shouldReceive(
            'resolveExisting',
        )
            ->once()
            ->with($repository)
            ->andReturn(
                $this->temporaryDirectory
                    .DIRECTORY_SEPARATOR
                    .'501.git',
            );

        $browser = new RepositoryBrowserService(
            git: $git,
            storage: $storage,
        );

        try {
            $browser->commits(
                repository: $repository,
                branch: '--help',
            );

            $this->fail(
                'A Git option-shaped branch name was accepted.',
            );
        } catch (RepositoryBrowserException $exception) {
            $this->assertSame(
                'invalid_branch',
                $exception->reason,
            );

            $this->assertSame(
                'resolve-branch',
                $exception->operation,
            );
        }
    }
}
