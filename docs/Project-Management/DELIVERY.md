# Delivery process

## Change workflow

1. Describe the user problem and observable acceptance criteria.
2. Inspect the affected requirements, code, data, and authorization boundaries.
3. Implement a focused change without altering unrelated repositories or user data.
4. Run appropriate checks using isolated test data.
5. Document behavior, limitations, operational steps, and verification results.
6. Commit and push the reviewed changes to the feature branch and update its pull request.
7. Merge through the project review process, then perform the release checklist before publishing a version.

## Definition of done

- Acceptance criteria are demonstrated by appropriate automated or manual checks.
- Authorization, invalid input, and failure recovery are considered where relevant.
- Frontend changes pass configured lint, tests, type checking, and build checks.
- Backend changes pass applicable tests and formatting checks.
- Database changes include migration and deployment/recovery guidance.
- CI results apply to the exact proposed commit; local and remote branch state is explicit.
- Documentation distinguishes implemented, tested, merged, and released work.
- Remaining issues and unverified areas are recorded rather than described as complete.

## Operational handoff

Local development uses frontend port 5173 and backend port 8000. Vite refuses an occupied frontend port instead of silently changing addresses. Repository Actions require the `gitforge-actions` worker; restart it after reboot and restart workers after code deployments. See the [worker guide](../features/repository-collaboration.md).

Production operation requires coordinated database/Git-storage backups, recovery checks, and supervised workers. See the [deployment runbook](../07-deployment/04-deployment-runbook.md) and [backup procedure](../07-deployment/07-backup-and-restore.md).
