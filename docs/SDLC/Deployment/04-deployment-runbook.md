# Deployment Runbook

| Field | Value |
|---|---|
| Project | GitForge — Web Git Hosting Platform |
| Version | V1.0.0 |
| Milestone | M6 — Deployment |
| Status | Approved |

## Host Preparation

Use a supported Linux host with Nginx, PHP 8.4/PHP-FPM, Composer, Node.js/npm, PostgreSQL 18 and Git. Create a dedicated `gitforge` service account and persistent repository root `/var/lib/gitforge/repositories`.

## Release Procedure

1. Check out the approved V1.0.0 release under `/var/www/gitforge`.
2. Run `deployment/scripts/preflight.sh`.
3. Copy `deployment/env/production.env.example` to `backend/.env` and replace placeholders with host-specific values and secrets.
4. Generate the Laravel application key if this is the first deployment.
5. Install backend dependencies with `composer install --no-dev --prefer-dist --optimize-autoloader`.
6. Install frontend dependencies with `npm ci` and build with `npm run build`.
7. Create and permission `/var/lib/gitforge/repositories` for the `gitforge` runtime identity.
8. Run `php artisan migrate --force`.
9. Run `php artisan config:cache` and `php artisan route:cache`.
10. Install the PHP-FPM pool and Nginx site configuration.
11. Validate PHP-FPM and Nginx configuration before reload.
12. Reload PHP-FPM and Nginx.
13. Run liveness and smoke tests.
14. Record the deployed Git commit or release tag.

## Failure Rule

Do not declare deployment successful after a failed build, dependency install, migration, configuration validation, liveness check or critical smoke test.
