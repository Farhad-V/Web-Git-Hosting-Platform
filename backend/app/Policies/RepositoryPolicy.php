<?php

namespace App\Policies;

use App\Enums\RepositoryVisibility;
use App\Models\Repository;
use App\Models\User;

class RepositoryPolicy
{
    /**
     * Determine whether the authenticated user may list repositories.
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the repository may be read.
     */
    public function view(
        ?User $user,
        Repository $repository,
    ): bool {
        if (
            $repository->visibility
            === RepositoryVisibility::PublicAccess
        ) {
            return true;
        }

        return $user !== null
            && $repository->owner_id === $user->id;
    }

    /**
     * Determine whether the user may create repositories.
     */
    public function create(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether repository settings may be changed.
     */
    public function update(
        User $user,
        Repository $repository,
    ): bool {
        return $repository->owner_id === $user->id;
    }

    /**
     * Determine whether the repository may receive Git writes.
     */
    public function write(
        User $user,
        Repository $repository,
    ): bool {
        return $repository->owner_id === $user->id;
    }
}