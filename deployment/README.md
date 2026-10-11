# GitForge V1.0.0 Deployment

This directory contains the production-oriented deployment package for GitForge V1.0.0.

## Deployment Profile

Reference production stack:

- Linux host;
- Nginx;
- PHP 8.4 with PHP-FPM;
- Laravel 12;
- PostgreSQL 18;
- Node.js/npm for frontend build;
- System Git;
- persistent bare Git repository storage.

V1.0.0 does not require Docker, Kubernetes, Redis, SSH Git transport or distributed Git storage.

## Topology

```text
HTTPS
  |
Nginx
  |-------------------------|
  |                         |
frontend/dist               PHP-FPM socket
                            |
                         Laravel
                       /    |     \
              PostgreSQL   Git    /var/lib/gitforge/repositories
```

## Canonical Paths

```text
Application root:          /var/www/gitforge
Backend:                   /var/www/gitforge/backend
Frontend:                  /var/www/gitforge/frontend
Repository root:           /var/lib/gitforge
Managed repositories:      /var/lib/gitforge/repositories
Production backend env:    /var/www/gitforge/backend/.env
```

## Configuration Files

- `env/production.env.example` — production-safe environment template;
- `nginx/gitforge.conf` — HTTPS, SPA, API and Git Smart HTTP ingress;
- `php-fpm/gitforge.conf` — dedicated least-privilege PHP-FPM pool;
- `scripts/preflight.sh` — host/tool/template checks;
- `scripts/smoke-test.sh` — basic HTTPS and `/up` verification;
- `scripts/backup.sh` — PostgreSQL + repository-storage backup;
- `scripts/verify-backup.sh` — checksum/archive verification.

## Production Security and Recovery Invariants

The following statements are V1.0.0 deployment invariants and are intentionally explicit because automated security tests enforce them:

- APP_DEBUG is false.
- Repository storage remains outside all public web roots.
- Normal runtime execution does not require root.
- A complete recovery set contains PostgreSQL metadata.
- A complete recovery set also contains persistent Git repository storage.
- If PostgreSQL metadata exists but the corresponding physical repository storage is missing, GitForge must not silently recreate it.
- If physical repository storage exists without corresponding PostgreSQL metadata, GitForge must not automatically adopt it.

## Production Rules

- `APP_ENV=production`;
- `APP_DEBUG=false`;
- use a unique production `APP_KEY`;
- never commit the real production `.env`;
- use HTTPS for external traffic;
- keep PostgreSQL private by default;
- run Laravel/Git processes under a dedicated `gitforge` identity;
- keep managed repositories outside all public web roots;
- preserve PostgreSQL and repository storage across releases;
- never log passwords, plaintext PATs, Authorization headers or Git protocol payloads.

## Release Procedure

1. Provision the Linux runtime and dedicated `gitforge` service identity.
2. Check out the approved release under `/var/www/gitforge`.
3. Run `deployment/scripts/preflight.sh`.
4. Create `backend/.env` from the production template and inject real secrets.
5. Run backend production dependency installation:

```bash
cd backend
composer install --no-dev --no-interaction --prefer-dist --optimize-autoloader
php artisan key:generate --force   # first deployment only
php artisan migrate --force
php artisan config:cache
php artisan route:cache
```

6. Build the frontend:

```bash
cd frontend
npm ci
npm run build
```

7. Install the PHP-FPM pool and Nginx configuration, replacing the example hostname/certificate paths for the real host.
8. Validate configurations before service reload.
9. Reload PHP-FPM and Nginx.
10. Run liveness and smoke verification.

## Automated Deployment Validation

`.github/workflows/deployment-validation.yml` reproduces the critical deployment path on a clean GitHub-hosted runner. It validates:

- production template security values;
- shell-script syntax;
- production Composer install without dev dependencies;
- PostgreSQL 18 migrations;
- Laravel config and route caching;
- clean frontend install and production build;
- Nginx syntax with temporary test TLS material;
- Laravel `/up` liveness.

M6 approval evidence:

- Deployment Validation run `32659338381` — PASS;
- Backend CI run `32659338516` — PASS.

## Smoke Test on a Real Host

```bash
BASE_URL=https://gitforge.example.com deployment/scripts/smoke-test.sh
```

After the basic smoke test, verify the approved acceptance workflows: registration/login/logout, repository creation and visibility, PAT management, public/private authorization, clone/fetch/push, and repository browsing.

## Backup

A complete GitForge backup contains both PostgreSQL metadata and persistent Git repository storage.

Before every backup, block new writes with file-based Laravel maintenance mode (`php artisan down` in the deployed backend). Wait for in-flight API requests and Git pushes to finish, pause scheduled jobs and any other direct repository writers, and keep writes paused until the backup completes. Maintenance mode alone does not drain requests that already started.

Set `GITFORGE_BACKEND_ROOT` to the deployed backend directory and set `GITFORGE_BACKUP_WRITES_PAUSED=1` only after confirming that all writers have stopped. The backup script refuses to run without both this confirmation and the maintenance marker, and rechecks the marker between the database dump and repository archive. The confirmation is an operator assertion, not an automatic lock on external writers.

After a successful backup and verification, restore paused jobs and run `php artisan up` if you enabled maintenance mode for this backup. On failure, keep writes paused while investigating. An unattended backup must implement the same pause-and-drain coordination.


Example:

```bash
export BACKUP_DIR=/srv/backups/gitforge
export DB_HOST=127.0.0.1
export DB_PORT=5432
export DB_DATABASE=git_hosting_platform
export DB_USERNAME=git_hosting_app
export PGPASSWORD='set-at-runtime'
export GITFORGE_REPOSITORY_ROOT=/var/lib/gitforge
export GITFORGE_BACKEND_ROOT=/var/www/gitforge/backend
export GITFORGE_BACKUP_WRITES_PAUSED=1  # only after pausing and draining all writers

deployment/scripts/backup.sh
```

Verify a produced backup with:

```bash
deployment/scripts/verify-backup.sh /srv/backups/gitforge/gitforge-<timestamp>
```

Backups contain sensitive data and require appropriate access controls and encryption at rest.

## Restore and Rollback

Restore is a coordinated operation: verify the backup, restore PostgreSQL with `pg_restore`, restore the repository archive with correct ownership, deploy a schema-compatible application version, rebuild caches, and run acceptance checks before resuming writes.

If PostgreSQL metadata exists but physical repository storage is missing, GitForge must not silently recreate it. If repository storage exists without PostgreSQL metadata, GitForge must not automatically adopt it.

Normal application rollback changes application code only. It must not casually replace PostgreSQL data or Git repository contents. Database rollback requires migration-specific compatibility analysis.

## Portfolio Deployment Scope

GitForge V1.0.0 is validated as a reproducible production-ready repository package. A paid VPS, public DNS name and public certificate are optional demonstration infrastructure; they are not required for M6 completion. The same package is ready to be attached to a real Linux host later.

Formal M6 evidence is maintained in `docs/SDLC/07-deployment/`.
