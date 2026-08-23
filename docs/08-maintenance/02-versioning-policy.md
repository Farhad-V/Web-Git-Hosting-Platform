# Versioning Policy

GitForge uses Semantic Versioning for published portfolio releases.

## Format

`MAJOR.MINOR.PATCH`

- MAJOR: incompatible product or data-contract changes.
- MINOR: backward-compatible approved features.
- PATCH: backward-compatible defect, security, documentation, or maintenance fixes.

## V1.0.0 Baseline

V1.0.0 establishes the first stable owner-centric GitForge product baseline. It includes session authentication, repository management, public/private authorization, managed bare Git storage, Git Smart HTTP clone/fetch/push, personal access tokens, repository browsing, observability, automated testing, and reproducible deployment readiness.

## Pre-release Labels

If required in future, release candidates may use labels such as `1.1.0-rc.1`. The V1.0.0 portfolio baseline is approved only after all M7 release gates pass.

## Scope Discipline

Organization/team collaboration, SSH Git transport, distributed repository storage, and other unimplemented capabilities are not silently introduced in patch releases.
