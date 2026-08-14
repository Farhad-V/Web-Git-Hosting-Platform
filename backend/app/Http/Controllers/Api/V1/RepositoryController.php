<?php

namespace App\Http\Controllers\Api\V1;

use App\Enums\RepositoryStatus;
use App\Enums\RepositoryVisibility;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\StoreRepositoryRequest;
use App\Http\Requests\Api\V1\UpdateRepositoryRequest;
use App\Http\Resources\RepositoryResource;
use App\Jobs\DeleteRepositoryStorage;
use App\Models\Repository;
use App\Services\Git\RepositoryStorageService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Throwable;

class RepositoryController extends Controller
{
    public function __construct(
        private readonly RepositoryStorageService $storage,
    ) {}

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
     * Create metadata and managed bare Git storage atomically.
     */
    public function store(
        StoreRepositoryRequest $request,
    ): JsonResponse {
        Gate::authorize('create', Repository::class);

        $repository = null;

        DB::beginTransaction();

        try {
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

            $this->storage->initialize($repository);

            DB::commit();
        } catch (Throwable $exception) {
            if (DB::transactionLevel() > 0) {
                DB::rollBack();
            }

            if (
                $repository instanceof Repository
                && $this->storage->exists($repository)
            ) {
                try {
                    $this->storage->delete($repository);
                } catch (Throwable $cleanupException) {
                    report($cleanupException);
                }
            }

            throw $exception;
        }

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
     * Schedule coordinated storage and metadata deletion.
     */
    public function destroy(Repository $repository): JsonResponse
    {
        Gate::authorize('delete', $repository);

        DB::transaction(
            static function () use ($repository): void {
                $repository->update([
                    'status' => RepositoryStatus::DeletionPending,
                    'deletion_requested_at' => now(),
                    'deletion_failure_reason' => null,
                ]);

                DeleteRepositoryStorage::dispatch(
                    $repository->id,
                )->afterCommit();
            },
        );

        return response()->json([
            'message' => 'Repository deletion scheduled.',
        ], 202);
    }
}
