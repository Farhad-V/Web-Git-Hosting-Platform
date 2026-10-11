# Functional requirements

| ID | Required behavior | Context |
| --- | --- | --- |
| RE-F01 | Register, authenticate, retrieve identity, and end a session | Released baseline |
| RE-F02 | Create/list owned repositories and manage visibility | Released baseline |
| RE-F03 | Issue, list, and revoke personal access tokens; show a new secret once | Released baseline |
| RE-F04 | Clone/fetch/push through real Git Smart HTTP with repository policies | Released baseline |
| RE-F05 | Browse Git branches, commits, directories, and supported text content | Released baseline |
| RE-F06 | Rename without changing repository identity or physical storage | Merged development; client URLs need updating |
| RE-F07 | Browse tags, commit detail, expandable files, and supported PDFs | Merged development; previews are bounded |
| RE-F08 | Create a branch from an existing branch and select the default | Merged development; owner-only mutations |
| RE-F09 | Open/read/discuss/close/reopen same-repository pull requests according to policy | Merged development |
| RE-F10 | Owner merge reviewed branch hashes; reject conflicts or stale refs and reconcile prepared merges | Merged development |
| RE-F11 | Owner queue/cancel built-in checks and read permitted run history/results | Merged development; dedicated worker required |
| RE-F12 | Persist light/dark preference and expose readable responsive navigation | Merged development |

Private repositories remain owner-only. Permitted signed-in readers may propose or comment on public repository requests; visitors may read public content. Authors/owners can close or reopen requests; only owners merge. Actions executes commit, tree, and connectivity checks rather than arbitrary scripts.

Deletion/archival, organizations, forks, protected branches, review approvals, SSH, and arbitrary runners are not asserted as implemented. See [acceptance criteria](acceptance-criteria.md), [feature contracts](../05-implementation/Features/repository-collaboration.md), and [historical requirements](04-functional-requirements.md).
