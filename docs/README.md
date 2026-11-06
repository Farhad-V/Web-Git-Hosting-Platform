# GitForge documentation

GitForge is the Web Git Hosting Platform: a Laravel API and React/TypeScript application backed by PostgreSQL and managed bare Git repositories.

## Start here

| Folder | Purpose |
| --- | --- |
| [Project-Definition](Project-Definition/README.md) | Product purpose, users, capabilities, and scope |
| [Project-Management](Project-Management/README.md) | Delivery process, backlog, risks, and completion criteria |
| [Releases](Releases/README.md) | Version 1 release history and upgrade guidance |
| [SDLC](SDLC/README.md) | Requirements through maintenance, with links to engineering evidence |
| [Sprints](Sprints/README.md) | Documented delivery increments and verification records |

## Version context

The released baseline is **v1.0.1**. Collaboration and refined UI work was merged into `main` through [PR #27](https://github.com/Farhad-V/Web-Git-Hosting-Platform/pull/27). It remains unreleased until a new version is tagged and published. See [the unreleased overview](Releases/v1/UNRELEASED.md).

The detailed engineering files now live inside their related subject folders. Historical test totals and milestone statements describe their recorded versions, rather than the current development branch.

## Operational guidance

Start with the [project setup instructions](../README.md), [collaboration and Actions worker guide](SDLC/05-implementation/Features/repository-collaboration.md), [visual system](SDLC/05-implementation/Features/visual-system.md), and [deployment runbook](SDLC/07-deployment/04-deployment-runbook.md). Never store account passwords, personal access tokens, or environment secrets in documentation.

## Diagram catalog

Browse the [PlantUML diagram catalog](diagrams/README.md) for editable sources and rendered SVG previews across the project.
