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
use Illuminate\Support\Facades\Log;
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

        Log::info(
            'repository_create_started',
            [
                'event' => 'repository_create_started',
                'request_id' => $request->attributes->get('request_id'),
                'user_id' => $request->user()->id,
                'repository_name' => $request->validated()['name'],
                'visibility' => $request->validated()['visibility'],
                'result' => 'started',
            ],
        );

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

            Log::info(
                'repository_metadata_created',
                [
                    'event' => 'repository_metadata_created',
                    'request_id' => $request->attributes->get('request_id'),
                    'user_id' => $request->user()->id,
                    'repository_id' => $repository->id,
                    'result' => 'success',
                ],
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
                Log::warning(
                    'repository_creation_compensation_started',
                    [
                        'event' => 'repository_creation_compensation_started',
                        'request_id' => $request->attributes->get('request_id'),
                        'user_id' => $request->user()->id,
                        'repository_id' => $repository->id,
                        'result' => 'started',
                    ],
                );

                try {
                    $this->storage->cleanupPartial(
                        $repository,
                    );

                    Log::info(
                        'repository_creation_compensation_succeeded',
                        [
                            'event' => 'repository_creation_compensation_succeeded',
                            'request_id' => $request->attributes->get('request_id'),
                            'user_id' => $request->user()->id,
                            'repository_id' => $repository->id,
                            'result' => 'success',
                        ],
                    );
                } catch (Throwable $cleanupException) {
                    Log::error(
                        'repository_creation_compensation_failed',
                        [
                            'event' => 'repository_creation_compensation_failed',
                            'request_id' => $request->attributes->get('request_id'),
                            'user_id' => $request->user()->id,
                            'repository_id' => $repository->id,
                            'result' => 'failure',
                            'failure_category' => 'storage_failure',
                            'exception_class' => $cleanupException::class,
                        ],
                    );

                    report($cleanupException);
                }
            }

            Log::error(
                'repository_create_failed',
                [
                    'event' => 'repository_create_failed',
                    'request_id' => $request->attributes->get('request_id'),
                    'user_id' => $request->user()->id,
                    'repository_id' => $repository?->id,
                    'result' => 'failure',
                    'failure_category' => 'repository_consistency_failure',
                    'exception_class' => $exception::class,
                ],
            );

            throw $exception;
        }

        $repository->load('owner');

        Log::info(
            'repository_create_succeeded',
            [
                'event' => 'repository_create_succeeded',
                'request_id' => $request->attributes->get('request_id'),
                'user_id' => $request->user()->id,
                'repository_id' => $repository->id,
                'result' => 'success',
            ],
        );

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

        $oldVisibility = $repository->visibility->value;
        $newVisibility = $request->validated()['visibility'];

        $repository->update([
            'visibility' => $newVisibility,
        ]);

        Log::info(
            'repository_visibility_changed',
            [
                'event' => 'repository_visibility_changed',
                'request_id' => $request->attributes->get('request_id'),
                'repository_id' => $repository->id,
                'user_id' => $request->user()->id,
                'old_visibility' => $oldVisibility,
                'new_visibility' => $newVisibility,
                'result' => 'success',
            ],
        );

        return response()->json([
            'message' => 'Repository visibility updated successfully.',
            'repository' => new RepositoryResource(
                $repository->fresh('owner'),
            ),
        ]);
    }
}