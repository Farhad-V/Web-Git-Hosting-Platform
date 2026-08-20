# GitForge V1.0.0 Deployment Guide

## 1. Purpose

This document defines the production-like deployment procedure for
GitForge V1.0.0.

## 2. Deployment Profile

GitForge V1.0.0 uses:

- Nginx
- PHP 8.4 with PHP-FPM
- Laravel 12
- PostgreSQL 18
- System Git
- Persistent bare Git repository storage

V1.0.0 does not require:

- Docker
- Kubernetes
- Redis
- Queue workers
- SSH Git transport
- Distributed Git storage

## 3. Production Topology

External traffic enters through Nginx over HTTPS.

Nginx serves the compiled frontend SPA from:

/var/www/gitforge/frontend/dist

Application API requests and Git Smart HTTP requests are forwarded to:

/var/www/gitforge/backend/public/index.php

Laravel communicates with PostgreSQL and launches the deployment-controlled
Git executable.

Bare Git repositories are stored persistently outside all public web roots.

## 4. Production Paths

Application root:

/var/www/gitforge

Backend:

/var/www/gitforge/backend

Frontend:

/var/www/gitforge/frontend

Repository storage root:

/var/lib/gitforge

Physical repository mapping:

/var/lib/gitforge/repositories/<repository-id>.git

Production environment file:

/var/www/gitforge/backend/.env

## 5. Service Identity

The production application runtime should execute under a dedicated
least-privilege service identity named:

gitforge

Normal Laravel and Git subprocess execution must not require root or
administrator privileges.

Nginx may use its normal web-server identity.

## 6. Runtime Requirements

The production host requires:

- Nginx
- PHP 8.4
- PHP-FPM 8.4
- Composer
- Node.js
- npm
- PostgreSQL 18
- Git

Required PHP capabilities include:

- PDO
- PDO PostgreSQL
- PostgreSQL extension
- OpenSSL
- JSON
- Mbstring
- Filesystem support
- Session support
- Process execution support

## 7. Persistent Repository Storage

Repository storage must:

- persist across application deployments;
- persist across application and web-server restarts;
- remain outside backend/public;
- remain outside frontend/dist;
- not be exposed through an Nginx static alias;
- be readable, writable, and traversable by the GitForge runtime identity;
- not be writable by unrelated users.

Configured production root:

GITFORGE_REPOSITORY_ROOT=/var/lib/gitforge

Physical repositories therefore resolve beneath:

/var/lib/gitforge/repositories

## 8. Laravel Writable Directories

The GitForge runtime identity requires controlled write access to:

- backend/storage
- backend/bootstrap/cache

The remaining application source should not be broadly writable by the
runtime or unrelated users unless explicitly required.

## 9. Production Environment

The production template is:

deployment/env/production.env.example

The deployed backend environment must provide valid production values for:

- APP_KEY
- APP_URL
- DB_HOST
- DB_PORT
- DB_DATABASE
- DB_USERNAME
- DB_PASSWORD
- GITFORGE_GIT_BINARY
- GITFORGE_REPOSITORY_ROOT

Production requirements include:

APP_ENV=production

APP_DEBUG=false

SESSION_SECURE_COOKIE=true

SESSION_HTTP_ONLY=true

Production secrets must not be committed to Git.

The production .env file must have restricted filesystem permissions.

## 10. Git Configuration

The production deployment uses a deployment-controlled Git executable.

Recommended production value:

GITFORGE_GIT_BINARY=/usr/bin/git

Application users must never select arbitrary executables or command paths.

## 11. PostgreSQL

GitForge uses the application database role:

git_hosting_app

The application role must not require:

- SUPERUSER
- CREATEDB
- CREATEROLE
- REPLICATION

PostgreSQL should be restricted to localhost or another trusted application
network and must not be exposed publicly unless explicitly required and
secured.

## 12. Frontend Build

Production deployment installs frontend dependencies and runs the approved
production build.

Expected build artifact:

/var/www/gitforge/frontend/dist

Nginx serves this directory as the SPA document root.

A failed frontend build blocks deployment success.

## 13. Backend Dependencies

Production deployment installs Composer dependencies using production
settings without development dependencies.

Dependency installation failure blocks deployment success.

## 14. Configuration Validation

Production configuration must be loaded successfully before traffic is
enabled.

Laravel configuration caching should succeed.

Missing or invalid critical configuration blocks deployment success.

## 15. Database Migrations

Only version-controlled Laravel migrations are approved sources of schema
change.

Production migrations are applied before new code depends on the new schema.

Migration failure blocks deployment success.

Untracked manual production schema mutation should be avoided.

## 16. PHP-FPM

The production PHP-FPM profile is:

deployment/php-fpm/gitforge.conf

The pool runs application requests under the dedicated GitForge service
identity and exposes a controlled local FastCGI socket to Nginx.

