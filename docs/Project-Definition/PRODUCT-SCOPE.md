# Product scope

## Users and responsibilities

Repository owners create repositories, choose visibility, manage personal access tokens, and perform authorized Git operations. Visitors can read public repositories. Private repository data remains restricted by server-side authorization.

## Released baseline

Version 1 provides registration and session authentication, repository metadata and visibility, managed bare Git storage, Git Smart HTTP clone/fetch/push, personal access tokens, branch/commit/tree/text browsing, health endpoints, and deployment and recovery guidance.

## Development branch additions

The current feature branch adds repository renaming, tag and commit navigation, a file sidebar, PDF preview/download support, branch creation/default selection, same-repository pull requests and discussions, owner merges with concurrency checks, and built-in Actions checks. Light and dark themes share a glass visual system.

Actions checks validate repository data. They do not execute arbitrary repository scripts or workflow YAML. Rename operations preserve repository identity and storage, but existing Git clients must update their remote URL.

## Boundaries

This project does not claim full GitHub parity. Fork collaboration, organization/team permissions, approval reviews, protected branches, required merge checks, SSH transport, distributed storage, and arbitrary CI runners are outside the implemented scope. Public hosting is not required for the portfolio release.

See [collaboration contracts](../features/repository-collaboration.md) and [version status](../Releases/v1/README.md) before treating a capability as released.
