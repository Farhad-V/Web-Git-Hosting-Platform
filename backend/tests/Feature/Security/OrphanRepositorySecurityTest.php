<?php

namespace Tests\Feature\Security;

use App\Exceptions\RepositoryStorageException;
use App\Models\Repository;
use App\Services\Git\GitCommandRunner;
use App\Services\Git\RepositoryStorageService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Tests\TestCase;

class OrphanRepositorySecurityTest extends TestCase
{
    use RefreshDatabase;

    private string $temporaryStorageRoot;

    protected function setUp(): void
    {
        parent::setUp();

        $this->temporaryStorageRoot = sys_get_temp_dir()
            .DIRECTORY_SEPARATOR
            .'gitforge-orphan-security-'
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

    public function test_orphan_physical_repository_is_not_automatically_adopted(): void
    {
        $orphanRepositoryId = 501;

        $repositoriesDirectory = $this->temporaryStorageRoot
            .DIRECTORY_SEPARATOR
            .'repositories';

        $orphanPath = $repositoriesDirectory
            .DIRECTORY_SEPARATOR
            .$orphanRepositoryId
            .'.git';

        File::ensureDirectoryExists(
            $repositoriesDirectory,
        );

        app(
            GitCommandRunner::class,
        )->run(
            operation: 'create-orphan-security-test-repository',
            arguments: [
                'init',
                '--bare',
                '--initial-branch=main',
                '--',
                $orphanPath,
            ],
        );

        $this->assertTrue(
            File::isDirectory(
                $orphanPath,
            ),
        );

        $this->assertDatabaseMissing(
            'repositories',
            [
                'id' => $orphanRepositoryId,
            ],
        );

        $repository = new Repository;

        $repository->forceFill([
            'id' => $orphanRepositoryId,
        ]);

        try {
            app(
                RepositoryStorageService::class,
            )->initialize(
                $repository,
            );

            $this->fail(
                'An orphan physical repository was automatically adopted.',
            );
        } catch (RepositoryStorageException $exception) {
            $this->assertSame(
                'resolve-new-target',
                $exception->operation,
            );
        }

        $this->assertDatabaseMissing(
            'repositories',
            [
                'id' => $orphanRepositoryId,
            ],
        );

        $this->assertTrue(
            File::isDirectory(
                $orphanPath,
            ),
        );
    }
}
