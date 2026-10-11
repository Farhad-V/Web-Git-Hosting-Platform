# Release publication procedure

Documentation completion and a merge into main do not publish a version. Publication requires an agreed version, approved candidate, and explicit authorization.

1. Select the Semantic Versioning number and exact candidate SHA.
2. Complete the [release checklist](RELEASE-CHECKLIST.md), candidate [validation](VALIDATION.md), and relevant operational review.
3. Reconcile package/version metadata, root changelog, version-specific notes, upgrade requirements, and known limitations.
4. Confirm the candidate is on the intended release branch and local/remote identities match.
5. Review automation for the chosen version. The existing v1.0.0 publication workflow is specific to that release and must not be assumed to publish arbitrary future versions.
6. With publication authorization, create the new version tag at the approved commit and publish its corresponding notes.
7. Verify the remote tag's commit and GitHub release metadata; record their identities in the release manifest.

Never move an existing historical tag to include newer work. A documentation-only commit on main does not update the contents of v1.0.0 or v1.0.1. If the candidate changes after validation, rerun affected checks before publication.

For a new notes document, use the [template](RELEASE-NOTES-TEMPLATE.md). For chronology, update the [root changelog](../../../CHANGELOG.md).
