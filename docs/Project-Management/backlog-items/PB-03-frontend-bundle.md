# PB-03: reduce the main frontend bundle

Status: improvement opportunity. Responsible role: frontend maintainer. Schedule: not assigned.

Recent production builds reported a main bundle above the 500 kB warning threshold. This is a build warning, not a confirmed runtime failure.

Acceptance criteria:

- Measure current main and lazy chunks on the exact baseline commit.
- Agree a bundle budget and identify actual heavy dependencies or route-loading opportunities.
- Reduce initial-load code while preserving routing, authentication, themes, and repository workflows.
- Pass frontend lint, tests, type checking, and build; record before/after sizes and affected browser journeys.

Do not declare improvement by raising the warning threshold alone. See [visual contracts](../../SDLC/Implementation/Features/visual-system.md).
