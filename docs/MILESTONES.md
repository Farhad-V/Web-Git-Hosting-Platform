# GitForge V1.0.0 Milestones

**Last reconciled:** 2026-08-23

This file is the authoritative milestone summary for GitForge V1.0.0. Detailed phase evidence lives in the corresponding `docs/` directories.

## Status Definitions

| Status | Meaning |
|---|---|
| Not started | Work has not begun |
| In progress | Work is active and exit criteria are not yet fully satisfied |
| Complete | Deliverables, verification, review, and merge evidence are present |

---

## M0 - Project Definition

**SDLC phase:** Project Definition  
**Status:** Complete

### Evidence

- project charter;
- problem statement;
- product vision;
- project goals;
- stakeholders;
- scope, constraints, feasibility, risks, success metrics, and release strategy.

Primary location: `docs/01-project-definition/`.

---

## M1 - Requirements Engineering

**SDLC phase:** Requirements Engineering  
**Status:** Complete

### Evidence

- requirements engineering plan;
- elicitation and actor analysis;
- functional requirements;
- non-functional requirements;
- data requirements;
- interface requirements;
- business rules;
- use cases and detailed use-case specifications;
- user stories and acceptance criteria;
- requirements traceability.

Primary location: `docs/02-requirements-engineering/`.

---

## M2 - System Analysis

**SDLC phase:** System Analysis  
**Status:** Complete

### Evidence

Approved analysis covers system context, domain behavior, workflows, state and failure behavior, authentication, authorization, Git Smart HTTP, repository storage, personal access tokens, repository browsing, security boundaries, and supporting diagrams.

Primary location: `docs/03-system-analysis/`.

---

## M3 - System Design

**SDLC phase:** System Design  
**Status:** Complete

### Evidence

Approved design covers architecture, components, persistence, APIs, authorization, managed Git storage, Git process execution, Smart HTTP transport, frontend structure, observability, security, deployment topology, and design diagrams.

Primary location: `docs/04-system-design/`.

---

## M4 - Implementation

**SDLC phase:** Implementation  
**Status:** Complete

### Implemented V1.0.0 capabilities

- Laravel 12 backend;
- React 19 / TypeScript frontend;
- PostgreSQL persistence;
- registration, login, authenticated identity, logout;
- repository creation, owner listing, metadata retrieval, and visibility update;
- public/private repository authorization;
- managed bare Git repository initialization and resolution;
- Git Smart HTTP clone, fetch, and push;
- personal access token management and Git authentication;
- repository branch, commit, tree, and text-blob browsing;
- structured observability, liveness, and readiness support;
- deployment-oriented environment, Nginx, and PHP-FPM profiles.

### Important scope clarification

Earlier milestone drafts referenced capabilities such as archive/unarchive, asynchronous repository deletion, token expiration/ability scopes, and other lifecycle behavior that are not part of the current implemented V1.0.0 surface. Those stale statements are not authoritative and have been removed from this milestone summary.

Primary evidence: application source plus `docs/05-implementation/`.

---

## M5 - Testing

**SDLC phase:** Testing  
**Status:** Complete

### Evidence

The formal testing package includes:

- testing plan;
- 141 formal test cases;
- environment validation;
- authentication testing;
- repository metadata testing;
- authorization testing;
- repository storage testing;
- Git process execution testing;
- repository browser testing;
- Git Smart HTTP read/write testing;
- personal access token testing;
- Web UI testing;
- security testing;
- observability and failure testing;
- system/end-to-end testing;
- regression testing;
- acceptance testing;
- test traceability;
- testing validation;
- M5 milestone review.

Primary location: `docs/06-testing/`.

Latest audited CI evidence includes a backend run with **205 passing tests and 1008 assertions**, successful migrations, Pint validation, and no Composer security advisory. Frontend CI validates linting, tests, production build, and production dependency audit.

---

## M6 - Deployment

**SDLC phase:** Deployment  
**Status:** In progress

### Existing deliverables

- production deployment guide;
- production environment template;
- Nginx production profile;
- PHP-FPM least-privilege pool profile;
- HTTPS and Git Smart HTTP routing design;
- persistent repository storage requirements;
- backup/restore and rollback guidance;
- liveness/readiness expectations.

Primary location: `deployment/`.

### Remaining exit criteria

M6 becomes Complete only after a production-like environment has been provisioned and verified, including:

- real host/domain configuration;
- valid TLS;
- successful dependency installation and frontend production build;
- successful migrations;
- correct repository-storage ownership and permissions;
- passing liveness and readiness checks;
- production smoke tests;
- verified database plus repository-storage backup and restore;
- tested rollback procedure;
- release-candidate approval.

---

## M7 - V1.0.0 Release and Maintenance Readiness

**SDLC phase:** Maintenance / Release transition  
**Status:** Not started

### Planned deliverables

- final release notes and changelog;
- installation and operator documentation;
- user/API documentation as required;
- security and maintenance guidance;
- incident and dependency-update procedures;
- backup schedule and operational checks;
- versioning policy;
- Git tag `v1.0.0`;
- GitHub V1.0.0 release.

### Exit criteria

- M6 is Complete;
- V1.0.0 Definition of Done is satisfied;
- final documentation is reviewed;
- release tag and GitHub release are created from the approved commit;
- maintenance responsibilities and recurring operational procedures are documented.

---

## Milestone Summary

| Milestone | Status |
|---|---|
| M0 - Project Definition | Complete |
| M1 - Requirements Engineering | Complete |
| M2 - System Analysis | Complete |
| M3 - System Design | Complete |
| M4 - Implementation | Complete |
| M5 - Testing | Complete |
| M6 - Deployment | In progress |
| M7 - Release and Maintenance Readiness | Not started |

## Current Position

GitForge has completed the formal engineering lifecycle through **Testing (M5)**. The active milestone is **M6 - Deployment**. No new V1.0.0 feature should be added unless it is required to resolve a verified deployment blocker or is approved through change control.
