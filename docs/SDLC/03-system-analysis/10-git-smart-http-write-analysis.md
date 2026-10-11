# Git Smart HTTP Write Analysis

## Scope

GitForge V1.0.0 supports owner-authenticated push over Git Smart HTTP. Organization/team collaboration, SSH transport, repository deletion, and distributed storage are outside this release.

## Write Flow

1. A Git client requests `/<username>/<repository>.git/info/refs?service=git-receive-pack`.
2. The controller resolves the logical namespace through user and repository metadata. The internal repository ID determines the physical path.
3. HTTP Basic credentials contain the owner's username and a personal access token. The token service validates its format, persisted hash, and revocation state; the credential authenticator verifies the username matches the token owner.
4. Only the repository owner may advertise receive-pack or push, for both public and private repositories.
5. The client submits `git-receive-pack` with the expected Git media type. The transport service bounds request size, verifies managed bare storage, and invokes the configured Git executable through structured arguments and binary-preserving standard input/output.
6. Git validates and applies the pack and reference updates. The application returns Git's protocol response with non-cacheable headers. An HTTP success status does not replace the Git protocol's per-reference result.

## State and Storage

Repository metadata holds ownership and visibility; commits, references, and the default branch remain Git-native state. Physical repositories use `<repository-root>/repositories/<repository-id>.git`. Existing orphan storage must never be adopted or removed by a failed creation attempt.

## Failure Boundaries

- Missing, invalid, or revoked credentials return an authentication challenge.
- An authenticated non-owner is forbidden from writing.
- Unsupported services, incorrect media types, oversized requests, unavailable/corrupt storage, and timed-out processes produce controlled failures.
- Push success must be determined from the Git protocol response; clients can receive rejected reference updates in a successful HTTP response.
- Private read concealment in the web API is distinct from the Git transport's authentication challenge behavior.

## Recovery and Concurrency

Git controls repository reference updates. Backups must coordinate PostgreSQL metadata with Git storage: block new API/Git requests using file-based maintenance mode, drain in-flight pushes and metadata mutations, pause other writers, and keep them paused through both snapshots. Restoring only one component can leave missing or orphan repositories.

## Implementation and Verification References

- `backend/routes/git.php`
- `backend/app/Http/Controllers/GitSmartHttpController.php`
- `backend/app/Services/Git/GitCredentialAuthenticator.php`
- `backend/app/Services/Git/GitSmartHttpService.php`
- `backend/app/Services/Git/GitCommandRunner.php`
- `backend/tests/Feature/Git/GitSmartHttpWriteEndToEndTest.php`
- `backend/tests/Feature/Api/V1/RepositoryTest.php`
- `docs/SDLC/06-testing/12-git-smart-http-write-testing.md`
- `docs/SDLC/07-deployment/07-backup-and-restore.md`
