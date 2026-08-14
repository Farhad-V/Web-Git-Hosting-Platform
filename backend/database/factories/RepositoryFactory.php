<?php

namespace Database\Factories;

use App\Enums\RepositoryStatus;
use App\Enums\RepositoryVisibility;
use App\Models\Repository;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

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
        $name = fake()->unique()->words(3, true);

        return [
            'owner_id' => User::factory(),
            'name' => Str::title($name),
            'slug' => Str::slug($name),
            'description' => fake()->optional()->sentence(),
            'visibility' => RepositoryVisibility::PrivateAccess,
            'status' => RepositoryStatus::Active,
            'default_branch' => null,
            'archived_at' => null,
            'deletion_requested_at' => null,
            'deletion_failure_reason' => null,
        ];
    }

    /**
     * Mark the repository as publicly visible.
     */
    public function publicRepository(): static
    {
        return $this->state(fn (): array => [
            'visibility' => RepositoryVisibility::PublicAccess,
        ]);
    }

    /**
     * Mark the repository as private.
     */
    public function privateRepository(): static
    {
        return $this->state(fn (): array => [
            'visibility' => RepositoryVisibility::PrivateAccess,
        ]);
    }

    /**
     * Mark the repository as archived and read-only.
     */
    public function archived(): static
    {
        return $this->state(fn (): array => [
            'status' => RepositoryStatus::Archived,
            'archived_at' => now(),
        ]);
    }
}
