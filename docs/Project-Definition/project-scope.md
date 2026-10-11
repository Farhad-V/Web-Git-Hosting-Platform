# Project scope

## In the released baseline

Registration, session authentication, owner-centric public/private authorization, repository metadata, managed bare Git storage, personal access tokens, Git Smart HTTP clone/fetch/push, branch/commit/tree/text browsing, health reporting, CI, and deployment/recovery guidance.

## Merged into main, awaiting a new release

Repository renaming; Code, Branches, Commits, Tags, Pull requests, Actions, and Settings navigation; expandable file browsing; supported PDF preview/download; branch creation and default selection; same-repository pull requests, discussion, comparison, and owner merge; built-in repository checks; refined light/dark glass UI.

## Explicit exclusions

- Organization/team permissions and fork or cross-repository collaboration.
- Approval reviews, protected branches, branch deletion, and required merge checks.
- Arbitrary CI scripts, dependency installation, and workflow YAML execution.
- SSH transport and distributed Git storage.
- Public hosting as a portfolio acceptance requirement.
- Repository deletion or archival as an implemented capability solely because it appears in an original plan.

Actions checks verify commit existence, tree readability, and object connectivity. Private repositories remain owner-only. Renaming preserves storage but requires existing Git clients to update remote URLs. PDF preview is bounded and browser-dependent, with download fallback.

See [scope diagram](scope-boundary-diagram.md), [release status](../Releases/v1/README.md), and [feature contracts](../SDLC/Implementation/Features/repository-collaboration.md).
