# Observability and Health Verification

| Field | Value |
|---|---|
| Project | GitForge — Web Git Hosting Platform |
| Version | V1.0.0 |
| Milestone | M6 — Deployment |
| Status | In Validation |

## Liveness

`GET /up` is the deployment liveness endpoint. It confirms that the Laravel runtime can answer a request through the configured application path.

## Readiness

Operational readiness additionally requires:

- PostgreSQL connectivity;
- writable repository storage;
- configured Git executable availability;
- valid production configuration;
- successful migrations.

## Logs

Laravel and Nginx logs must not contain passwords, plaintext PATs, PAT hashes, Authorization headers, session secrets or raw Git protocol payloads.

## Deployment Evidence

The GitHub Actions `Deployment Validation` workflow provides reproducible evidence for the portfolio release that production dependency installation, migration, build, Nginx syntax and application liveness are valid from a clean runner.
