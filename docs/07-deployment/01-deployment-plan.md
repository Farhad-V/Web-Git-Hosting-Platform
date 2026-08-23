# Deployment Plan

| Field | Value |
|---|---|
| Project | GitForge — Web Git Hosting Platform |
| Version | V1.0.0 |
| SDLC phase | 7 — Deployment |
| Milestone | M6 — Deployment |
| Status | Approved |
| Date | 2026-08-23 |

## Purpose

M6 converts the tested V1.0.0 implementation into a reproducible, production-oriented deployment package suitable for a portfolio release and for later installation on a Linux host.

## Deployment Objectives

1. Define a deterministic Linux production topology.
2. Keep secrets outside version control.
3. Provide production Nginx, PHP-FPM, Laravel and PostgreSQL configuration boundaries.
4. Make frontend and backend release preparation reproducible.
5. Validate migrations, build output, runtime liveness and Nginx syntax in CI.
6. Define backup, restore, rollback and operational verification procedures.
7. Preserve persistent Git repositories independently from application releases.

## Scope

M6 validates the deployment package in GitHub Actions using a production-like environment. A paid VPS, public domain and public TLS certificate are intentionally not required for the portfolio V1.0.0 milestone. The package is designed so those external resources can be attached later without redesigning the application.

## Exit Criteria

All repository-side M6 exit criteria passed in Deployment Validation run `32659338381` and Backend CI run `32659338516`:

- deployment configuration is version controlled;
- production secrets are not committed;
- deployment scripts pass shell syntax validation;
- frontend production build succeeds;
- backend production dependency installation succeeds;
- PostgreSQL migrations succeed against PostgreSQL 18;
- Laravel production configuration and route caching succeed;
- `/up` responds in a production-like runtime;
- Nginx configuration passes `nginx -t` with temporary test TLS material;
- backup and recovery procedures are documented;
- M6 milestone review is approved.
