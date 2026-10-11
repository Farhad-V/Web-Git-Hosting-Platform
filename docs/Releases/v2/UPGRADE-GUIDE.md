# Draft v2.0.0 upgrade guide

**Unreleased planning guidance.** This is not deployment approval or a claim that an upgrade has been executed.

## Permission and historical versions

Read [LICENSE](../../../LICENSE) and obtain prior written permission before using original materials covered by the new policy. Upstream licenses and earlier permissions remain applicable. Existing v1 tags stay unchanged; do not overwrite them or assume the new policy revokes previous grants.

## Preparation

Select a verified candidate rather than an unspecified moving branch. Review migration compatibility and preserve the existing APP_KEY. Block new writes, drain requests/pushes and pause workers/direct writers before creating and verifying a paired PostgreSQL/Git backup. Test recovery on an isolated target before committing to the upgrade window.

## Application and runtime changes

Install the selected candidate's locked dependencies, build frontend assets, apply the additive repository-workflows migration, rebuild caches and validate ingress/runtime configuration. Git 2.38 or later is required for modern merge-tree operations. Run a dedicated `gitforge-actions` worker and restart it with changed code. Linux service setup and supervision remain host responsibilities; the supplied Windows starter is for local development.

Update existing client remotes after repository rename; there is no former-name redirect. Actions performs built-in repository checks only, not arbitrary project commands or workflow YAML.

## Verification and failure handling

Verify sessions/authorization, Git clone/fetch/push, branches/tags/commits/files, bounded previews, PR conflicts/stale refs/merge recovery, Actions and manual UI journeys. Keep writes paused after required failures. Code rollback is safe only when the previous code supports the current schema; incompatible restoration requires a reviewed paired recovery set and compatible application version.

Use the [deployment plan](../../SDLC/07-deployment/01-deployment-plan.md), [backup procedure](../../SDLC/07-deployment/07-backup-and-restore.md), [recovery procedure](../../SDLC/07-deployment/08-rollback-and-recovery.md) and [candidate checklist](RELEASE-CHECKLIST.md). Record actual results; no guaranteed recovery duration or production SLA is asserted.
