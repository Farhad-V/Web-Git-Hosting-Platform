# Software Development Life Cycle

## GitForge - Web Git Hosting Platform

**Target release:** V1.0.0  
**Development approach:** Eight-phase SDLC  
**Last reconciled:** 2026-08-23

## Purpose

This document is the authoritative lifecycle summary for GitForge V1.0.0. Detailed evidence for each completed phase is stored in the corresponding `docs/<phase>/` directory. Milestone state is tracked in `docs/MILESTONES.md`.

## SDLC Status

| Phase | Milestone | Status | Primary evidence |
|---|---|---|---|
| 1. Project Definition | M0 | Complete | `docs/01-project-definition/` |
| 2. Requirements Engineering | M1 | Complete | `docs/02-requirements-engineering/` |
| 3. System Analysis | M2 | Complete | `docs/03-system-analysis/` |
| 4. System Design | M3 | Complete | `docs/04-system-design/` |
| 5. Implementation | M4 | Complete | `docs/05-implementation/` plus application source |
| 6. Testing | M5 | Complete | `docs/06-testing/`, automated suites, CI evidence |
| 7. Deployment | M6 | Complete | `deployment/`, `docs/07-deployment/`, Deployment Validation |
| 8. Maintenance / Release transition | M7 | Not started | Begins with release-readiness work |

## Phase 1 - Project Definition

**Complete.** Product purpose, scope, stakeholders, constraints, feasibility, risks and success criteria are approved in `docs/01-project-definition/`.

## Phase 2 - Requirements Engineering

**Complete.** The approved requirements package is in `docs/02-requirements-engineering/`, including functional, non-functional, data and interface requirements, use cases, user stories and traceability.

## Phase 3 - System Analysis

**Complete.** Actors, domain behavior, workflows, states, trust boundaries, threats, Git transport, storage and failures are analyzed in `docs/03-system-analysis/`.

## Phase 4 - System Design

**Complete.** Architecture, components, APIs, persistence, authorization, Git execution, repository storage, frontend structure, observability, security and deployment topology are defined in `docs/04-system-design/`.

## Phase 5 - Implementation

**Complete for the approved V1.0.0 scope.** The application implements Laravel 12 and React/TypeScript with PostgreSQL, session authentication, owner-centric repository management, managed bare repositories, Git Smart HTTP clone/fetch/push, personal access tokens, repository browsing and observability.

Implementation evidence is maintained in `docs/05-implementation/` and the source tree.

## Phase 6 - Testing

**Complete.** The formal package in `docs/06-testing/` contains 141 test cases and traceability across the approved requirement set. CI validates backend behavior, PostgreSQL migrations, formatting, dependency security, frontend lint/tests/build and Git transport behavior.

## Phase 7 - Deployment

**Complete for the portfolio V1.0.0 release.**

M6 provides:

- a production Linux topology;
- production environment, Nginx and PHP-FPM templates;
- persistent repository storage rules;
- deployment runbook;
- backup, restore and rollback procedures;
- preflight/smoke/backup verification scripts;
- an automated production-like Deployment Validation workflow.

Deployment Validation run `32659338381` passed production Composer installation, PostgreSQL 18 migrations, Laravel configuration/route caching, clean frontend production build, Nginx syntax and Laravel `/up` liveness. Backend CI run `32659338516` also passed.

A public VPS/domain/certificate is optional demonstration infrastructure and does not block M6 for this GitHub portfolio release. The deployment package is ready to attach to a real Linux host later.

## Phase 8 - Maintenance / Release Transition

**Not started.** M7 performs the final V1.0.0 release-readiness audit, release notes/changelog, maintenance guidance, final portfolio review, tag and GitHub Release.

## Scope Integrity Rule

A capability is considered part of V1.0.0 only when it is represented by approved scope and implemented/verified in the current codebase. Organization/team collaboration, SSH Git transport, distributed storage and other future extensions remain outside V1.0.0 unless introduced through change control.

## Current SDLC Position

GitForge has completed **M0 through M6**. The next objective is **M7 — V1.0.0 Release and Maintenance Readiness**.

**Document status:** Approved and reconciled as of 2026-08-23.
