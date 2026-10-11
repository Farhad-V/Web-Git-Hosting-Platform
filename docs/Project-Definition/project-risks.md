# Project risks

This register records qualitative risks and controls; it does not claim measured likelihoods or completed mitigation work beyond documented implementation.

| ID | Risk | Existing control or follow-up | Responsible role |
| --- | --- | --- | --- |
| R-01 | Unauthorized repository access | Server-side policies, concealment, CSRF/token checks, security regression tests | Maintainer/security reviewer |
| R-02 | Git storage and metadata diverge during recovery | Coordinated backups and verified restore procedures | Operator |
| R-03 | Concurrent branch changes invalidate reviewed merges | Reviewed hashes, atomic ref checks, durable merge preparation/reconciliation | Backend maintainer |
| R-04 | Stopped worker leaves Actions pending | Dedicated worker starter; supervise in production and restart after reboot | Operator |
| R-05 | Large transfers exhaust memory or process time | Bounded upload streams, configured limits, subprocess timeouts | Backend maintainer |
| R-06 | Folder moves break Linux CI or links | Exact-case path validation and release/documentation checks | Documentation maintainer |
| R-07 | Dependency defects or vulnerabilities recur | Lockfiles, dependency audits, maintenance update policy | Maintainer |
| R-08 | Glass effects reduce readability or browser compatibility | Shared tokens, fallback styles, keyboard/responsive/visual QA | Frontend maintainer |
| R-09 | Planned or merged features are mistaken for released capabilities | Scope exclusions and explicit release status | Project owner |
| R-10 | Historical QA evidence becomes stale | Record commit identity and rerun affected checks after changes | Reviewer |

Open operational and delivery items belong in the [backlog](../Project-Management/BACKLOG.md). Confirmed defects follow the [incident procedure](../SDLC/08-maintenance/05-incident-and-defect-handling.md). This register assigns roles, not unconfirmed individuals or deadlines.
