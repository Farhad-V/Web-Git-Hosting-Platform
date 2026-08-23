# Software Development Life Cycle

## GitForge — Web Git Hosting Platform

**Target release:** V1.0.0  
**Development approach:** Eight-phase SDLC  
**Last reconciled:** 2026-08-23

## Lifecycle

| Phase | Milestone | Status | Evidence |
|---|---|---|---|
| 1. Project Definition | M0 | Complete | `docs/01-project-definition/` |
| 2. Requirements Engineering | M1 | Complete | `docs/02-requirements-engineering/` |
| 3. System Analysis | M2 | Complete | `docs/03-system-analysis/` |
| 4. System Design | M3 | Complete | `docs/04-system-design/` |
| 5. Implementation | M4 | Complete | `docs/05-implementation/` + source |
| 6. Testing | M5 | Complete | `docs/06-testing/` + automated suites |
| 7. Deployment | M6 | Complete | `docs/07-deployment/` + deployment validation |
| 8. Maintenance | M7 | In Validation | `docs/08-maintenance/` + release-readiness gate |

## Phase 8 — Maintenance and Release Readiness

### Objective

Establish a stable V1.0.0 baseline that is locally usable, tested, documented, maintainable, versioned, and ready for portfolio publication without requiring a public live deployment.

### Work Products

Phase 8 defines corrective/adaptive/preventive maintenance, semantic versioning, dependency updates, security maintenance, incident and defect handling, backup/recovery maintenance, final release audit, release checklist, changelog, release notes, and an integrated release-readiness CI gate.

### Product Stability Rule

M7 is not a feature-development phase. Product changes are limited to confirmed release blockers or defects. New capabilities require change control and a later release plan.

### Hosting Boundary

GitForge V1.0.0 is a GitHub portfolio/local-use release. Public hosting, a VPS, public DNS, and a live domain are not Phase 8 acceptance criteria. The existing deployment package remains part of the engineering evidence.

### Exit Criteria

Phase 8 completes when the final release candidate has no known critical/high blocker, all release-readiness checks are green, documentation matches the implemented product, the M7 review is approved, and the approved baseline is merged for V1.0.0 publication.

## Scope Integrity

The current implementation and approved evidence take precedence over stale planning statements. GitForge V1.0.0 uses an owner-centric repository permission model and does not claim organization/team collaboration, SSH Git transport, distributed repository storage, or other unimplemented capabilities.

## Current Position

M0 through M6 are Complete. M7 / Phase 8 is the active final lifecycle stage for V1.0.0 release and maintenance readiness.
