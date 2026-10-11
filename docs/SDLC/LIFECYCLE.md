# Software Development Life Cycle

## GitForge — Web Git Hosting Platform

**Stable release:** V1.0.0  
**Development approach:** Eight-phase SDLC  
**Last reconciled:** 2026-08-23

## Lifecycle

| Phase | Milestone | Status | Evidence |
|---|---|---|---|
| 1. Project Definition | M0 | Complete | `docs/Project-Definition/` |
| 2. Requirements Engineering | M1 | Complete | `docs/SDLC/02-requirements-engineering/` |
| 3. System Analysis | M2 | Complete | `docs/SDLC/03-system-analysis/` |
| 4. System Design | M3 | Complete | `docs/SDLC/04-system-design/` |
| 5. Implementation | M4 | Complete | `docs/SDLC/05-implementation/` + source |
| 6. Testing | M5 | Complete | `docs/SDLC/06-testing/` + automated suites |
| 7. Deployment | M6 | Complete | `docs/SDLC/07-deployment/` + Deployment Validation |
| 8. Maintenance | M7 | Complete | `docs/SDLC/08-maintenance/` + Release Readiness + V1.0.0 publication |

## Phase 8 — Maintenance and Release Readiness

### Objective

Establish a stable V1.0.0 baseline that is locally usable, tested, documented, maintainable, versioned, and portfolio-ready without requiring a public live deployment.

### Completed Work Products

Phase 8 defines corrective/adaptive/preventive maintenance, semantic versioning, dependency updates, security maintenance, incident and defect handling, backup/recovery maintenance, final release audit, completed release checklist, changelog, release notes, and an integrated release-readiness CI gate.

### Validation

The final M7 release candidate passed Backend CI and the integrated Release Readiness workflow, including PostgreSQL migrations, the full backend suite, Composer audit, clean frontend install, lint, tests, production build, npm production audit, and release-documentation checks.

### Publication

GitForge V1.0.0 was published under tag `v1.0.0`. The tag points to commit `c941302d32c5e1f494703437824efbd6feeaacca` and the release uses the approved `docs/Releases/v1/v1.0.0.md`.

### Hosting Boundary

GitForge V1.0.0 is a GitHub portfolio/local-use release. Public hosting, a VPS, public DNS, and a live domain are not acceptance criteria. The deployment package remains part of the engineering evidence.

## Scope Integrity

The implementation and approved evidence take precedence over stale planning statements. V1.0.0 uses an owner-centric repository permission model and does not claim organization/team collaboration, SSH Git transport, distributed repository storage, or other unimplemented capabilities.

## Final Position

**All eight SDLC phases (M0–M7) are Complete for GitForge V1.0.0.**

Future changes are maintenance work or belong to a separately approved later release. The `v1.0.0` tag remains immutable as the published baseline.
