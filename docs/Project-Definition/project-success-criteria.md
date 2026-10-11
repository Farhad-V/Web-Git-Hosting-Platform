# Project success criteria

Success is assessed against the exact candidate commit. Passing a recorded checkpoint does not automatically validate later changes.

| Criterion | Acceptance evidence |
| --- | --- |
| Authorized users can register, sign in, and manage their repositories | Authentication/API journeys and invalid-input checks |
| Private data remains concealed and mutations enforce permissions | Anonymous, non-owner, owner, CSRF, and token tests |
| Git clients can clone/fetch/push committed data without corruption | Real Git transport tests and appropriate large-stream checks |
| Browsing reflects Git-native history and refs | Branch, tag, commit, tree, and blob checks |
| Pull requests reject stale or conflicting merges and retain recoverable state | Comparison hashes, atomic refs, concurrent-close, and recovery tests |
| Actions checks complete under a running worker and preserve cancellation | Queue/run tests and operational worker verification |
| UI is understandable in both themes and narrow layouts | Lint/tests/build plus keyboard, layout, and visual scenarios |
| Deployment and recovery procedures are reproducible | Migrations, readiness, deployment validation, backup/restore evidence |
| Documentation navigation and release paths remain usable | Local link checks, exact-case path checks, and release gate |

## Completion gate

Apply the [definition of done](../Project-Management/DELIVERY.md) and [release checklist](../Releases/v1/RELEASE-CHECKLIST.md). Record skipped, blocked, or unverified scenarios explicitly. Historical verification totals are available in [delivery records](../Sprints/README.md); current CI results must be checked separately.
