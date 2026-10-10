# Changelog

All notable changes to GitForge are documented in this file.

The project follows Semantic Versioning.

## [Unreleased]

### Fixed

- Preserve pre-existing Git storage when repository creation fails.
- Use the public site origin for production clone URLs while retaining the local development address.
- Require maintenance mode and explicit pause-and-drain confirmation for coordinated backups.
- Restore the missing Git Smart HTTP write analysis document.

### Security

- Update affected Laravel, CommonMark, Flysystem, and Axios dependencies to patched compatible versions.

## [1.0.0] - 2026-08-23

### Added

- Laravel 12 API backend with PostgreSQL persistence;
- React 19 / TypeScript web interface;
- registration, login, identity retrieval, and logout;
- repository creation, listing, metadata retrieval, and visibility updates;
- owner-centric public/private authorization;
- managed bare Git repository storage;
- Git Smart HTTP clone, fetch, and push;
- personal access token creation, listing, revocation, and Git authentication;
- repository branch, commit, tree, and text-blob browsing;
- liveness, readiness, request correlation, and structured operational logging;
- formal requirements, analysis, design, implementation, testing, deployment, and maintenance documentation;
- automated backend, frontend, deployment-validation, and release-readiness quality gates;
- production-oriented Nginx/PHP-FPM/PostgreSQL deployment package;
- backup verification, restore, rollback, security, incident, and maintenance procedures.

### Security

- CSRF protection for state-changing session requests;
- server-side repository authorization and private-repository concealment;
- non-reversible PAT storage and credential-safe logging;
- controlled Git subprocess execution and path validation;
- bounded Git request and repository-browser blob sizes;
- least-privilege production runtime guidance.

### Scope

V1.0.0 intentionally does not include organization/team collaboration, SSH Git transport, distributed repository storage, or other unimplemented platform features. Public hosting is not required for this portfolio release.
