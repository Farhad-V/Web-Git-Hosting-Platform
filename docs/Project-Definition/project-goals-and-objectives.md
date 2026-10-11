# Project goals and objectives

These objectives extend the historical G-01 to G-10 register preserved in the [combined definition](README.md#project-goals). They describe measurable outcomes rather than new release approvals.

| ID | Objective | Evidence |
| --- | --- | --- |
| PD-01 | Provide secure identity, session, and Git token management | Authentication, authorization, token, and security checks |
| PD-02 | Preserve repository identity and managed data during metadata changes | Rename/storage and failure-path tests |
| PD-03 | Support real clone, fetch, and push operations | Smart HTTP tests with real Git repositories |
| PD-04 | Make repository history and content discoverable | Branch, commit, tag, tree, sidebar, text/PDF journeys |
| PD-05 | Provide controlled same-repository collaboration | Pull request permissions, comparisons, concurrency, and recovery checks |
| PD-06 | Expose built-in checks with understandable run states | Actions queue, result, cancellation, and permission checks |
| PD-07 | Maintain readable light/dark and responsive interfaces | UI regression checks and explicit browser/accessibility scenarios |
| PD-08 | Make delivery and operations reproducible | CI, migrations, health, backup/restore, and release documentation |

No numerical availability or performance SLA is asserted without an agreed target and measurements. See [success criteria](project-success-criteria.md) for acceptance evidence and [delivery criteria](../Project-Management/DELIVERY.md) for completion rules.
