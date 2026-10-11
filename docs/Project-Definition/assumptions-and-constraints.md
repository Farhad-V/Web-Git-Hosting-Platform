# Assumptions and constraints

| Area | Assumption or constraint | Consequence |
| --- | --- | --- |
| Toolchain | Validated baseline uses PHP 8.4, PostgreSQL 18, Node.js 24, Composer, npm, and system Git | Match documented environments when reproducing CI |
| Git merges | Modern merge-tree behavior requires Git 2.38 or later | Check Git version before enabling merge workflows |
| Local ports | Frontend 5173, backend 8000; frontend refuses an occupied port | Resolve port conflicts rather than expecting silent fallback |
| Authorization | Owner-centric repository model; private repositories owner-only | Do not assume organization or collaborator permissions |
| Storage | Bare Git objects and database metadata are separate persistence systems | Back up and restore them together |
| Runtime | Actions uses the dedicated gitforge-actions queue | Keep a worker running; restart after reboot or code deployment |
| CI boundary | Built-in Git checks only | Never treat repository scripts or YAML as runnable workflows |
| Preview | Text and PDF endpoints enforce configured limits | Large or unsupported content needs download/clone fallback |
| Browser | Blur, PDF viewing, and accessibility behavior vary by browser | Use documented fallbacks and platform-specific QA |
| Windows tests | Directory-symlink creation may be unavailable | Record skips; use a supporting environment for that coverage |
| Release status | Main may contain changes newer than v1.0.1 | Distinguish merged development from tagged release behavior |

Public hosting is optional for the portfolio. Production deployment requires its own configuration, TLS/network setup, least-privilege access, backups, and supervised workers. These documents contain no production credentials. See [deployment guidance](../SDLC/Deployment/04-deployment-runbook.md).
