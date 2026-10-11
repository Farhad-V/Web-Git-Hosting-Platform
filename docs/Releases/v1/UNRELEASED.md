# Unreleased development overview

The following changes were merged into `main` through [PR #27](https://github.com/Farhad-Valizadegan/Web-Git-Hosting-Platform/pull/27). They remain unreleased; this document does not declare a new version.

## Capabilities

- Repository renaming while retaining repository identity and managed storage.
- Code, branch, commit, tag, pull request, Actions, and Settings navigation.
- Expandable file sidebar and streamed PDF preview with download fallback.
- Branch creation/default selection and same-repository pull requests with discussions, comparison, close/reopen, and owner merge.
- Built-in Actions checks, queue-backed execution, history, controlled logs, and cancellation.
- Shared glass surfaces, readable semantic colors, light/dark preference persistence, and responsive floating navigation.
- Fixed local frontend port 5173 and a documented Windows Actions worker starter.

## Upgrade considerations

Apply the additive repository-workflows migration before exposing collaboration features. Back up database metadata and bare Git repositories together before upgrading. Run the dedicated `gitforge-actions` queue worker and restart it after deploying changed code. Git 2.38 or later is required for the modern merge-tree operation.

After renaming a repository, update existing local Git remotes to its new clone URL. This implementation does not provide a redirect for the former name. Actions run built-in repository checks only; arbitrary scripts and workflow YAML are not supported.

## Verification context

At commit `22ee75b`, GitHub Backend CI passed 227 tests with 1,153 assertions, and frontend, deployment, and release gates passed. The 2026-10-11 local QA pass passed 57 frontend tests and 65 backend unit tests; one Windows symlink test was skipped. The startup fix at `64cc788` passed lint/build, service readiness, and an occupied-port regression check. These are recorded checkpoints, not a claim that later commits have identical results.

Feature details: [collaboration](../../SDLC/Implementation/Features/repository-collaboration.md) and [visual system](../../SDLC/Implementation/Features/visual-system.md).
