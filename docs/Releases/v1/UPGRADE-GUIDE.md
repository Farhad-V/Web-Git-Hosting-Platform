# Version 1 upgrade guide

Choose a tagged release for stable use. Main contains newer merged work that is not automatically a published version. Read the target version's notes before upgrading.

## Pre-upgrade preparation

1. Identify the installed commit, target commit/tag, environment, and operator responsible for recovery.
2. Preserve environment secrets, the existing application key, database, and managed Git storage. Do not regenerate the application key as an upgrade step.
3. Take and verify a coordinated database/Git-storage backup using the [backup procedure](../../SDLC/Deployment/07-backup-and-restore.md).
4. Pause and drain API/Git writes, queued checks, scheduled jobs, and direct repository writers as required by that procedure.
5. Confirm validated tooling and review configuration differences in the target version.

## v1.0.0 to v1.0.1

The patch adds no application schema migration. Install locked dependencies, rebuild the frontend, and clear/rebuild Laravel caches according to the deployment profile. Review public clone URL configuration and the strengthened backup pause-and-drain requirements. See [patch notes](v1.0.1.md) for exact changes.

## Preparing for merged development changes

This is guidance for a future approved candidate, not a declaration of its readiness. The collaboration migration adds pull request, discussion, and Actions run tables. Apply it before exposing the new UI. Run the dedicated `gitforge-actions` worker and restart it after code changes. Modern merge-tree requires Git 2.38 or later.

Repository renames preserve IDs/storage but require existing clients to update their remote URLs. Actions executes built-in Git checks, not arbitrary scripts. Review [feature contracts](../../SDLC/Implementation/Features/repository-collaboration.md) and [unreleased notes](UNRELEASED.md).

## Post-upgrade verification

Check readiness, authentication, visibility enforcement, Git clone/fetch/push, browsing, and affected collaboration/Actions flows with isolated data. Verify worker health and browser light/dark behavior. Record exact versions and outcomes. Resume writes only after checks pass or follow the [recovery procedure](ROLLBACK-AND-RECOVERY.md).
