# Stakeholders and external systems

## Stakeholders

| Role | Interest and responsibility |
| --- | --- |
| Project owner | Sets scope, priorities, release decisions, and acceptance criteria |
| Repository owner | Manages owned repositories, Git credentials, branch defaults, and merges |
| Signed-in participant | Can propose and discuss requests where repository read permissions allow |
| Public visitor | Can inspect public repository content and discussions |
| Operator | Manages runtime, queue workers, health, backups, recovery, and deployment |
| Maintainer/contributor | Implements changes, maintains docs, and produces verification evidence |
| Reviewer/evaluator | Assesses correctness, security, usability, and traceability |
| Dependency maintainer | Provides upstream framework, library, and tool updates |

The historical ST-01 to ST-09 register is preserved in the [combined definition](README.md#stakeholders). Roles describe responsibilities, not a claim of an established team or permissions beyond implemented policy.

## Systems and dependencies

| System | Interaction and boundary |
| --- | --- |
| Browser | React UI, session/API requests, supported PDF preview |
| Git client | Clone/fetch/push via Smart HTTP with appropriate token authentication |
| PostgreSQL | Application metadata, session/queue data according to configuration, workflow records |
| System Git and bare storage | Controlled subprocesses and Git objects/refs; not arbitrary shell execution |
| Composer/npm ecosystem | Dependency installation and audits during engineering workflows |
| GitHub | Hosts this project's source, pull requests, and CI; hosted GitForge repositories are separate |
| Production web/runtime stack | Nginx/PHP-FPM profiles and deployment tooling; separate from local Vite |

See [stakeholder diagram](stakeholder-ecosystem-diagram.md), [context diagram](project-context-diagram.md), and [trust-boundary analysis](../SDLC/System-Analysis/15-trust-boundary-analysis.md).
