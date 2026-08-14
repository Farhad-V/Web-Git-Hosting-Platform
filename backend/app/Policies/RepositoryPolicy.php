<?php

namespace App\Policies;

use App\Enums\RepositoryStatus;
use App\Enums\RepositoryVisibility;
use App\Models\Repository;
use App\Models\User;

class RepositoryPolicy
{
    /**
     * Determine whether repositories may be listed.
     */
    public function viewAny(?User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the repository may be viewed.
     */
    public function view(?User $user, Repository $repository): bool
    {
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
     * Determine whether the user may update the repository.
     */
    public function update(User $user, Repository $repository): bool
    {
        return $repository->owner_id === $user->id
            && $repository->status === RepositoryStatus::Active;
    }

    /**
     * Determine whether the user may archive the repository.
     */
    public function archive(User $user, Repository $repository): bool
    {
        return $repository->owner_id === $user->id
            && $repository->status === RepositoryStatus::Active;
    }

    /**
     * Determine whether the user may restore an archived repository.
     */
    public function unarchive(User $user, Repository $repository): bool
    {
        return $repository->owner_id === $user->id
            && $repository->status === RepositoryStatus::Archived;
    }

    /**
     * Determine whether the user may request repository deletion.
     */
    public function delete(User $user, Repository $repository): bool
    {
        return $repository->owner_id === $user->id
            && in_array(
                $repository->status,
                [
                    RepositoryStatus::Active,
                    RepositoryStatus::Archived,
                    RepositoryStatus::DeletionFailed,
                ],
                true,
            );
    }

    /**
     * Restoring soft-deleted repositories is not exposed in V1.
     */
    public function restore(User $user, Repository $repository): bool
    {
        return false;
    }

    /**
     * Force deletion is handled only by the internal storage workflow.
     */
    public function forceDelete(User $user, Repository $repository): bool
    {
        return false;
    }
}
