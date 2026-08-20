<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\StoreRepositoryRequest;
use App\Http\Requests\Api\V1\UpdateRepositoryRequest;
use App\Http\Resources\RepositoryResource;
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
     * List repositories owned by the authenticated user.
     */
    public function index(
        Request $request,
    ): AnonymousResourceCollection {
        Gate::authorize(
            'viewAny',
            Repository::class,
        );

        $repositories = Repository::query()
            ->with('owner')
            ->where(
                'owner_id',
                $request->user()->id,
            )
            ->latest()
            ->paginate(20);

        return RepositoryResource::collection(
            $repositories,
        );
    }

    /**
     * Create repository metadata and bare Git storage.
     */
    public function store(
        StoreRepositoryRequest $request,
    ): JsonResponse {
        Gate::authorize(
            'create',
            Repository::class,
        );

        $repository = null;

        DB::beginTransaction();

        try {
            $repository = $request
                ->user()
                ->repositories()
                ->create(
                    $request->safe()->only([
                        'name',
                        'visibility',
                    ]),
                );

            $this->storage->initialize(
                $repository,
            );

            DB::commit();
        } catch (Throwable $exception) {
            if (DB::transactionLevel() > 0) {
                DB::rollBack();
            }

            if ($repository instanceof Repository) {
                try {
                    $this->storage->cleanupPartial(
                        $repository,
                    );
                } catch (Throwable $cleanupException) {
                    report($cleanupException);
                }
            }

            throw $exception;
        }

        $repository->load('owner');

        return response()->json([
            'message' => 'Repository created successfully.',
            'repository' => new RepositoryResource(
                $repository,
            ),
        ], 201);
    }

    /**
     * Display repository metadata.
     */
    public function show(
        Repository $repository,
    ): RepositoryResource {
        Gate::authorize(
            'view',
            $repository,
        );

        return new RepositoryResource(
            $repository->load('owner'),
        );
    }

    /**
     * Change repository visibility.
     */
    public function update(
        UpdateRepositoryRequest $request,
        Repository $repository,
    ): JsonResponse {
        Gate::authorize(
            'update',
            $repository,
        );

        $repository->update([
            'visibility' => $request->validated()['visibility'],
        ]);

        return response()->json([
            'message' => 'Repository visibility updated successfully.',
            'repository' => new RepositoryResource(
                $repository->fresh('owner'),
            ),
        ]);
    }
}