<?php

namespace App\Http\Controllers\Api\V1;

use App\Exceptions\GitCommandException;
use App\Exceptions\RepositoryStorageException;
use App\Http\Controllers\Controller;
use App\Jobs\RunRepositoryChecks;
use App\Models\PullRequest;
use App\Models\PullRequestComment;
use App\Models\Repository;
use App\Models\RepositoryActionRun;
use App\Services\Git\RepositoryWorkflowService;
use Illuminate\Contracts\Cache\LockTimeoutException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\HttpException;

class RepositoryCollaborationController extends Controller
{
    public function __construct(private readonly RepositoryWorkflowService $workflow) {}

    public function createBranch(Request $request, Repository $repository): JsonResponse
    {
        Gate::authorize('write', $repository);
        $input = $request->validate(['name' => ['required', 'string', 'max:255'], 'source' => ['required', 'string', 'max:255']]);

        return $this->gitResponse(function () use ($repository, $input): JsonResponse {
            $this->workflow->createBranch($repository, $input['name'], $input['source']);

            return response()->json(['message' => 'Branch created.'], 201);
        });
    }

    public function defaultBranch(Request $request, Repository $repository): JsonResponse
    {
        Gate::authorize('write', $repository);
        $input = $request->validate(['name' => ['required', 'string', 'max:255']]);

        return $this->gitResponse(function () use ($repository, $input): JsonResponse {
            $this->workflow->setDefaultBranch($repository, $input['name']);

            return response()->json(['message' => 'Default branch updated.']);
        });
    }

    public function pulls(Request $request, Repository $repository): JsonResponse
    {
        Gate::authorize('view', $repository);
        $input = $request->validate(['status' => ['sometimes', Rule::in(['open', 'closed', 'merged', 'all'])], 'page' => ['sometimes', 'integer', 'min:1', 'max:1000']]);
        $query = PullRequest::where('repository_id', $repository->id)->with('author:id,username,name');
        if (($input['status'] ?? 'open') !== 'all') {
            $statuses = ($input['status'] ?? 'open') === 'open' ? ['open', 'merging'] : [$input['status']];
            $query->whereIn('status', $statuses);
        }

        return response()->json($query->orderByDesc('number')->simplePaginate(20));
    }

    public function createPull(Request $request, Repository $repository): JsonResponse
    {
        Gate::authorize('view', $repository);
        $input = $request->validate(['title' => ['required', 'string', 'max:200'], 'description' => ['nullable', 'string', 'max:20000'], 'base_branch' => ['required', 'string', 'max:255'], 'head_branch' => ['required', 'string', 'max:255', 'different:base_branch']]);

        return $this->gitResponse(function () use ($request, $repository, $input): JsonResponse {
            $comparison = $this->workflow->comparison($repository, $input['base_branch'], $input['head_branch']);
            abort_unless($comparison['commit_count'] > 0, 422, 'The source branch has no new commits to propose.');
            $pull = DB::transaction(function () use ($request, $repository, $input): PullRequest {
                Repository::whereKey($repository->id)->lockForUpdate()->firstOrFail();
                abort_if(PullRequest::where('repository_id', $repository->id)->whereIn('status', ['open', 'merging'])->where('base_branch', $input['base_branch'])->where('head_branch', $input['head_branch'])->exists(), 409, 'An open pull request already exists for these branches.');
                $number = 1 + (int) PullRequest::where('repository_id', $repository->id)->max('number');

                return PullRequest::create([...$input, 'repository_id' => $repository->id, 'author_id' => $request->user()->id, 'number' => $number]);
            });

            return response()->json(['data' => $pull->load('author:id,username,name')], 201);
        });
    }

    public function pull(Request $request, Repository $repository, int $pull): JsonResponse
    {
        Gate::authorize('view', $repository);

        return $this->gitResponse(function () use ($repository, $pull): JsonResponse {
            $record = $this->findPull($repository, $pull);
            $record = $this->workflow->reconcile($repository, $record);
            $comparison = null;
            $error = null;
            if (in_array($record->status, ['open', 'merging'], true)) {
                try {
                    $comparison = $this->workflow->comparison($repository, $record->base_branch, $record->head_branch);
                } catch (ValidationException|HttpException $exception) {
                    $error = 'Branches cannot be compared. They may have been deleted or have unrelated histories.';
                }
            }

            return response()->json(['data' => $record->load('author:id,username,name'), 'comparison' => $comparison, 'comparison_error' => $error, 'can_manage' => auth()->id() === $repository->owner_id || auth()->id() === $record->author_id, 'can_merge' => auth()->id() === $repository->owner_id]);
        });
    }

