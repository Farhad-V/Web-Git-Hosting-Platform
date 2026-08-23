# M6 Milestone Review — Deployment

| Field | Value |
|---|---|
| Project | GitForge — Web Git Hosting Platform |
| Version | V1.0.0 |
| Milestone | M6 — Deployment |
| Status | Pending CI Validation |
| Review date | 2026-08-23 |

## Deliverables

- production deployment architecture;
- environment/configuration specification;
- deployment runbook;
- PostgreSQL and persistent Git storage procedure;
- Nginx/TLS/PHP-FPM runtime specification;
- backup and restore procedure;
- rollback and recovery procedure;
- observability and health procedure;
- deployment security checklist;
- executable preflight, smoke-test and backup-verification scripts;
- automated Deployment Validation GitHub Actions workflow.

## Approval Gates

M6 may be marked Complete only after:

- Backend CI passes on the M6 branch/PR;
- Frontend CI passes on the M6 branch/PR;
- Deployment Validation passes;
- no deployment secret is committed;
- the final diff contains no unintended product behavior changes.

## Current Decision

**PENDING** — awaiting automated CI evidence.
