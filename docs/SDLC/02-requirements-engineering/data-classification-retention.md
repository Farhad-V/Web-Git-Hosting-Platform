# Data classification and retention

| Class | Examples | Handling |
| --- | --- | --- |
| Secrets | Passwords, token secrets, application key, database credentials | Non-reversible credential storage where applicable; preserve protected environment secrets; do not log or document secret values |
| Account metadata | Account identifiers and contact/profile data | Restrict access to intended authenticated flows; include in operator privacy review |
| Repository/workflow data | Code, Git author metadata, branches/tags, requests/comments, check results | Apply public/private repository permissions and nested resource scoping |
| Operational data | Health, correlation logs, queue/session records | Controlled diagnostics and configured operational access |
| Recovery sets | Database dump plus Git archive | Coordinated capture, restricted storage, verification, and controlled restore |

## Retention boundaries

RE-D01: retention periods and log rotation must be explicitly chosen by the operator; no automatic expiration period is claimed here. RE-D02: backups must use compatible retention and coordinated recovery sets. RE-D03: token revocation must prevent subsequent authentication. RE-D04: repository/workflow data must remain subject to visibility policy.

Git history may retain committed data after a working-tree deletion. Repository deletion, account erasure, automatic purge, and backup erasure are not claimed as implemented workflows. The collaboration migration rollback drops workflow history and must not be used as a routine retention tool.

Before production operation, define retention periods, backup/log access, incident handling, and any required erasure process. Those policy decisions are pending unless documented elsewhere by the operator. See [backup procedure](../07-deployment/07-backup-and-restore.md) and [security/privacy requirements](security-and-privacy-requirements.md).