    public function updatePull(Request $request, Repository $repository, int $pull): JsonResponse
    {
        Gate::authorize('view', $repository);
        $record = $this->findPull($repository, $pull);
        abort_unless($request->user()->id === $repository->owner_id || $request->user()->id === $record->author_id, 403);
        $input = $request->validate(['status' => ['required', Rule::in(['open', 'closed'])]]);
        $updated = DB::transaction(function () use ($repository, $record, $input): int {
            Repository::whereKey($repository->id)->lockForUpdate()->firstOrFail();
            if ($input['status'] === 'open') {
                abort_if(PullRequest::where('repository_id', $repository->id)->where('id', '!=', $record->id)->whereIn('status', ['open', 'merging'])->where('base_branch', $record->base_branch)->where('head_branch', $record->head_branch)->exists(), 409, 'Another open pull request already exists for these branches.');
            }

            return PullRequest::whereKey($record->id)->whereIn('status', ['open', 'closed'])->update(['status' => $input['status']]);
        });
        abort_unless($updated, 409, 'A merged or merging pull request cannot be reopened or closed.');

        return response()->json(['data' => $record->fresh()]);
    }

    public function mergePull(Request $request, Repository $repository, int $pull): JsonResponse
    {
        Gate::authorize('write', $repository);
        $input = $request->validate(['expected_base' => ['required', 'regex:/\A[0-9a-f]{40,64}\z/'], 'expected_head' => ['required', 'regex:/\A[0-9a-f]{40,64}\z/']]);

        return $this->gitResponse(fn (): JsonResponse => response()->json(['data' => $this->workflow->merge($repository, $this->findPull($repository, $pull), $request->user(), $input['expected_base'], $input['expected_head'])]));
    }

    public function comments(Request $request, Repository $repository, int $pull): JsonResponse
    {
        Gate::authorize('view', $repository);
        $record = $this->findPull($repository, $pull);
        $request->validate(['page' => ['sometimes', 'integer', 'min:1', 'max:1000']]);

        return response()->json(PullRequestComment::where('pull_request_id', $record->id)->with('author:id,username,name')->orderBy('id')->simplePaginate(30));
    }

    public function comment(Request $request, Repository $repository, int $pull): JsonResponse
    {
        Gate::authorize('view', $repository);
        $record = $this->findPull($repository, $pull);
        $input = $request->validate(['body' => ['required', 'string', 'max:10000']]);
        $comment = PullRequestComment::create(['pull_request_id' => $record->id, 'author_id' => $request->user()->id, 'body' => $input['body']]);

        return response()->json(['data' => $comment->load('author:id,username,name')], 201);
    }

    public function actions(Request $request, Repository $repository): JsonResponse
    {
        Gate::authorize('view', $repository);
        $request->validate(['page' => ['sometimes', 'integer', 'min:1', 'max:1000']]);

        return response()->json(RepositoryActionRun::where('repository_id', $repository->id)->orderByDesc('id')->simplePaginate(20));
    }

    public function action(Repository $repository, int $run): JsonResponse
    {
        Gate::authorize('view', $repository);

        return response()->json(['data' => RepositoryActionRun::where('repository_id', $repository->id)->findOrFail($run)]);
    }

    public function runAction(Request $request, Repository $repository): JsonResponse
    {
        Gate::authorize('write', $repository);
        $input = $request->validate(['branch' => ['required', 'string', 'max:255']]);

        return $this->gitResponse(function () use ($request, $repository, $input): JsonResponse {
            $hash = $this->workflow->branchCommit($repository, $input['branch']);
            $run = DB::transaction(function () use ($request, $repository, $input, $hash): RepositoryActionRun {
                Repository::whereKey($repository->id)->lockForUpdate()->firstOrFail();
                abort_if(RepositoryActionRun::where('repository_id', $repository->id)->whereIn('status', ['queued', 'in_progress'])->exists(), 409, 'A check is already queued or running for this repository.');

                return RepositoryActionRun::create(['repository_id' => $repository->id, 'actor_id' => $request->user()->id, 'branch' => $input['branch'], 'commit' => $hash, 'steps' => [], 'log' => 'Queued for the repository check worker.']);
            });
            RunRepositoryChecks::dispatch($run->id)->afterCommit();

            return response()->json(['data' => $run->fresh()], 202);
        });
    }

    public function cancelAction(Request $request, Repository $repository, int $run): JsonResponse
    {
        Gate::authorize('write', $repository);
        $record = RepositoryActionRun::where('repository_id', $repository->id)->findOrFail($run);
        $updated = RepositoryActionRun::whereKey($record->id)->whereIn('status', ['queued', 'in_progress'])->update(['status' => 'cancelled', 'finished_at' => now(), 'log' => 'Cancelled by the repository owner.']);
        abort_unless($updated, 409, 'This run has already finished.');

        return response()->json(['data' => $record->fresh()]);
    }

    private function findPull(Repository $repository, int $id): PullRequest
    {
        return PullRequest::where('repository_id', $repository->id)->findOrFail($id);
    }

    private function gitResponse(callable $callback): JsonResponse
    {
        try {
            return $callback();
        } catch (GitCommandException|RepositoryStorageException|LockTimeoutException $exception) {
            report($exception);
            abort(503, 'Repository storage is temporarily unavailable. Try again later.');
        }
    }
}
