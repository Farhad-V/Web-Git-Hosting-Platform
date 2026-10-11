# Rollback and recovery

Use the approved [deployment rollback runbook](../../SDLC/07-deployment/08-rollback-and-recovery.md) and [backup/restore procedure](../../SDLC/07-deployment/07-backup-and-restore.md). This document supplies release context, not an automatic recovery command.

## Before changing versions

Record the running code commit, environment/configuration, database schema, repository storage root, worker state, and verified recovery set. Preserve the existing application key and secrets. Backups must coordinate metadata and Git objects/refs while writers are paused and drained.

## Decide the recovery approach

- A code-only rollback is appropriate only when the older code is compatible with the current schema, configuration, and stored data.
- If compatibility is uncertain, keep writes paused and investigate against a recovery copy before altering live data.
- Restoring a previous recovery set may discard writes made after that snapshot. Explicitly review that impact with the operator/project owner before restoration.
- Never independently restore the database and an unrelated Git archive.

## Collaboration migration warning

The newer collaboration schema stores pull requests, comments, and Actions history. Rolling it back drops that history. Do not run migration rollback merely to change code versions. Assess compatibility or restore a coordinated recovery set through the documented procedure.

Git merges update repository refs as well as application state. Never attempt recovery by resetting user refs or force-pushing a prior release tag. Prepared merge reconciliation and repository checks must be considered when diagnosing interrupted operations.

## Recovery acceptance

Verify readiness, permissions, metadata/storage alignment, representative Git reads/writes, affected workflows, and queue behavior with isolated checks. Resume writers only after the responsible operator confirms recovery. Record outcome, data impact, and follow-up defects.
