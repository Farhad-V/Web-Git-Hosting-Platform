# Software Development Life Cycle

## GitForge - Web Git Hosting Platform

**Target release:** V1.0.0  
**Development approach:** Eight-phase SDLC  
**Last reconciled:** 2026-08-23

---

## 1. Purpose

This document is the authoritative lifecycle summary for GitForge V1.0.0. Detailed evidence for each completed phase is stored in the corresponding `docs/<phase>/` directory. Milestone state is tracked in `docs/MILESTONES.md`.

GitForge is a self-hosted Git hosting platform with a Laravel API backend, React/TypeScript frontend, PostgreSQL persistence, managed bare Git repository storage, Git Smart HTTP transport, session authentication, personal access tokens, repository authorization, repository browsing, observability, and production deployment profiles.

---

## 2. SDLC Phases

GitForge follows these phases:

1. Project Definition
2. Requirements Engineering
3. System Analysis
4. System Design
5. Implementation
6. Testing
7. Deployment
8. Maintenance

Controlled feedback between phases is permitted, but approved V1.0.0 scope changes must remain traceable.

---

## 3. Current Phase Status

| Phase | Milestone | Status | Primary evidence |
|---|---|---|---|
| 1. Project Definition | M0 | Complete | `docs/01-project-definition/` |
| 2. Requirements Engineering | M1 | Complete | `docs/02-requirements-engineering/` |
| 3. System Analysis | M2 | Complete | `docs/03-system-analysis/` |
| 4. System Design | M3 | Complete | `docs/04-system-design/` |
| 5. Implementation | M4 | Complete | `docs/05-implementation/` plus application source |
| 6. Testing | M5 | Complete | `docs/06-testing/`, automated suites, CI evidence |
| 7. Deployment | M6 | In progress | `deployment/`; production-like verification still required |
| 8. Maintenance | M7 | Not started | Begins after V1.0.0 release |

---

## 4. Phase 1 - Project Definition

### Objective
Establish product purpose, scope, constraints, stakeholders, feasibility, risks, success criteria, and release direction.

### Status
**Complete.** The approved artifacts are maintained in `docs/01-project-definition/`.

---

## 5. Phase 2 - Requirements Engineering

### Objective
Define authoritative functional, non-functional, data, interface, business-rule, use-case, user-story, and traceability requirements for V1.0.0.

### Status
**Complete.** The approved requirements set is maintained in `docs/02-requirements-engineering/`.

The formal primary requirement inventory contains:

- 98 functional requirements;
- 98 non-functional requirements;
- 94 data requirements;
- 100 interface requirements.

---

## 6. Phase 3 - System Analysis

### Objective
Analyze actors, domain behavior, workflows, state transitions, trust boundaries, threats, failures, Git transport behavior, storage behavior, and system interactions.

### Status
**Complete.** Analysis artifacts and diagrams are maintained in `docs/03-system-analysis/`.

---

## 7. Phase 4 - System Design

### Objective
Define the architecture, components, APIs, persistence model, authorization rules, Git execution boundaries, repository storage, frontend structure, observability, security, and deployment topology.

### Status
**Complete.** Approved design artifacts are maintained in `docs/04-system-design/`.

---

## 8. Phase 5 - Implementation

### Objective
Implement the approved V1.0.0 design without introducing unapproved scope.

### Implemented product capabilities

- Laravel 12 API backend;
- React 19 and TypeScript frontend;
- PostgreSQL persistence;
- user registration, login, profile retrieval, and logout;
- repository creation, listing, detail retrieval, and visibility updates;
- public/private repository authorization;
- managed bare Git repository storage;
- Git Smart HTTP clone, fetch, and push;
- personal access token creation, listing, revocation, and Git authentication;
- repository branch, commit, tree, and text-blob browsing;
- request correlation, structured operational logging, liveness, and readiness checks;
- production-oriented Nginx, PHP-FPM, and environment templates.

### Status
**Complete for the approved V1.0.0 implementation scope.** Implementation evidence and validation are maintained in `docs/05-implementation/`.

Capabilities not represented by the current implementation must not be described as implemented merely because they appeared in earlier planning drafts.

---

## 9. Phase 6 - Testing

### Objective
Verify functional correctness, security boundaries, persistence, Git process behavior, Smart HTTP transport, frontend behavior, observability, failure handling, performance, regression behavior, system workflows, and acceptance criteria.

### Verification model

Testing includes:

- PHPUnit/Laravel unit and feature tests;
- real-Git Smart HTTP end-to-end tests;
- PostgreSQL integrity tests;
- security tests;
- performance tests;
- Vitest and React Testing Library tests;
- ESLint and TypeScript build validation;
- Laravel Pint;
- Composer and npm dependency audits;
- GitHub Actions CI;
- formal test traceability and milestone validation.

### Status
**Complete.** The formal testing package contains 141 test cases and is maintained in `docs/06-testing/`.

The latest audited backend CI execution passed 205 tests with 1008 assertions, migrations, formatting validation, and Composer security audit. Frontend CI validates lint, tests, production build, and production dependency audit.

---

## 10. Phase 7 - Deployment

### Objective
Deploy GitForge reproducibly in a production-like environment and verify application, infrastructure, security, backup, recovery, and rollback behavior.

### Existing deployment assets

- `deployment/README.md`;
- `deployment/env/production.env.example`;
- `deployment/nginx/gitforge.conf`;
- `deployment/php-fpm/gitforge.conf`.

### Remaining verification

- provision a production-like host;
- configure the real domain and TLS certificates;
- validate Nginx and PHP-FPM configuration on the target host;
- install production dependencies and build the frontend;
- run database migrations;
- provision persistent repository storage and permissions;
- verify liveness and readiness;
- execute production smoke tests;
- verify database and Git-repository backup/restore;
- test rollback procedure;
- approve the V1.0.0 release candidate.

### Status
**In progress.** Configuration artifacts exist, but deployment is not complete until target-environment verification succeeds.

---

## 11. Phase 8 - Maintenance

### Objective
Operate the released system, manage defects and incidents, update dependencies, verify backups, handle security updates, and control future scope changes.

### Status
**Not started.** Maintenance begins after the V1.0.0 production release.

---

## 12. Scope Integrity Rule

The implementation and approved phase artifacts take precedence over stale planning statements. A capability is considered part of V1.0.0 only when it is supported by the approved requirements/design and implemented and verified in the current codebase.

Future capabilities such as organization/team collaboration, SSH Git transport, distributed storage, and other unapproved extensions remain outside V1.0.0 unless formally introduced through change control.

---

## 13. Current SDLC Position

GitForge has completed M0 through M5 and is entering **M6 - Deployment**. The next engineering objective is a reproducible production-like deployment followed by release approval and then maintenance readiness.

**Document status:** Approved and reconciled with `main` as of 2026-08-23.
