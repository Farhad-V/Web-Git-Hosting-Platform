<?php

namespace Database\Factories;

use App\Enums\RepositoryVisibility;
use App\Models\Repository;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Repository>
 */
class RepositoryFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'owner_id' => User::factory(),
            'name' => sprintf(
                'repository-%s',
                fake()->unique()->numerify('########'),
            ),
            'visibility' => RepositoryVisibility::PrivateAccess,
        ];
    }

    /**
     * Mark the repository as publicly visible.
     */
    public function publicRepository(): static
    {
        return $this->state(
            fn (): array => [
                'visibility' => RepositoryVisibility::PublicAccess,
            ],
        );
    }

    /**
     * Mark the repository as private.
     */
    public function privateRepository(): static
    {
        return $this->state(
            fn (): array => [
                'visibility' => RepositoryVisibility::PrivateAccess,
            ],
        );
    }
}