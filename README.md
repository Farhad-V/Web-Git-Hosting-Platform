# GitForge - Web Git Hosting Platform

GitForge is a self-hosted Git hosting platform built as a full-stack software-engineering portfolio project. It combines a Laravel API, React/TypeScript frontend, PostgreSQL persistence, managed bare Git repositories, and Git Smart HTTP transport.

## Current Status

**Target release:** V1.0.0  
**Completed milestones:** M0 through M6  
**Next milestone:** M7 — V1.0.0 Release and Maintenance Readiness

Implementation, formal testing and portfolio-grade deployment validation are complete for the approved V1.0.0 scope.

## Core Capabilities

- user registration and session authentication;
- authenticated profile retrieval and logout;
- repository creation, listing, metadata retrieval, and visibility updates;
- public and private repository authorization;
- managed bare Git repository storage;
- Git clone, fetch, and push over Smart HTTP;
- personal access token creation, listing, revocation, and Git authentication;
- repository branch and commit browsing;
- repository tree navigation and text-file viewing;
- request correlation, structured operational logging, liveness, and readiness support;
- automated backend, frontend and deployment validation CI.

## V1.0.0 Scope Boundary

GitForge V1.0.0 uses an owner-centric repository permission model. It does **not** claim organization/team collaboration, SSH Git transport, distributed repository storage, or other unimplemented platform features.

## Architecture

```text
Browser / Git CLI
       |
       v
      Nginx
       |----------------------|
       |                      |
React + TypeScript SPA      PHP-FPM 8.4
                              |
                           Laravel 12
                         /     |      \
                PostgreSQL 18 Git   Managed bare repositories
```

## Technology Stack

### Backend

- PHP / Laravel 12
- PostgreSQL 18 reference profile
- System Git
- PHPUnit
- Laravel Pint

### Frontend

- React 19
- TypeScript
- Vite
- Tailwind CSS
- React Router
- TanStack Query
- Axios
- React Hook Form
- Zod
- Vitest / Testing Library
- ESLint

### Infrastructure and Delivery

- Nginx
- PHP-FPM
- GitHub Actions
- HTTPS/TLS production profile
- persistent repository storage outside public web roots
- automated deployment validation

## Repository Structure

```text
.github/workflows/       Backend, frontend and deployment validation CI
backend/                 Laravel backend and Git transport
frontend/                React/TypeScript SPA
deployment/              Production deployment profiles, scripts and guide
docs/                    Formal SDLC engineering documentation
```

## Local Development

### Backend

```bash
cd backend
composer install
cp .env.example .env
php artisan key:generate
# Configure PostgreSQL in .env
php artisan migrate
php artisan serve
```

The Composer compatibility constraint allows PHP 8.2+, while the reference CI and production deployment profile use PHP 8.4.

### Frontend

```bash
cd frontend
npm ci
npm run dev
```

Default development endpoints:

```text
Backend:  http://127.0.0.1:8000
Frontend: http://127.0.0.1:5173
```

## Quality Gates

Backend CI validates Composer configuration/install, PostgreSQL migrations, Pint, Composer audit and the full Laravel test suite.

Frontend CI validates clean dependency installation, ESLint, Vitest, production build and production dependency audit.

Deployment Validation independently validates production-mode backend installation, PostgreSQL 18 migrations, Laravel configuration/route caching, clean frontend production build, Nginx syntax and Laravel `/up` liveness.

Formal testing evidence is maintained in `docs/06-testing/`; deployment evidence is maintained in `docs/07-deployment/`.

## Deployment

The production-ready portfolio deployment package is in `deployment/` and includes:

- `env/production.env.example`;
- `nginx/gitforge.conf`;
- `php-fpm/gitforge.conf`;
- deployment preflight and HTTPS smoke-test scripts;
- PostgreSQL + Git repository backup and backup verification scripts;
- restore and rollback guidance.

The package is validated automatically on a clean GitHub runner. A paid VPS/domain is optional for V1.0.0 portfolio presentation; the same package can later be installed on a standard Linux host.

See `deployment/README.md` and `docs/07-deployment/`.

## SDLC Documentation

1. Project Definition — `docs/01-project-definition/`
2. Requirements Engineering — `docs/02-requirements-engineering/`
3. System Analysis — `docs/03-system-analysis/`
4. System Design — `docs/04-system-design/`
5. Implementation — `docs/05-implementation/`
6. Testing — `docs/06-testing/`
7. Deployment — `docs/07-deployment/`

Lifecycle summary: `docs/SDLC.md`  
Milestone tracker: `docs/MILESTONES.md`

## Security Principles

GitForge applies server-side authorization, private repository concealment, CSRF protection for state-changing session requests, throttling, non-reversible PAT storage, path traversal protection, controlled Git command arguments, bounded request/blob sizes, least-privilege deployment identity, repository storage outside public roots, and logging rules that exclude credentials and Git protocol payloads.

## Release Status

`v1.0.0` has **not** been tagged yet. The next step is M7: final release-readiness audit and portfolio release preparation. The release tag is created only after M7 confirms there are no release blockers.

## License

The backend Composer package currently declares MIT. A repository-level license file should be added only when the final public licensing decision is intentionally confirmed.
