# Backup and Restore

| Field | Value |
|---|---|
| Project | GitForge — Web Git Hosting Platform |
| Version | V1.0.0 |
| Milestone | M6 — Deployment |
| Status | Approved |

## Backup Scope

A complete backup contains both PostgreSQL and `/var/lib/gitforge/repositories`.

`deployment/scripts/backup.sh` creates a timestamped backup containing:

- PostgreSQL custom-format dump;
- compressed repository-storage archive;
- manifest;
- SHA-256 checksums.

`deployment/scripts/verify-backup.sh` verifies file presence, checksums and archive readability.

## Backup Consistency

Before every backup, block new writes with file-based Laravel maintenance mode (`php artisan down` in the deployed backend). Wait for in-flight API requests and Git pushes to finish, pause scheduled jobs and any other direct repository writers, and keep writes paused until the backup completes. Maintenance mode alone does not drain requests that already started.

Set `GITFORGE_BACKEND_ROOT` to the deployed backend directory and set `GITFORGE_BACKUP_WRITES_PAUSED=1` only after confirming that all writers have stopped. The backup script refuses to run without both this confirmation and the maintenance marker, and rechecks the marker between the database dump and repository archive. The confirmation is an operator assertion, not an automatic lock on external writers.

After a successful backup and verification, restore paused jobs and run `php artisan up` if you enabled maintenance mode for this backup. On failure, keep writes paused while investigating. An unattended backup must implement the same pause-and-drain coordination.

## Restore Procedure

1. Stop or place the application in maintenance mode to prevent writes.
2. Verify the selected backup before restoration.
3. Restore PostgreSQL to the intended database using `pg_restore`.
4. Restore the repository archive to the configured repository root with correct ownership and permissions.
5. Deploy the application release compatible with the restored schema.
6. Clear/rebuild Laravel caches as required.
7. Run liveness, authentication, authorization, Git clone/fetch/push and repository-browser verification.
8. Resume traffic only after consistency checks pass.

## Security

Backups contain private source code and sensitive account metadata. They require access control, encryption at rest appropriate to the target environment and an explicit retention policy.
