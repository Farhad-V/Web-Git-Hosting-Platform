# Rollback and Recovery

| Field | Value |
|---|---|
| Project | GitForge — Web Git Hosting Platform |
| Version | V1.0.0 |
| Milestone | M6 — Deployment |
| Status | Approved |

## Application Rollback

Application code may be rolled back to a previously approved release when the database schema remains compatible. Persistent PostgreSQL data and bare repositories are not replaced as part of a normal code rollback.

## Database Rollback

Database rollback is not assumed to be safe automatically. Each migration requires compatibility analysis. For destructive or incompatible changes, recovery uses an approved backup and a coordinated application version.

## Recovery Priorities

1. Preserve user Git repositories.
2. Preserve database metadata.
3. Prevent unauthorized access while degraded.
4. Restore a known compatible application release.
5. Validate consistency before resuming writes.

## Rollback Trigger Examples

- application fails liveness/readiness after deployment;
- critical authentication or authorization regression;
- Git clone/fetch/push regression;
- migration causes incompatible runtime behavior;
- deployment introduces unrecoverable frontend or API failure.
