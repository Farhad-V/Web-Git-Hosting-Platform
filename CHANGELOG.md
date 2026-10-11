# Changelog

All notable changes to GitForge are documented in this file.

The project follows Semantic Versioning.

## [Unreleased]

## [2.0.0] - 2026-10-11

Version metadata: `2.0.0`. See [release notes](docs/Releases/v2/v2.0.0.md) and [validation checklist](docs/Releases/v2/RELEASE-CHECKLIST.md). The annotated Git tag and a published GitHub Release are separate publication steps.

### Licensing

- Reserve rights in original project code, documentation and diagrams; require prior written permission for use and reuse under the repository-level LICENSE.
- Declare proprietary backend metadata and UNLICENSED frontend metadata. Third-party components retain their licenses; previous grants are not retroactively revoked.
- Plan a major release boundary for this licensing policy change; this is not a claim that every API has become incompatible.

### Added

- Repository rename, branch creation/default selection, tag and commit navigation, expandable file sidebar, and bounded PDF preview/download.
- Same-repository pull requests with discussions, comparison, close/reopen, owner merge, stale-ref/conflict checks and prepared-merge recovery.
- Queue-backed built-in Actions checks, run history, controlled results and cancellation.
- Organized project, management, SDLC, sprint and release documentation with editable PlantUML sources and checked-in SVG previews.

### Changed

- Shared glass UI surfaces, semantic colors, persistent light/dark modes and responsive floating navigation.
- Fixed local frontend port 5173 and documented dedicated Windows Actions worker startup.
- Repaired documentation encoding, Markdown tables and diagram typography; expanded deployment and testing references with explicit historical evidence boundaries.

### Upgrade considerations

- Review new permission requirements before using the v2 original materials.
- Apply additive repository-workflow migrations after verifying a coordinated database/Git backup.
- Operate and restart the `gitforge-actions` worker for Actions. Git 2.38 or newer is needed for the modern merge-tree operation.
- Update client remotes after repository rename; old-name redirects are not provided. Actions does not execute arbitrary project commands or workflow YAML.

### Validation status

All five automated workflows passed on preparation commit `deb3447`: Backend CI, Frontend CI, Deployment Validation, Release Readiness and Documentation Diagrams. The stable-metadata commit must pass its triggered checks before tagging. Manual acceptance and isolated recovery rehearsal not recorded as completed remain explicit in the checklist. A tag does not certify a live deployment or every manual acceptance item.

## [1.0.1] - 2026-10-10

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
