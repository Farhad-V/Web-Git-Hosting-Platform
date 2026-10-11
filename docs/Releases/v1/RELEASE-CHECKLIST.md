# Version 1 release checklist

Use this checklist for a future candidate; unchecked items are deliberately pending.

- [ ] Agree scope and choose a Semantic Versioning number.
- [ ] Confirm the release commit and reviewed pull request.
- [ ] Pass Backend CI, Frontend CI, Deployment Validation, and Release Readiness on that candidate.
- [ ] Review dependency audit results and unresolved defects.
- [ ] Check migrations, repository authorization, Git transport, and merge recovery where affected.
- [ ] Verify core UI journeys in light/dark modes and a narrow viewport.
- [ ] Confirm Actions worker supervision and code-restart procedure.
- [ ] Rehearse coordinated backup, restore, and rollback as appropriate to the change.
- [ ] Update changelog, release notes, setup instructions, and known limitations.
- [ ] Merge approved work and verify the intended release branch.
- [ ] Create the version tag and publish release notes through the authorized release process.
- [ ] Verify local/remote commit and tag identities after publication.

Historical evidence: [v1.0.0 checklist](History/08-v1.0.0-release-checklist.md). Upgrade specifics: [v1.0.1 patch notes](v1.0.1.md).
