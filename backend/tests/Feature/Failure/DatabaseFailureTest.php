<?php

namespace Tests\Feature\Failure;

use App\Enums\RepositoryVisibility;
use App\Models\Repository;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class DatabaseFailureTest extends TestCase
{
    use RefreshDatabase;

    public function test_database_failure_does_not_report_successful_repository_mutation(): void
    {
        $owner = User::factory()->create();

        $repository = Repository::factory()
            ->privateRepository()
            ->create([
                'owner_id' => $owner->id,
                'name' => 'database-failure-test',
            ]);

        $this->installVisibilityUpdateFailureTrigger();

        DB::statement('SAVEPOINT simulated_database_failure');

        $response = $this
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
            );

        DB::statement('ROLLBACK TO SAVEPOINT simulated_database_failure');
        DB::statement('RELEASE SAVEPOINT simulated_database_failure');

        $response->assertServerError();

        $this->assertStringNotContainsString(
            'Repository visibility updated successfully.',
            $response->getContent(),
        );

        $repository->refresh();

        $this->assertSame(
            RepositoryVisibility::PrivateAccess,
            $repository->visibility,
        );

        $this->assertDatabaseHas(
            'repositories',
            [
                'id' => $repository->id,
                'owner_id' => $owner->id,
                'name' => 'database-failure-test',
                'visibility' => RepositoryVisibility::PrivateAccess->value,
            ],
        );
    }

    private function installVisibilityUpdateFailureTrigger(): void
    {
        if (DB::getDriverName() === 'pgsql') {
            DB::unprepared(
                <<<'SQL'
                CREATE OR REPLACE FUNCTION fail_repository_visibility_update()
                RETURNS trigger AS $$
                BEGIN
                    RAISE EXCEPTION 'simulated repository database failure';
                END;
                $$ LANGUAGE plpgsql;

                CREATE TRIGGER fail_repository_visibility_update
                BEFORE UPDATE ON repositories
                FOR EACH ROW
                EXECUTE FUNCTION fail_repository_visibility_update();
                SQL,
            );

            return;
        }

        DB::statement(
            <<<'SQL'
            CREATE TRIGGER fail_repository_visibility_update
            BEFORE UPDATE ON repositories
            BEGIN
                SELECT RAISE(ABORT, 'simulated repository database failure');
            END
            SQL,
        );
    }
}
