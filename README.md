# GitForge - Web Git Hosting Platform

GitForge is a self-hosted Git hosting platform built as a full-stack software-engineering project. It combines a Laravel API, React/TypeScript frontend, PostgreSQL persistence, managed bare Git repositories, and Git Smart HTTP transport.

The project is developed against a formal SDLC with requirements, system analysis, system design, implementation traceability, testing, deployment planning, and milestone reviews stored in the repository.

## Current Status

**Target release:** V1.0.0  
**Completed milestones:** M0 through M5  
**Active milestone:** M6 - Deployment

The application implementation and formal testing phases are complete for the approved V1.0.0 scope. Production-like deployment verification is the current lifecycle stage.

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
- request correlation, structured operational logging, liveness, and readiness checks;
- automated backend and frontend CI;
- production-oriented Nginx, PHP-FPM, environment, backup, restore, and rollback guidance.

## V1.0.0 Scope Boundary

GitForge V1.0.0 uses an owner-centric repository permission model. The repository does **not** claim organization/team collaboration, SSH Git transport, distributed repository storage, or other unimplemented platform features.

## Architecture

```text
Browser
  |
  v
React + TypeScript SPA
  |
  | /api/v1
  v
Laravel 12 API -------------------- PostgreSQL 18
  |
  | controlled Git subprocesses
  v
System Git
  |
  v
Managed bare repositories

Git CLI
  |
  | Git Smart HTTP
  v
Laravel Git transport endpoints
```

Production ingress is designed around Nginx and PHP-FPM, with the compiled frontend served as a static SPA and API/Git Smart HTTP traffic forwarded to Laravel.

## Technology Stack

### Backend

- PHP / Laravel 12
- PostgreSQL
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
- HTTPS/TLS deployment profile
- persistent repository storage outside public web roots

## Repository Structure

```text
.github/
  workflows/          GitHub Actions CI
backend/              Laravel backend and Git transport
frontend/             React/TypeScript SPA
deployment/           Production deployment profiles and guide
docs/                 SDLC engineering documentation
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

## Testing and Quality Gates

Backend CI performs:

```text
Composer validation
Composer install
Laravel environment preparation
PostgreSQL migrations
Laravel Pint validation
Composer security audit
Full Laravel test suite
```

Frontend CI performs:

```text
npm ci
ESLint
Vitest
TypeScript/Vite production build
production dependency audit
```

The latest audited backend CI run passed **205 tests with 1008 assertions**. The formal testing specification contains **141 test cases**, with traceability across the approved V1.0.0 requirement set.

## Documentation

The repository contains formal engineering artifacts for:

1. Project Definition - `docs/01-project-definition/`
2. Requirements Engineering - `docs/02-requirements-engineering/`
3. System Analysis - `docs/03-system-analysis/`
4. System Design - `docs/04-system-design/`
5. Implementation - `docs/05-implementation/`
6. Testing - `docs/06-testing/`
7. Deployment - `deployment/`

Lifecycle summary: `docs/SDLC.md`  
Milestone tracker: `docs/MILESTONES.md`

## Production Deployment

Production guidance is maintained in `deployment/README.md` and includes:

- Nginx HTTPS ingress;
- PHP-FPM least-privilege runtime identity;
- PostgreSQL configuration boundaries;
- persistent Git repository storage;
- secure production environment defaults;
- liveness and readiness verification;
- smoke tests;
- backup and restore boundaries;
- rollback guidance.

Deployment is not considered complete until the M6 production-like verification criteria are satisfied.

## Security Principles

GitForge applies defense-in-depth controls including:

- server-side authorization;
- private repository concealment;
- CSRF protection for state-changing session requests;
- throttling;
- non-reversible PAT storage;
- path traversal protection;
- controlled Git command arguments;
- bounded Git request and browser blob sizes;
- least-privilege deployment identity;
- repository storage outside public web roots;
- logs designed to exclude passwords, PATs, authorization headers, and Git protocol payloads.

## License

The backend Composer package declares the project under the MIT license. A repository-level license file should be added only when the project owner intentionally confirms the final public licensing terms.
