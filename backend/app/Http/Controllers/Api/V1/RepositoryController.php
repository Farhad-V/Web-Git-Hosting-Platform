<?php

namespace App\Http\Controllers\Api\V1;

use App\Enums\RepositoryStatus;
use App\Enums\RepositoryVisibility;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\StoreRepositoryRequest;
use App\Http\Requests\Api\V1\UpdateRepositoryRequest;
use App\Http\Resources\RepositoryResource;
use App\Models\Repository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Gate;

class RepositoryController extends Controller
{
    /**
     * List repositories visible to the authenticated user.
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        Gate::authorize('viewAny', Repository::class);

        $repositories = Repository::query()
            ->with('owner')
            ->where(function ($query) use ($request): void {
                $query
                    ->where(
                        'visibility',
                        RepositoryVisibility::PublicAccess,
                    )
                    ->orWhere('owner_id', $request->user()->id);
            })
            ->latest()
            ->paginate(20);

        return RepositoryResource::collection($repositories);
    }

    /**
     * Create repository metadata for the authenticated owner.
     */
    public function store(
        StoreRepositoryRequest $request,
    ): JsonResponse {
        Gate::authorize('create', Repository::class);

        $repository = $request
            ->user()
            ->repositories()
            ->create([
                ...$request->safe()->only([
                    'name',
                    'slug',
                    'description',
                    'visibility',
                ]),
                'status' => RepositoryStatus::Active,
                'default_branch' => null,
            ]);

        $repository->load('owner');

        return response()->json([
            'message' => 'Repository created successfully.',
            'repository' => new RepositoryResource($repository),
        ], 201);
    }

    /**
     * Display a repository.
     */
    public function show(Repository $repository): RepositoryResource
    {
        Gate::authorize('view', $repository);

        return new RepositoryResource(
            $repository->load('owner'),
        );
    }

    /**
     * Update repository metadata.
     */
    public function update(
        UpdateRepositoryRequest $request,
        Repository $repository,
    ): JsonResponse {
        Gate::authorize('update', $repository);

        $repository->update(
            $request->safe()->only([
                'name',
                'slug',
                'description',
                'visibility',
            ]),
        );

        return response()->json([
            'message' => 'Repository updated successfully.',
            'repository' => new RepositoryResource(
                $repository->fresh('owner'),
            ),
        ]);
    }

    /**
     * Archive a repository and make it read-only.
     */
    public function archive(Repository $repository): JsonResponse
    {
        Gate::authorize('archive', $repository);

        $repository->update([
            'status' => RepositoryStatus::Archived,
            'archived_at' => now(),
        ]);

        return response()->json([
            'message' => 'Repository archived successfully.',
            'repository' => new RepositoryResource(
                $repository->fresh('owner'),
            ),
        ]);
    }

    /**
     * Return an archived repository to active status.
     */
    public function unarchive(Repository $repository): JsonResponse
    {
        Gate::authorize('unarchive', $repository);

        $repository->update([
            'status' => RepositoryStatus::Active,
            'archived_at' => null,
        ]);

        return response()->json([
            'message' => 'Repository unarchived successfully.',
            'repository' => new RepositoryResource(
                $repository->fresh('owner'),
            ),
        ]);
    }

    /**
     * Mark a repository for coordinated metadata and storage deletion.
     */
    public function destroy(Repository $repository): JsonResponse
    {
        Gate::authorize('delete', $repository);

        $repository->update([
            'status' => RepositoryStatus::DeletionPending,
            'deletion_requested_at' => now(),
            'deletion_failure_reason' => null,
        ]);

        return response()->json([
            'message' => 'Repository deletion scheduled.',
        ], 202);
    }
}
