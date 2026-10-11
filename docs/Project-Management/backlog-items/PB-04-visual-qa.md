# PB-04: refresh visual and accessibility verification

Status: pending accessible browser tooling. Responsible role: frontend maintainer/reviewer. Schedule: not assigned.

The latest read-only visual pass was blocked by browser tooling. Automated tests and prior visual records do not replace a fresh pass on the intended release candidate.

Acceptance criteria:

- Record candidate commit, browser, viewport, and tested journeys.
- Check landing, sign-in, dashboard, repository navigation, file browsing, PDF fallback, pull requests, and Actions.
- Cover light/dark modes, keyboard focus, narrow layouts, overflow, and reduced-motion/fallback behavior.
- Use isolated test data and preserve user repositories.
- Record confirmed defects and blocked scenarios with evidence.

Dependencies: accessible supported browser tooling and an isolated demo environment. See [visual system](../../SDLC/Implementation/Features/visual-system.md).
