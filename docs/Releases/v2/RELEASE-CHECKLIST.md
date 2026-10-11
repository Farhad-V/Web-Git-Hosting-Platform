# v2.0.0 validation and publication checklist

**Stable metadata finalized; annotated tag requested by owner.** Checked items reflect recorded work. Unchecked manual acceptance and recovery items remain unverified; tag creation does not claim they passed. Automated preparation evidence: all five workflows passed on `deb3447`; final-commit results are recorded in GitHub Actions.

- [x] Choose planned major version v2.0.0 and development identifier `2.0.0-dev`.
- [x] Document permission-only terms, third-party exclusions and earlier-grant boundary.
- [x] Draft changelog, release notes and upgrade guidance.
- [ ] Select exact candidate commit and confirm final scope.
- [ ] Validate backend, frontend, deployment and release-readiness gates on the candidate; record skipped workflows and run missing checks explicitly.
- [ ] Review dependency audits, unresolved defects and permission to distribute included third-party materials.
- [ ] Verify identity, private concealment, PAT clone/fetch/push, browsing and rename/default-branch behavior.
- [ ] Verify PR stale refs, conflicts, owner merge and prepared-merge recovery; verify Actions cancellation/results and worker restart.
- [ ] Record manual light/dark, keyboard/focus, readability and narrow-layout acceptance.
- [ ] Rehearse coordinated database/Git backup and compatible restoration/rollback in isolated fixtures.
- [ ] Review migration compatibility and record host-specific worker supervision requirements.
- [x] Set stable package metadata to `2.0.0` and finalize changelog/date and release notes.
- [ ] Review final-commit automated results in GitHub Actions before tagging.
- [x] Record owner authorization to create the annotated v2.0.0 tag; production deployment is not authorized by this checklist.
- [ ] Create and verify annotated `v2.0.0` tag without modifying existing v1 tags.
- [ ] Publish GitHub Release page separately.
- [ ] Verify remote/local tag and release identities after publication.

Historical manifests: [v1 release identities](../v1/RELEASE-MANIFEST.md). General publication steps: [publication procedure](../v1/PUBLICATION.md); use the new v2 version and notes, not historical tag names.
