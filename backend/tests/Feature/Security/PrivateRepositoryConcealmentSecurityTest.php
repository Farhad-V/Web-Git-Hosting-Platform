<?php

namespace Tests\Feature\Security;

use App\Models\Repository;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PrivateRepositoryConcealmentSecurityTest extends TestCase
{
    use RefreshDatabase;

    public function test_non_owner_receives_not_found_for_private_repository(): void
    {
        $owner = User::factory()->create();

        $viewer = User::factory()->create();

        $repository = Repository::factory()
            ->privateRepository()
            ->create([
                'owner_id' => $owner->id,
            ]);

        $this
            ->actingAs(
                $viewer,
                'web',
            )
            ->getJson(
                route(
                    'api.v1.repositories.show',
                    $repository,
                ),
            )
            ->assertNotFound();
    }

    public function test_anonymous_visitor_receives_not_found_for_private_repository(): void
    {
        $repository = Repository::factory()
            ->privateRepository()
            ->create();

        $this
            ->getJson(
                route(
                    'api.v1.repositories.show',
                    $repository,
                ),
            )
            ->assertNotFound();
    }

    public function test_missing_repository_has_same_external_status_as_concealed_private_repository(): void
    {
        $this
            ->getJson(
                route(
                    'api.v1.repositories.show',
                    [
                        'repository' => 999999999,
                    ],
                ),
            )
            ->assertNotFound();
    }
}