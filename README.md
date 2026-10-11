# GitForge — Web Git Hosting Platform

GitForge is a self-hosted Git hosting platform built as a full-stack software-engineering portfolio project. It combines a Laravel API, React/TypeScript frontend, PostgreSQL persistence, managed bare Git repositories, and Git Smart HTTP transport.

## Release Status

**Stable release:** V1.0.1

**Git tag:** `v1.0.1`

**SDLC milestones:** M0 through M7 Complete

GitForge V1.0.0 is released as a GitHub portfolio/local-use project. It is designed to be cloned, inspected, run locally, tested, and evaluated, and it **does not require public hosting**.

V1.0.1 fixes repository-creation data loss, production clone URLs, and backup coordination, and updates vulnerable dependencies. See [V1.0.1 patch release notes](docs/Releases/v1/v1.0.1.md) for validation, upgrade instructions, and the required backup-procedure changes.

## Core Capabilities

- user registration and session authentication;
- authenticated profile retrieval and logout;
- repository creation, listing, metadata retrieval, renaming, and visibility updates;
- owner-centric public/private repository authorization;
- managed bare Git repository storage;
- Git clone, fetch, and push over Smart HTTP;
- personal access token creation, listing, revocation, and Git authentication;
- repository branch and commit browsing;
- repository tree navigation and text-file viewing;
- request correlation, structured operational logging, liveness, and readiness support;
- automated backend, frontend, deployment-validation, and release-readiness quality gates.

## V1.0.0 Scope Boundary

GitForge V1.0.0 uses an **owner-centric repository permission model**. It does not claim organization/team collaboration, SSH Git transport, distributed repository storage, or other unimplemented platform features.

## Architecture

```text
Browser / Git CLI
       |
       v
      Nginx (production profile)
       |----------------------|
       |                      |
React + TypeScript SPA      PHP-FPM / Laravel 12
                              |
                    |---------|---------|
                    v         v         v
              PostgreSQL 18  Git   Managed bare repositories
```

For local development, Vite runs separately and proxies `/api` to Laravel.

## Validated Toolchain

Use the validated V1.0.0 baseline for reproducible setup:

- PHP 8.4;
- Composer 2;
- PostgreSQL 18;
- Node.js 24;
- npm;
- system Git.

The backend Composer manifest retains Laravel-skeleton compatibility metadata, but the validated V1.0.0 CI/deployment/release baseline is PHP 8.4.

## Local Quick Start

### 1. PostgreSQL

Create a local database and application role matching the backend environment, for example:

```text
Database: git_hosting_platform
User:     git_hosting_app
```

Set the password you choose in `backend/.env`.

### 2. Backend

```bash
cd backend
composer install
cp .env.example .env
php artisan key:generate
# Edit DB_PASSWORD (and other DB values if needed) in .env
php artisan migrate
php artisan serve --host=127.0.0.1 --port=8000
```

If `GITFORGE_REPOSITORY_ROOT` is omitted, local managed repositories use Laravel storage under `backend/storage/app/git/repositories`.

### 3. Frontend

In a second terminal:

```bash
cd frontend
npm ci
npm run dev
```

Open `http://127.0.0.1:5173`. Vite uses this fixed port and reports an error if it is already occupied, instead of silently switching to another address.

The Vite development server proxies `/api` to `http://127.0.0.1:8000`, so the default local frontend/backend configuration works without a separate API base URL.

## Typical Product Workflow

1. Register or log in through the web UI.
2. Create a public or private repository.
3. Create a personal access token for authenticated Git operations.
4. Clone/fetch using the repository Smart HTTP URL.
5. Push as the repository owner using the PAT.
6. Browse branches, paginated commits, tags, trees, text files, and PDFs in the web UI.
7. Revoke PATs that are no longer needed.

## Quality Gates

Backend CI validates Composer configuration/install, PostgreSQL migrations, Laravel Pint, Composer security audit, and the full Laravel suite. Existing coverage includes real Git Smart HTTP read/write end-to-end flows, authorization, database integrity, security, observability, performance, and system workflows.

Frontend CI validates clean dependency installation, ESLint, Vitest, production build, and production dependency audit.

Deployment Validation validates production-mode dependency installation, PostgreSQL 18 migrations, Laravel caching, clean frontend build, Nginx syntax, and `/up` liveness.

Release Readiness reruns the release-critical backend/frontend gates together with release-documentation checks before release approval.

## Repository Structure

```text
.github/workflows/       CI, deployment, release gates, and publication automation
backend/                 Laravel API and Git transport
frontend/                React/TypeScript SPA
deployment/              Production-oriented deployment package
docs/                    Formal SDLC artifacts
CHANGELOG.md              Version history
docs/Releases/v1/v1.0.0.md          V1.0.0 release overview
```

## SDLC Documentation

Start with the [documentation index](docs/README.md) for Project Definition, Project Management, Releases, SDLC, and Sprint records.

1. Project Definition — `docs/Project-Definition/`
2. Requirements Engineering — `docs/SDLC/02-requirements-engineering/`
3. System Analysis — `docs/SDLC/03-system-analysis/`
4. System Design — `docs/SDLC/04-system-design/`
5. Implementation — `docs/SDLC/05-implementation/`
6. Testing — `docs/SDLC/06-testing/`
7. Deployment — `docs/SDLC/07-deployment/`
8. Maintenance — `docs/SDLC/08-maintenance/`

Lifecycle summary: `docs/SDLC/LIFECYCLE.md`

Milestone tracker: `docs/Project-Management/milestones.md`

## Deployment Position

A production-oriented deployment package is included to demonstrate reproducible deployment engineering. It contains Nginx/PHP-FPM profiles, production environment guidance, preflight/smoke scripts, backup verification, restore, and rollback procedures.

The V1.0.0 portfolio release does not require a VPS, domain, or public live demo.

## Maintenance and Versioning

GitForge uses Semantic Versioning. Corrective, adaptive, preventive, security, incident, dependency, and recovery maintenance procedures are defined in `docs/SDLC/08-maintenance/`.

## Security Principles

GitForge uses server-side authorization, private repository concealment, CSRF protection for state-changing session requests, throttling, non-reversible PAT storage, path validation, controlled Git arguments, bounded request/blob sizes, credential-safe logging, and storage separation from public web roots.

## Release Files

- `CHANGELOG.md`
- `docs/Releases/v1/v1.0.0.md`
- `docs/Releases/v1/History/07-release-readiness-audit.md`
- `docs/Releases/v1/History/08-v1.0.0-release-checklist.md`

## License

The backend Composer package currently declares MIT. No repository-level `LICENSE` file is claimed until the project owner intentionally confirms the final public licensing terms.

## Repository collaboration

Branches, pull requests, discussions, merge comparisons, and built-in Actions checks are available in the repository navigation. See [Repository collaboration](docs/SDLC/05-implementation/Features/repository-collaboration.md) for permissions, API contracts, migrations, and Actions worker setup.
