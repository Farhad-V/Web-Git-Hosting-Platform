# Repository workflows after v1.0.1

This document describes the current implementation of repository navigation and collaboration. Earlier v1.0.0 milestone documents and v1.0.1 release notes describe their historical scope; their deferred-feature statements do not describe the features added here.

## Branches

The Branches tab lists Git-native branches, their latest commits, a search field, and the default branch. Repository owners can create a branch from an existing branch and change the default branch. View code opens the selected branch in the repository browser. New branches preserve their exact Git names, including slash-separated names. Creation uses compare-and-swap ref updates and refuses to replace an existing branch. Git pushes continue to work normally. Branch deletion and protection rules are not included in this version.

## Pull requests

Pull requests propose changes between two branches in the same repository. Forks and cross-repository requests are not included. Signed-in users who may read a repository can propose a request and comment; private repositories remain owner-only. Public visitors may read discussions and comparisons. Authors and repository owners can close or reopen requests; only owners can merge.

Each repository has its own request numbering. Requests include a title, description, branch pair, status, paginated discussion, and a three-dot Git comparison based on the branches' merge base. Comparison previews are limited to 256 KiB and safely replace invalid UTF-8. The response includes exact base and head commit hashes. The owner confirms these reviewed hashes when merging. If either branch changed, merging returns HTTP 409 and requires a new review.

Merging uses Git merge-tree and commit-tree to prepare a merge commit with both parents. Conflicts are rejected and must be resolved in a local clone. An atomic Git ref transaction verifies the source ref and updates the base ref only if both still match the reviewed hashes. A concurrent close prevents merge preparation from being accepted. A durable `merging` record stores the proposed commit before changing Git refs. If a process stops after Git updates the base, later reads reconcile the record when that merge commit is reachable. An unapplied prepared merge can be retried by its owner. No force push or rollback of user refs is performed.

## Actions

Actions provides manually triggered built-in repository checks, as selected for this first version:

1. The selected commit object exists.
2. Its tree can be read.
3. Git object connectivity is valid.

The commit hash is pinned when the run is queued. Repository code, project build scripts, dependencies, and YAML workflows are not executed. Runs retain their status, step results, timestamps, and a controlled log. Owners can start, cancel, and run checks again. Reading uses repository visibility permissions; private run data is concealed from unauthorized users. One queued or running run per repository is allowed. Cancellation is preserved if a worker finishes a step concurrently, though it does not terminate an already-running Git check. Git subprocess time limits still apply.

Jobs run on the `gitforge-actions` Laravel queue. Queued runs update automatically in the UI. A worker must be running:

```sh
cd backend
php artisan migrate --force
php artisan queue:work --queue=gitforge-actions --tries=1 --timeout=60 --sleep=1
```

On Windows, `backend/scripts/start-actions-worker.ps1 -PhpBinary <path-to-php.exe>` starts a hidden worker and records its process ID. Repeating the script reuses an identified existing worker. Its logs are in `backend/storage/logs/actions-worker*.log`. Start the worker again after reboot. Restart workers through the normal Laravel queue restart procedure after deployments so they load updated code. In production, supervise the queue worker as a service. The queue retry delay must exceed the 60-second worker timeout. A stopped worker leaves pending runs queued; failed or timed-out jobs are marked failed when the queue processes them.

## API

All endpoints use the existing versioned API, session authentication, CSRF protection for mutations, and throttling. Repository IDs remain stable after renaming.

| Endpoint under `/api/v1/repositories/{repository}` | Behavior |
| --- | --- |
| GET `/branches` | List branches and default branch |
| POST `/branches` | Create `{name, source}` as owner |
| PATCH `/default-branch` | Select `{name}` as owner |
| GET, POST `/pulls` | List or open a request |
| GET, PATCH `/pulls/{pull}` | Read, close, or reopen a request |
| POST `/pulls/{pull}/merge` | Owner merge with `{expected_base, expected_head}` |
| GET, POST `/pulls/{pull}/comments` | Read or contribute discussion |
| GET, POST `/actions` | List or queue checks on `{branch}` |
| GET `/actions/{run}` | Read run details and logs |
| POST `/actions/{run}/cancel` | Owner cancellation |

Pull and Actions lists use 20 items per page; comments use 30. Pages range from 1 to 1000. Pull request filters support `open`, `closed`, `merged`, and `all`. Nested request/run IDs are scoped to their repository before any data is returned. Backend validation restricts sizes and branch/ref syntax. Git commands use structured arguments rather than shell interpolation.

## Deployment and recovery

The additive migration creates `pull_requests`, `pull_request_comments`, and `repository_action_runs`. Back up the database and Git storage together as usual. Applying it does not rewrite existing repository objects or user files. Rolling it back drops workflow history and should only be done when that data is no longer needed. Run migrations before exposing the new tabs, and supervise the Actions worker. Git 2.38 or later is required for merge-tree's modern mode. This first version does not include reviews with approvals, branch protection, checks required for merging, fork collaboration, workflow YAML, or arbitrary project CI runners.
