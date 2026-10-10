# GitForge Backend

Laravel API and Git transport backend for GitForge, a self-hosted Web Git Hosting Platform.

## Stack

- PHP 8.2+ (reference CI/deployment baseline: PHP 8.4)
- Laravel 12
- PostgreSQL 18
- System Git
- PHPUnit
- Laravel Pint

## Responsibilities

The backend provides:

- user registration, login, authenticated identity, and logout;
- repository metadata and ownership management;
- public/private repository authorization;
- managed bare Git repository storage;
- Git Smart HTTP clone, fetch, and push transport;
- personal access token management and Git authentication;
- repository branch, commit, tree, and text-blob browsing;
- liveness, readiness, request correlation, and operational logging.

The separate React/TypeScript application is located in `../frontend`.

## Requirements

- PHP 8.2 or later; PHP 8.4 is the reference environment used by CI and deployment documentation
- Composer 2
- PostgreSQL 18
- Git

## Local Setup

From the `backend` directory:

```bash
composer install
cp .env.example .env
php artisan key:generate
```

Configure PostgreSQL in `.env`, then run:

```bash
php artisan migrate
php artisan serve
```

Default local application URL:

```text
http://127.0.0.1:8000
```

The repository-storage root is controlled by `GITFORGE_REPOSITORY_ROOT`. Keep managed repositories outside public web roots in production.

## Tests

Run the complete backend suite:

```bash
php artisan test
```

Check formatting:

```bash
vendor/bin/pint --test
```

Audit PHP dependencies:

```bash
composer audit --locked
```

GitHub Actions runs migrations, formatting validation, dependency audit, and the full backend suite against PostgreSQL 18 using PHP 8.4.

## API

Application APIs are versioned under:

```text
/api/v1
```

Primary API areas include authentication, repositories, repository browsing, personal access tokens, and readiness.

## Git Smart HTTP

Git transport is exposed through repository-scoped Smart HTTP endpoints supporting the approved V1.0.0 clone, fetch, and push workflows.

Git subprocesses are executed through controlled application services. User-controlled shell command construction is not permitted.

## Security Model

Key controls include:

- server-side authorization policies;
- private repository concealment;
- controlled repository path resolution;
- non-reversible personal access token storage;
- CSRF protection for session-authenticated state changes;
- request throttling;
- bounded Git request and browser blob sizes;
- structured logs that exclude credentials and Git protocol payloads;
- deployment-controlled Git executable selection.

## Production

Production deployment guidance and infrastructure profiles are maintained in:

```text
../deployment/
```

The production deployment uses Nginx, PHP-FPM 8.4, PostgreSQL 18, HTTPS, and persistent managed repository storage.

## Documentation

The complete software-engineering lifecycle is documented in `../docs/`, including requirements, analysis, design, implementation traceability, testing, and milestone state.

## PDF repository previews

The repository browser displays PDF files through a dedicated, policy-controlled `GET /api/v1/repositories/{repository}/pdf?branch=main&path=document.pdf` endpoint. Public repositories allow anonymous reading; private repositories allow only their owner. Git paths are validated and the extracted file must have a PDF signature. Output goes directly to a temporary file, then is served inline with private/no-store caching and removed after sending.

`GIT_BROWSER_MAX_PDF_BYTES` controls the PDF size limit (100 MiB by default). The separate 1 MiB text preview limit is unchanged. Extraction needs sufficient temporary disk space. Embedded previews use the browser PDF viewer and include a download option for browsers without inline PDF support.

## Repository settings, tags, and history

Owners may rename a repository or change its visibility through PATCH /api/v1/repositories/{repository}. Either name or visibility is required. Names follow the same normalization, path safety, and namespace uniqueness rules as creation. Repository storage remains identified by its numeric ID, preserving Git objects and refs after a rename. Existing clone URLs must be updated; old names do not redirect.

The web repository page provides Code, Commits, Tags, and owner-only Settings navigation. GET /api/v1/repositories/{repository}/tags lists annotated and lightweight tags in descending Git version order, including their target commits. Commit and tag lists accept page (1–1000) and limit (1–100) and return has_more. Private repositories retain owner-only reading; public repositories support anonymous reading. Tags are created and pushed using the Git CLI.
