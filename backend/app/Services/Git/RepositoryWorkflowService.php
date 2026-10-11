<?php

namespace App\Services\Git;

use App\Exceptions\GitCommandException;
use App\Models\PullRequest;
use App\Models\Repository;
use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Validation\ValidationException;

class RepositoryWorkflowService
{
    public function __construct(private readonly GitCommandRunner $git, private readonly RepositoryStorageService $storage) {}

    public function branchCommit(Repository $repository, string $name): string
    {
        $this->validateBranch($name);
        try {
            return $this->command($repository, ['rev-parse', '--verify', 'refs/heads/'.$name.'^{commit}']);
        } catch (GitCommandException) {
            throw ValidationException::withMessages(['branch' => 'This branch no longer exists. Refresh the branch list.']);
        }
    }

    public function validateBranch(string $name): void
    {
        if ($name === '' || strlen($name) > 255 || str_starts_with($name, '-') || $name === 'HEAD') {
            throw ValidationException::withMessages(['name' => 'Enter a valid Git branch name.']);
        }
        try {
            $this->git->run(operation: 'validate-workflow-branch', arguments: ['check-ref-format', 'refs/heads/'.$name]);
        } catch (GitCommandException) {
            throw ValidationException::withMessages(['name' => 'Enter a valid Git branch name.']);
        }
    }

    public function createBranch(Repository $repository, string $name, string $source): void
    {
        $this->validateBranch($name);
        $hash = $this->branchCommit($repository, $source);
        try {
            $this->command($repository, ['update-ref', 'refs/heads/'.$name, $hash, str_repeat('0', strlen($hash))]);
        } catch (GitCommandException) {
            abort(409, 'The branch already exists or changed. Refresh and try again.');
        }
    }

    public function setDefaultBranch(Repository $repository, string $name): void
    {
        $this->branchCommit($repository, $name);
        $this->command($repository, ['symbolic-ref', 'HEAD', 'refs/heads/'.$name]);
    }

    public function comparison(Repository $repository, string $baseBranch, string $headBranch): array
    {
        $base = $this->branchCommit($repository, $baseBranch);
        $head = $this->branchCommit($repository, $headBranch);
        try {
            $ancestor = $this->command($repository, ['merge-base', $base, $head]);
        } catch (GitCommandException) {
            abort(422, 'These branches have unrelated histories.');
        }
        // Diff output is spooled to disk; only a bounded preview is read into PHP memory.
        $stream = tmpfile();
        abort_if($stream === false, 503, 'Unable to prepare the comparison.');
        try {
            $this->git->runRaw(operation: 'compare-pull-request', arguments: ['--git-dir='.$this->storage->resolveExisting($repository), 'diff', '--no-ext-diff', '--no-textconv', '--no-color', '--no-renames', '--unified=3', $ancestor, $head, '--'], outputStream: $stream);
            $size = fstat($stream)['size'];
            rewind($stream);
            $diff = stream_get_contents($stream, 262144);
        } finally {
            fclose($stream);
        }
        $count = $this->command($repository, ['rev-list', '--count', $base.'..'.$head]);

        return ['base_commit' => $base, 'head_commit' => $head, 'commit_count' => (int) $count, 'diff' => mb_convert_encoding($diff, 'UTF-8', 'UTF-8'), 'diff_truncated' => $size > 262144];
    }

    public function reconcile(Repository $repository, PullRequest $pull): PullRequest
    {
        // A Git ref and a database row cannot share one transaction. Persist the
        // proposed commit first so a restart after the ref update can be repaired.
        if ($pull->status === 'merging' && $pull->merge_commit) {
            try {
                $base = $this->branchCommit($repository, $pull->base_branch);
                $this->command($repository, ['merge-base', '--is-ancestor', $pull->merge_commit, $base]);
                $pull->update(['status' => 'merged', 'merged_at' => now()]);
            } catch (GitCommandException|ValidationException) {
                // The merge has not been applied. An owner can retry it.
            }
        }

        return $pull;
    }

