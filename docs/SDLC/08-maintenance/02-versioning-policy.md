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

## Current development and licensing boundary

Current development is **Unreleased — planned v2.0.0**, with frontend package metadata `2.0.0-dev`. The last published baseline remains v1.0.1. The major version boundary is an explicit project policy for the permission-only licensing change and accumulated development scope; licensing changes are not themselves proof of a public API or schema incompatibility under Semantic Versioning.

Do not move or replace existing release tags. Historical snapshots retain their contents and applicable licensing terms. Permission requirements for new original materials do not revoke earlier grants or override third-party licenses.

Use a development/pre-release identifier until candidate gates pass. Before publication, select and validate the exact candidate, align final package metadata with `2.0.0`, finalize release notes and changelog, and only then create `v2.0.0` through the approved publication procedure. A document labeled v2.0.0 is not a published release. See the [version 2 checklist](../../Releases/v2/RELEASE-CHECKLIST.md).
