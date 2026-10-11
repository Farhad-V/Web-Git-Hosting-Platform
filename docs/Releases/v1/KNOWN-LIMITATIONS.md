# Version 1 known limitations

## Released v1.0.1

The tagged patch retains the original owner-centric baseline. Collaboration, repository renaming, tags navigation, PDF preview, and refined UI added later to main must not be attributed to this published tag. See [patch notes](v1.0.1.md).

## Merged development

- Private repositories remain owner-only. Organization/team permissions and forks are absent.
- Pull requests are same-repository; owners merge. Approval reviews, protected branches, and required merge checks are absent.
- Actions runs built-in checks only. Arbitrary workflow YAML, scripts, and isolated project CI runners are absent.
- A dedicated Actions worker is required. Local startup remains manual after reboot; production workers need supervision.
- Rename operations require clients to update remote URLs; the old name is not redirected.
- Text/PDF previews enforce limits; PDF behavior depends on browser support and uses a download fallback.
- SSH transport, distributed Git storage, and branch deletion are outside the current implementation.

## Verification and operations

The main JavaScript bundle has an existing size warning. Windows PHP may skip directory-symlink tests. The latest read-only visual pass was blocked by browser tooling. Passing CI does not demonstrate a real-data disaster-recovery restore or a public production deployment.

For follow-up work, use the [product backlog](../../Project-Management/product-backlog.md). Check current candidate evidence in [validation](VALIDATION.md); do not treat this dated limitation list as a substitute for a new review.
