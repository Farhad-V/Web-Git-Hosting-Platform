# Use cases

| ID | Actor and goal | Preconditions and result |
| --- | --- | --- |
| UC-R01 | User registers/signs in | Valid input; authenticated session or controlled validation/auth failure |
| UC-R02 | Owner creates/manages a repository | Signed in; valid metadata; controlled managed storage and visibility |
| UC-R03 | Owner issues/revokes Git token | Authenticated account; secret shown once; revoked token no longer authenticates |
| UC-R04 | Git client clones/fetches/pushes | Repository permission and valid credentials where required; Git-compatible response |
| UC-R05 | Reader browses code/history | Repository readable; valid branch/path; bounded content or controlled fallback |
| UC-R06 | Owner manages branch/default | Existing source/target; no silent replacement of an existing branch |
| UC-R07 | Permitted user proposes/discusses a request | Same repository and distinct valid branches; scoped request/comments |
| UC-R08 | Owner merges a request | Open request and reviewed hashes; reject stale/conflicting state and reconcile interrupted prepared merge |
| UC-R09 | Owner runs/cancels built-in checks | Committed branch; permitted queue operation; eventual recorded result under active worker |
| UC-R10 | Browser user changes appearance | Theme choice persists and applies consistently |

Unauthorized private reads are concealed; mutations reject callers without the appropriate role. Conflicting merges require local resolution. A stopped worker leaves checks queued; this is an operational dependency, not successful execution.

Detailed historical cases: [specifications](10-detailed-use-case-specifications.md). Current collaboration contracts: [feature guide](../05-implementation/Features/repository-collaboration.md). See [use-case diagram](use-case-diagram.md).
