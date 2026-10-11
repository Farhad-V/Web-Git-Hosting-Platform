<?php

namespace App\Jobs;

use App\Models\Repository;
use App\Models\RepositoryActionRun;
use App\Services\Git\GitCommandRunner;
use App\Services\Git\RepositoryStorageService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Throwable;

class RunRepositoryChecks implements ShouldQueue
{
    use Queueable;

    public int $tries = 1;

    public int $timeout = 60;

    public function __construct(public readonly int $runId)
    {
        $this->onQueue('gitforge-actions');
    }

    public function handle(GitCommandRunner $git, RepositoryStorageService $storage): void
    {
        $claimed = RepositoryActionRun::whereKey($this->runId)->where('status', 'queued')->update(['status' => 'in_progress', 'started_at' => now()]);
        if (! $claimed) {
            return;
        }
        $run = RepositoryActionRun::findOrFail($this->runId);
        $steps = [];
        $log = 'Built-in repository checks. No repository code is executed.';
        try {
            $repository = Repository::findOrFail($run->repository_id);
            $path = $storage->resolveExisting($repository);
            $checks = [
                'Commit object exists' => ['cat-file', '-e', $run->commit.'^{commit}'],
                'Commit tree is readable' => ['cat-file', '-e', $run->commit.'^{tree}'],
                'Git object connectivity' => ['fsck', '--connectivity-only', '--no-dangling'],
            ];
            foreach ($checks as $name => $arguments) {
                if ($run->fresh()->status === 'cancelled') {
                    return;
                }
                $git->run(operation: 'repository-action-check', arguments: ['--git-dir='.$path, ...$arguments]);
                $steps[] = ['name' => $name, 'status' => 'success'];
                $log .= "\nPASS: ".$name;
                RepositoryActionRun::whereKey($run->id)->where('status', 'in_progress')->update(['steps' => json_encode($steps), 'log' => $log]);
            }
            RepositoryActionRun::whereKey($run->id)->where('status', 'in_progress')->update(['status' => 'success', 'finished_at' => now()]);
        } catch (Throwable $exception) {
            $steps[] = ['name' => $name ?? 'Repository storage', 'status' => 'failure'];
            $log .= "\nFAIL: A repository check failed or timed out. Ask the repository owner to check server diagnostics.";
            RepositoryActionRun::whereKey($run->id)->where('status', 'in_progress')->update(['status' => 'failure', 'steps' => json_encode($steps), 'log' => $log, 'finished_at' => now()]);
            report($exception);
        }
    }

    public function failed(?Throwable $exception): void
    {
        RepositoryActionRun::whereKey($this->runId)->whereIn('status', ['queued', 'in_progress'])->update(['status' => 'failure', 'finished_at' => now(), 'log' => 'The check worker stopped or timed out. Run the checks again.']);
    }
}
