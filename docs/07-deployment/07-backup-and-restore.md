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
