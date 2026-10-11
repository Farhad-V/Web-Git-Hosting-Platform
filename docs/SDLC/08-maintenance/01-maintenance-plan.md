# Maintenance Plan

| Field | Value |
|---|---|
| Project | GitForge — Web Git Hosting Platform |
| Version | V1.0.0 |
| SDLC phase | 8 — Maintenance |
| Milestone | M7 — Release and Maintenance Readiness |
| Status | In Validation |
| Date | 2026-08-23 |

## Purpose

This plan defines how GitForge V1.0.0 is kept stable after the implementation, testing, and deployment-readiness phases. The project is a GitHub portfolio project and does not require public hosting.

## Maintenance Scope

Maintenance covers defect correction, security updates, dependency updates, documentation corrections, compatibility fixes, regression prevention, backup/recovery procedure upkeep, and controlled future releases.

New features are not maintenance work. They require explicit scope approval and a new minor or major release plan.

## Maintenance Principles

- preserve the approved V1.0.0 behavior;
- reproduce defects before changing code where practical;
- add or update automated tests for corrected defects;
- keep backend, frontend, database, Git transport, and documentation changes traceable;
- keep secrets and local runtime data out of Git;
- require green CI before merging maintenance changes;
- prefer small, reversible changes;
- keep public/private authorization and Git storage boundaries fail-closed.

## Maintenance Categories

### Corrective
Fix confirmed defects without changing approved product scope.

### Adaptive
Make compatibility changes required by supported PHP, Node.js, PostgreSQL, Git, browser, or dependency updates.

### Perfective
Improve maintainability, documentation, observability, or developer ergonomics without changing product semantics.

### Preventive
Address dependency vulnerabilities, deprecations, test gaps, backup/recovery weaknesses, and operational risks before they become incidents.

## Baseline

The V1.0.0 maintenance baseline is the commit approved by the M7 milestone review. Subsequent maintenance releases must remain traceable to that baseline.
