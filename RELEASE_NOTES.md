# GitForge V1.0.0 Release Notes

GitForge V1.0.0 is the first stable portfolio release of the Web Git Hosting Platform.

## What V1.0.0 Provides

GitForge provides an owner-centric self-hosted Git workflow with a Laravel API, React/TypeScript interface, PostgreSQL metadata, managed bare Git repositories, and Git Smart HTTP transport.

Users can register and authenticate, create repositories, control public/private visibility, create and revoke personal access tokens, clone/fetch/push with real Git clients, and browse branches, commits, trees, and text files through the web interface.

## Engineering Quality

The release is supported by formal SDLC documentation, automated backend/frontend testing, real-Git Smart HTTP end-to-end tests, PostgreSQL integrity tests, security tests, performance checks, dependency audits, reproducible deployment validation, and a final release-readiness gate.

## Local Use

The validated development baseline uses PHP 8.4, PostgreSQL 18, Node.js 24, npm, Composer, and system Git. The Laravel server runs on `127.0.0.1:8000` and the Vite frontend on `127.0.0.1:5173`; Vite proxies `/api` requests to Laravel.

## Hosting Scope

Public hosting is not part of the V1.0.0 acceptance criteria. The project is intended to be cloned, inspected, run locally, tested, and evaluated as a GitHub portfolio project. A production-oriented deployment package remains included to demonstrate deployment engineering.

## Explicit Non-Goals

V1.0.0 does not claim organization/team collaboration, SSH Git transport, distributed Git storage, or other platform capabilities that are not implemented in the current codebase.

## Maintenance

Maintenance guidance is located in `docs/08-maintenance/` and covers versioning, dependencies, security, incident/defect handling, backup/recovery maintenance, and release governance.
