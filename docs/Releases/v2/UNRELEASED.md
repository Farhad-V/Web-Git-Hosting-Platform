# Unreleased — planned v2.0.0

This document describes current development scope, not a published release. Package metadata uses `2.0.0-dev`; final publication and candidate-specific validation remain pending.

## Licensing policy

Original project code, documentation and diagrams now require prior written permission for use, execution, copying, modification and redistribution under the root [LICENSE](../../../LICENSE). [Third-party licensing notes](../../../THIRD_PARTY_NOTICES.md) preserve upstream terms and earlier grants. Public GitHub viewing/forking follows GitHub's terms. Existing v1 tags retain their historical files and applicable terms.

## Development scope

- Rename repositories while preserving identity/storage; update client clone URLs after rename.
- Navigate branches, tags, commits and files; use bounded PDF previews and download fallback.
- Create branches and select defaults; use same-repository pull requests, discussions, comparison, close/reopen and owner merge with conflict/stale-ref protection and prepared-merge recovery.
- Queue, inspect and cancel built-in Actions checks with a dedicated worker.
- Use readable glass UI surfaces, persistent light/dark preferences, responsive floating navigation and an expandable file sidebar.
- Use fixed local frontend port 5173, the Windows worker starter, organized documentation, repaired typography, and PlantUML source/SVG catalogs.

See [collaboration contracts](../../SDLC/05-implementation/Features/repository-collaboration.md) and [visual system](../../SDLC/05-implementation/Features/visual-system.md).

## Limits and pending acceptance

Owner-centric permissions remain in force. Organization/team roles, forks, protected branches, arbitrary CI runners and workflow YAML are not asserted as implemented. Former repository names have no clone redirect. Linux worker service installation and supervision remain operator responsibilities.

Historical verification checkpoints are retained in the [previous development overview](../v1/UNRELEASED.md). They are not acceptance evidence for a final v2 candidate. Follow the [candidate checklist](RELEASE-CHECKLIST.md) and record fresh results before publication.
