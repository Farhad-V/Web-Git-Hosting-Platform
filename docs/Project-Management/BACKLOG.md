# Backlog and risks

Recorded on **2026-10-11**. This is a planning list, not a promise of scheduled delivery.

| Item | Status | Completion evidence |
| --- | --- | --- |
| Collaboration and refined glass UI | Merged into main; release pending | PR #27, feature documentation, relevant CI |
| Documentation navigation folders | Added in this documentation increment | Link and structure validation |
| Local frontend port stability | Merged into main | Duplicate launch refuses occupied port 5173 |
| Local Actions worker | Restarted during QA follow-up | Matching queue worker process verified |
| Persistent worker supervision | Production guidance exists; local startup remains manual after reboot | Supervised service or explicitly approved startup integration |
| Frontend bundle optimization | Improvement opportunity | Main bundle falls below agreed budget without regressions |
| Windows directory-symlink coverage | Environment limitation | Symlink test executes in a supporting environment |
| Fresh visual QA | Latest read-only pass blocked by browser tooling | Accessible browser pass on the exact candidate |
| Advanced GitHub-style collaboration | Outside current implementation | Separate requirements and authorized delivery plan |

## Material risks

- A stopped worker leaves Actions runs queued. Keep worker startup visible in the local handoff and supervise it in production.
- Database metadata and bare Git storage must be backed up together. A backup of only one component is insufficient for recovery.
- Git ref updates and database workflow state cross persistence boundaries. Keep concurrency and recovery regression checks when changing merge behavior.
- PDF preview depends on browser support and configured size limits; download remains the fallback.
- Passing tests does not prove every visual state or all combinations of user data are correct. Record the actual scenarios and platform limitations.

Use the [incident procedure](../SDLC/Maintenance/05-incident-and-defect-handling.md) for confirmed defects and the [release checklist](../Releases/v1/RELEASE-CHECKLIST.md) before publication.
