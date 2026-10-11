# GitForge documentation

GitForge is the Web Git Hosting Platform: a Laravel API and React/TypeScript application backed by PostgreSQL and managed bare Git repositories.

## Start here

| Folder | Purpose |
| --- | --- |
| [Project-Definition](Project-Definition/README.md) | Product purpose, users, capabilities, and scope |
| [Project-Management](Project-Management/README.md) | Delivery process, backlog, risks, and completion criteria |
| [Releases/v1](Releases/v1/README.md) | Version 1 release history and upgrade guidance |
| [SDLC](SDLC/README.md) | Requirements through maintenance, with links to engineering evidence |
| [Sprints](Sprints/README.md) | Documented delivery increments and verification records |

## Version context

The released baseline is **v1.0.1**. Collaboration and refined UI work is on `feat/glassmorphism-themes` in [PR #27](https://github.com/Farhad-Valizadegan/Web-Git-Hosting-Platform/pull/27); it is not part of the released baseline until merged and released. See [the unreleased overview](Releases/v1/UNRELEASED.md).

The numbered folders remain the detailed engineering record and retain their existing paths. The release folder capitalization is normalized to `Releases`; links within the project use that spelling. Historical test totals and milestone statements describe their recorded versions, rather than the current development branch.

## Operational guidance

Start with the [project setup instructions](../README.md), [collaboration and Actions worker guide](features/repository-collaboration.md), [visual system](features/visual-system.md), and [deployment runbook](07-deployment/04-deployment-runbook.md). Never store account passwords, personal access tokens, or environment secrets in documentation.