    public function merge(Repository $repository, PullRequest $pull, User $actor, string $expectedBase, string $expectedHead): PullRequest
    {
        return Cache::lock('repository-merge-'.$repository->id, 120)->block(5, function () use ($repository, $pull, $actor, $expectedBase, $expectedHead): PullRequest {
            $pull = $this->reconcile($repository, $pull->fresh());
            if ($pull->status === 'merged') {
                return $pull;
            }
            abort_unless(in_array($pull->status, ['open', 'merging'], true), 409, 'Only an open pull request can be merged.');
            $base = $this->branchCommit($repository, $pull->base_branch);
            $head = $this->branchCommit($repository, $pull->head_branch);
            abort_if($base !== $expectedBase || $head !== $expectedHead, 409, 'A branch changed since you reviewed it. Refresh the comparison before merging.');
            abort_if($base === $head, 422, 'There are no changes to merge.');
            abort_if((int) $this->command($repository, ['rev-list', '--count', $base.'..'.$head]) === 0, 422, 'The source commits are already in the base branch.');
            try {
                $this->command($repository, ['merge-base', $base, $head]);
            } catch (GitCommandException $exception) {
                if ($exception->processExitCode === 1) {
                    abort(422, 'These branches have unrelated histories.');
                }
                throw $exception;
            }
            if ($pull->status === 'merging' && $pull->merge_base === $base && $pull->merge_head === $head) {
                $commit = $pull->merge_commit;
            } else {
                try {
                    $tree = $this->command($repository, ['merge-tree', '--write-tree', $base, $head]);
                } catch (GitCommandException $exception) {
                    if ($exception->processExitCode === 1) {
                        abort(409, 'This pull request has merge conflicts. Resolve them in your local clone and push the branch.');
                    }
                    throw $exception;
                }
                abort_unless(preg_match('/\A[0-9a-f]{40,64}\z/', $tree), 503, 'Unable to prepare the merge.');
                $commit = $this->command($repository, ['-c', 'user.name='.$actor->username, '-c', 'user.email='.$actor->id.'@users.gitforge.local', 'commit-tree', $tree, '-p', $base, '-p', $head], 'Merge pull request #'.$pull->number.': '.$pull->title."\n");
                $claimed = PullRequest::whereKey($pull->id)->where('status', $pull->status)->update(['status' => 'merging', 'merge_commit' => $commit, 'merge_base' => $base, 'merge_head' => $head]);
                abort_unless($claimed, 409, 'The pull request changed during merging. Refresh and try again.');
                $pull->refresh();
            }
            try {
                // Lock and verify both refs, including the head, in one Git
                // transaction. Concurrent pushes must never merge unseen code.
                $this->command($repository, ['update-ref', '--stdin'], "start\nverify refs/heads/{$pull->head_branch} {$head}\nupdate refs/heads/{$pull->base_branch} {$commit} {$base}\nprepare\ncommit\n");
            } catch (GitCommandException) {
                // An error can arrive after Git committed the ref transaction.
                // Inspect the actual ref before changing the durable merge record.
                try {
                    $currentBase = $this->branchCommit($repository, $pull->base_branch);
                    $this->command($repository, ['merge-base', '--is-ancestor', $commit, $currentBase]);
                    $pull->update(['status' => 'merged', 'merged_at' => now()]);

                    return $pull->fresh();
                } catch (GitCommandException $check) {
                    if ($check->processExitCode !== 1) {
                        throw $check;
                    }
                } catch (ValidationException) {
                    abort(503, 'The merge outcome could not be verified. Refresh before retrying.');
                }
                $pull->update(['status' => 'open', 'merge_commit' => null, 'merge_base' => null, 'merge_head' => null]);
                abort(409, 'A branch changed during merging. Refresh and try again.');
            }
            $pull->update(['status' => 'merged', 'merged_at' => now()]);

            return $pull->fresh();
        });
    }

    private function command(Repository $repository, array $arguments, ?string $input = null): string
    {
        return $this->git->run(operation: 'repository-workflow', arguments: ['--git-dir='.$this->storage->resolveExisting($repository), ...$arguments], input: $input);
    }
}
