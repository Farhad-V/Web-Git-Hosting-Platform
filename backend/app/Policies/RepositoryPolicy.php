<?php

namespace App\Policies;

use App\Enums\RepositoryVisibility;
use App\Models\Repository;
use App\Models\User;
use Illuminate\Auth\Access\Response;
use Illuminate\Support\Facades\Log;

class RepositoryPolicy
{
    /**
     * Determine whether the authenticated user may list repositories.
     */
    public function viewAny(
        User $user,
    ): bool {
        return $this->decision(
            operation: 'view_any',
            allowed: true,
            user: $user,
        );
    }

    /**
     * Determine whether the repository may be read.
     *
     * Unauthorized reads of private repositories are externally concealed
     * as not-found responses so callers cannot distinguish a private
     * repository from a repository that does not exist.
     */
    public function view(
        ?User $user,
        Repository $repository,
    ): Response {
        $allowed = (
            $repository->visibility
            === RepositoryVisibility::PublicAccess
        )
            || (
                $user !== null
                && $repository->owner_id === $user->id
            );

        $this->decision(
            operation: 'view',
            allowed: $allowed,
            user: $user,
            repository: $repository,
        );

        return $allowed
            ? Response::allow()
            : Response::denyAsNotFound();
    }

    /**
     * Determine whether the user may create repositories.
     */
    public function create(
        User $user,
    ): bool {
        return $this->decision(
            operation: 'create',
            allowed: true,
            user: $user,
        );
    }

    /**
     * Determine whether repository settings may be changed.
     */
    public function update(
        User $user,
        Repository $repository,
    ): bool {
        return $this->decision(
            operation: 'update',
            allowed: $repository->owner_id === $user->id,
            user: $user,
            repository: $repository,
        );
    }

    /**
     * Determine whether the repository may receive Git writes.
     */
    public function write(
        User $user,
        Repository $repository,
    ): bool {
        return $this->decision(
            operation: 'write',
            allowed: $repository->owner_id === $user->id,
            user: $user,
            repository: $repository,
        );
    }

    /**
     * Emit a structured authorization decision and return the decision.
     */
    private function decision(
        string $operation,
        bool $allowed,
        ?User $user = null,
        ?Repository $repository = null,
    ): bool {
        Log::info(
            'authorization_decision',
            [
                'event' => 'authorization_decision',
                'request_id' => $this->requestId(),
                'user_id' => $user?->id,
                'actor_state' => $user === null
                    ? 'anonymous'
                    : 'authenticated',
                'repository_id' => $repository?->id,
                'operation' => $operation,
                'decision' => $allowed
                    ? 'allow'
                    : 'deny',
                'result' => $allowed
                    ? 'allowed'
                    : 'denied',
                'failure_category' => $allowed
                    ? null
                    : 'authorization_denied',
            ],
        );

        return $allowed;
    }

    /**
     * Return the current request correlation identifier when available.
     */
    private function requestId(): ?string
    {
        if (! app()->bound('request')) {
            return null;
        }

        $requestId = request()->attributes->get(
            'request_id',
        );

        return is_string($requestId)
            && $requestId !== ''
                ? $requestId
                : null;
    }
}