Invalid PHP-FPM configuration blocks deployment success.

## 17. Nginx

The production Nginx profile is:

deployment/nginx/gitforge.conf

Nginx is responsible for:

- HTTPS ingress;
- HTTP-to-HTTPS redirection;
- serving frontend/dist;
- forwarding API requests to Laravel;
- forwarding Git Smart HTTP routes to Laravel;
- forwarding the application health endpoint;
- preventing exposure of hidden or sensitive files;
- supporting Git Smart HTTP request streaming.

Invalid Nginx configuration blocks deployment success.

## 18. TLS

Externally accessible production traffic uses HTTPS.

Port 443 is the normal public application port.

Port 80 may exist only to redirect traffic to HTTPS.

PAT credentials must not be intentionally transmitted over unencrypted
production HTTP.

## 19. Liveness

The application liveness endpoint is:

GET /up

Liveness confirms that the application runtime can respond.

A production health response must not expose secrets or internal sensitive
configuration.

## 20. Readiness

Readiness verifies critical dependencies including:

- PostgreSQL connectivity;
- repository storage accessibility;
- Git executable availability;
- critical configuration.

Deployment must not be declared successful while readiness fails.

## 21. Smoke Tests

After deployment verify:

- frontend loads over HTTPS;
- SPA navigation works;
- API traffic reaches Laravel;
- registration and login work as approved;
- logout works;
- repository creation works;
- repository metadata and physical storage remain consistent;
- public repository reads work;
- private repositories remain concealed from unauthorized callers;
- PAT authentication works;
- public clone/fetch works;
- private owner clone/fetch works;
- owner push works;
- anonymous and non-owner push attempts fail;
- repository browser branches, commits, trees, and blobs work.

## 22. Logging

Application logs must be writable by the GitForge service identity.

Logs must not contain:

- passwords;
- plaintext PATs;
- PAT hashes;
- Authorization headers;
- session secrets;
- Git protocol payloads.

Production log rotation, retention, and disk-space controls must be configured.

## 23. Backup Scope

A complete GitForge backup requires both:

- PostgreSQL metadata;
- persistent Git repository storage.

Repository storage path:

/var/lib/gitforge/repositories

Neither component alone represents a complete recovery set.

Backups must be protected because they may contain private repositories,
account metadata, and PAT verification hashes.

## 24. Restore

Recovery requires:

- an approved application release;
- production configuration and secrets;
- PostgreSQL metadata;
- persistent repository storage.

After restore verify:

- database connectivity;
- repository ID to physical storage mapping;
- Git executable availability;
- authentication;
- authorization;
- public/private repository behavior;
- clone and fetch;
- push;
- repository browsing.

If metadata exists but physical repository storage is missing, GitForge must
not silently recreate it.

If repository storage exists without metadata, GitForge must not
automatically adopt it.

## 25. Rollback

Application code may be rolled back to a previously approved release.

Application rollback must not casually replace or revert:

- PostgreSQL user data;
- Git repository contents.

Database rollback requires independent migration analysis.

## 26. Deployment Failure Conditions

Deployment fails when any required condition occurs:

- invalid production configuration;
- missing APP_KEY;
- unavailable database;
- migration failure;
- unavailable repository storage;
- invalid repository permissions;
- unavailable Git executable;
- frontend build failure;
- backend dependency installation failure;
- PHP-FPM configuration failure;
- Nginx configuration failure;
- TLS failure;
- readiness failure;
- critical smoke-test failure.

## 27. Production Security Checklist

Before declaring deployment successful verify:

- APP_ENV is production.
- APP_DEBUG is false.
- HTTPS is enabled.
- HTTP redirects to HTTPS.
- A production APP_KEY exists.
- Database credentials are securely configured.
- The production .env file is access restricted.
- PostgreSQL is not unnecessarily publicly exposed.
- A dedicated GitForge runtime identity is used.
- Normal runtime execution does not require root.
- Repository storage remains outside all public web roots.
- Repository storage permissions are restricted.
- Git executable selection is deployment controlled.
- Nginx serves only the approved frontend build.
- API requests reach Laravel.
- Git Smart HTTP requests reach Laravel.
- Git protocol buffering is disabled where required.
- Secrets are excluded from logs.
- Health responses do not expose sensitive information.
- Readiness checks pass.
- Database backup procedure exists.
- Repository storage backup procedure exists.

## 28. Deployment Success Criteria

GitForge V1.0.0 deployment is successful only when:

- production configuration is valid;
- frontend build succeeds;
- backend dependencies are installed;
- database migrations succeed;
- repository storage is verified;
- Git executable is verified;
- PHP-FPM configuration is valid;
- Nginx configuration is valid;
- TLS is active;
- liveness succeeds;
- readiness succeeds;
- smoke tests pass;
- backup boundaries are confirmed.