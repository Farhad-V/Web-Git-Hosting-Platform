# Sprint 02: Local startup and CI reliability

**Record type:** reconstructed delivery increment, not a retrospectively approved timeboxed sprint.
**Observed work dates:** 2026-10-11.
**Status:** documented delivered work; follow-ups remain explicit.

## Goal

Restore the local Actions worker, prevent silent frontend port changes, and repair release-note path validation.

## Record boundaries

This record groups evidenced work for navigation. No planned capacity, estimates, team attendance, velocity, burndown, or original sprint commitment is asserted. The original release used M0-M7 milestones. Implementation, merge, documentation publication, and version release are separate states.

- [Backlog and acceptance evidence](sprint-backlog.md)
- [Review and retrospective](review-and-retrospective.md)
- [Sprint register](../SPRINT-REGISTER.md)
- [Product backlog](../../Project-Management/product-backlog.md)

## Evidence

`64cc788` pins Vite to port 5173. `08a431e` corrects the Releases path in CI. The corresponding PR checks passed after the repair. Frontend returned HTTP 200 and backend readiness reported ok during local verification. Process health is time-sensitive and must be checked again when needed.

## Handoff

Reliable local worker supervision is PB-02. No persistent startup service was installed and no release tag was created.
