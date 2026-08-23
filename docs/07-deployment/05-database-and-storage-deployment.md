# Database and Repository Storage Deployment

| Field | Value |
|---|---|
| Project | GitForge — Web Git Hosting Platform |
| Version | V1.0.0 |
| Milestone | M6 — Deployment |
| Status | Approved |

## PostgreSQL

The production application role is `git_hosting_app`. It requires normal application DDL/DML permissions for version-controlled Laravel migrations but does not require SUPERUSER, CREATEDB, CREATEROLE or REPLICATION.

Migrations are executed from the approved release with:

```bash
php artisan migrate --force
```

Manual, untracked production schema changes are not part of the approved deployment process.

## Git Repository Storage

The canonical root is `/var/lib/gitforge`; managed bare repositories live below `/var/lib/gitforge/repositories`.

Repository storage persists independently from `/var/www/gitforge`. Deploying or rolling back application code must never delete or replace repository contents.

## Consistency Rule

Database metadata and physical Git storage form a logical pair. Missing storage must not be silently recreated for existing metadata, and orphaned storage must not be automatically adopted without an explicit recovery procedure.
