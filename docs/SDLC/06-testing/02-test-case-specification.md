# Test Case Specification

| Field | Value |
|---|---|
| Project | GitForge — Web Git Hosting Platform |
| Version | V1.0.0 |
| SDLC phase | 6 — Testing |
| Milestone | M5 — Testing |
| Work package | WP-02 — Test Case Specification |
| Document | Test Case Specification |
| Status | Approved |
| Date | 2026-08-21 |
| Branch | `testing/m5` |

---
## 1. Purpose

This document defines the formal test-case specification for GitForge V1.0.0.

The purpose of WP-02 is to transform approved requirements, use cases, user stories, authorization rules, analysis decisions, design decisions, implementation behavior, and identified risk areas into executable test cases.

These test cases provide the formal basis for the remaining M5 testing work packages.

---
## 2. Objectives

The objectives of this specification are to:

- define reproducible test cases;
- establish consistent test identifiers;
- define explicit preconditions;
- define test procedures;
- define expected results;
- associate tests with approved requirement groups;
- cover positive and negative behavior;
- cover security-sensitive behavior;
- cover frontend, backend, database, filesystem, and Git boundaries;
- support later test-result recording;
- support requirements-to-test traceability;
- provide the basis for regression and acceptance testing.

---
## 3. Test Basis

The formal test basis includes approved artifacts from:

```text
docs/Project-Definition/
docs/SDLC/02-requirements-engineering/
docs/SDLC/03-system-analysis/
docs/SDLC/04-system-design/
docs/SDLC/05-implementation/
docs/SDLC/06-testing/01-testing-plan.md
```

Primary requirements groups include:

| Capability | Requirement Basis |
|---|---|
| Identity and authentication | FR-001 – FR-012 |
| Repository creation | FR-013 – FR-020 |
| Repository retrieval and visibility | FR-021 – FR-028 |
| Repository browser | FR-029 – FR-035 |
| Git Smart HTTP read | FR-036 – FR-042 |
| Git Smart HTTP write | FR-043 – FR-049 |
| Personal Access Tokens | FR-050 – FR-060 |
| Core authorization | FR-061 – FR-066 |
| Repository storage | FR-067 – FR-070 |
| Git process execution | FR-071 – FR-074 |
| Validation | FR-075 – FR-078 |
| Error handling | FR-079 – FR-083 |
| Repository lifecycle | FR-084 – FR-088 |
| Web interfaces | FR-089 – FR-095 |
| Logging and observability | FR-096 – FR-098 |
| Security | NFR-001 – NFR-018 |
| Authentication/session quality | NFR-019 – NFR-022 |
| Performance | NFR-023 – NFR-027 |
| Reliability and integrity | NFR-028 – NFR-038 |
| Availability and recovery | NFR-039 – NFR-041 and applicable later NFRs |

Detailed requirement-to-test mapping will be completed in WP-19 — Test Traceability.

---
## 4. Test Case Identifier Convention

Test cases use:

```text
TC-<AREA>-<NNN>
```

Examples:

```text
TC-AUTH-001
TC-REPO-003
TC-AUTHZ-004
TC-GITR-002
TC-GITW-005
TC-SEC-009
```

Area codes:

| Code | Area |
|---|---|
| AUTH | Authentication |
| REPO | Repository metadata |
| AUTHZ | Authorization |
| STORE | Repository storage |
| GEXEC | Git process execution |
| BROWSE | Repository browser |
| GITR | Git Smart HTTP read |
| PAT | Personal Access Token |
| GITW | Git Smart HTTP write |
| UI | Web UI |
| DB | Database |
| SEC | Security |
| OBS | Observability |
| FAIL | Failure and exception behavior |
| E2E | System/end-to-end |
| REG | Regression |
| ACC | Acceptance |

---
## 5. Test Result States

Each test execution must use one of:

```text
NOT EXECUTED
PASS
FAIL
BLOCKED
```

A test must never be marked `PASS` unless its documented expected result has been observed.

---
## 6. Test Priority

Priority values:

| Priority | Meaning |
|---|---|
| P1 | Security-critical or release-critical |
| P2 | Core required behavior |
| P3 | Important secondary behavior |
| P4 | Non-critical verification |

All P1 and P2 cases must be executed before M5 closure.

---
## 7. Test Types

This specification uses the following test types:

- Functional
- Integration
- API
- Database
- Security
- Negative
- System
- End-to-End
- Regression
- Acceptance
- Manual
- Automated

A test may belong to multiple types.

---
## 8. Authentication Test Cases

Requirement basis:

```text
FR-001 – FR-012
NFR-019 – NFR-022
UC-001 – UC-003
```

---
### TC-AUTH-001 — Register a Valid User

| Field | Value |
|---|---|
| Priority | P2 |
| Type | Functional / API / Integration |
| Requirement basis | FR-001 – FR-004 |
| Preconditions | User is not authenticated; email and username do not already exist |
| Action | Submit valid registration data |
| Expected result | User account is created successfully; password is not stored as plaintext; authenticated behavior follows approved implementation |

---
### TC-AUTH-002 — Reject Duplicate Email Registration

| Field | Value |
|---|---|
| Priority | P2 |
| Type | Negative / API |
| Requirement basis | FR-001 – FR-004, FR-075 – FR-078 |
| Preconditions | Existing account uses the submitted email |
| Action | Attempt registration with the same email |
| Expected result | Registration is rejected with validation behavior; no duplicate user is created |

---
### TC-AUTH-003 — Reject Duplicate Username Registration

| Field | Value |
|---|---|
| Priority | P2 |
| Type | Negative / API |
| Requirement basis | FR-001 – FR-004, FR-075 – FR-078 |
| Preconditions | Existing account uses the submitted username |
| Action | Attempt registration with the same username |
| Expected result | Registration is rejected; existing account remains unchanged |

---
### TC-AUTH-004 — Login with Valid Credentials

| Field | Value |
|---|---|
| Priority | P1 |
| Type | Functional / Security |
| Requirement basis | FR-005 – FR-009 |
| Preconditions | Valid user exists |
| Action | Submit valid login credentials |
| Expected result | Authentication succeeds and authenticated session state is established correctly |

---
### TC-AUTH-005 — Reject Invalid Password

| Field | Value |
|---|---|
| Priority | P1 |
| Type | Security / Negative |
| Requirement basis | FR-005 – FR-009, NFR-019 – NFR-022 |
| Preconditions | Valid user exists |
| Action | Submit correct identity with incorrect password |
| Expected result | Authentication fails; authenticated session is not created |

---
### TC-AUTH-006 — Reject Unknown User Login

| Field | Value |
|---|---|
| Priority | P1 |
| Type | Security / Negative |
| Requirement basis | FR-005 – FR-009 |
| Preconditions | Submitted identity does not exist |
| Action | Attempt login |
| Expected result | Login fails without exposing sensitive account information |

---
### TC-AUTH-007 — Logout Authenticated User

| Field | Value |
|---|---|
| Priority | P2 |
| Type | Functional / Integration |
| Requirement basis | FR-005 – FR-012 |
| Preconditions | User is authenticated |
| Action | Execute logout |
| Expected result | Current authenticated session becomes invalid and protected resources require authentication again |

---
### TC-AUTH-008 — Reject Protected Request Without Authentication

| Field | Value |
|---|---|
| Priority | P1 |
| Type | Security / API |
| Requirement basis | FR-010 – FR-012, FR-061 – FR-066 |
| Preconditions | No authenticated session exists |
| Action | Request an authenticated-only API operation |
| Expected result | Request is rejected according to approved authentication behavior |

---
### TC-AUTH-009 — CSRF Protection for Session-Based State Change

| Field | Value |
|---|---|
| Priority | P1 |
| Type | Security |
| Requirement basis | NFR-001 – NFR-018, NFR-019 – NFR-022 |
| Preconditions | Session-authenticated request context exists |
| Action | Submit applicable state-changing request without valid CSRF protection |
| Expected result | Request is rejected and state is unchanged |

---
### TC-AUTH-010 — Authenticated Session Persists Across Valid Requests

| Field | Value |
|---|---|
| Priority | P2 |
| Type | Integration |
| Requirement basis | FR-005 – FR-012 |
| Preconditions | Valid login has completed |
| Action | Perform multiple authenticated requests |
| Expected result | Session identity remains consistent until logout or expiration |

---
## 9. Repository Metadata Test Cases

Requirement basis:

```text
FR-013 – FR-028
UC-004 – UC-006
```

---
### TC-REPO-001 — Create Valid Public Repository

| Field | Value |
|---|---|
| Priority | P2 |
| Type | Functional / API / Integration |
| Requirement basis | FR-013 – FR-020 |
| Preconditions | User is authenticated |
| Action | Create repository with valid name and public visibility |
| Expected result | Metadata is created, ownership is assigned to authenticated user, and corresponding repository storage is initialized |

---
### TC-REPO-002 — Create Valid Private Repository

| Field | Value |
|---|---|
| Priority | P2 |
| Type | Functional / Integration |
| Requirement basis | FR-013 – FR-020 |
| Preconditions | User is authenticated |
| Action | Create repository with private visibility |
| Expected result | Private repository is created and visible only according to authorization rules |

---
### TC-REPO-003 — Reject Repository Creation Without Authentication

| Field | Value |
|---|---|
| Priority | P1 |
| Type | Security / Negative |
| Requirement basis | FR-013 – FR-020, AR-010 – AR-012 |
| Preconditions | User is unauthenticated |
| Action | Submit repository creation request |
| Expected result | Request is rejected; no metadata and no repository storage are created |

---
### TC-REPO-004 — Reject Invalid Repository Name

| Field | Value |
|---|---|
| Priority | P2 |
| Type | Validation / Negative |
| Requirement basis | FR-013 – FR-020, FR-075 – FR-078 |
| Preconditions | User is authenticated |
| Action | Submit repository name violating approved validation rules |
| Expected result | Request is rejected and no repository is created |

---
### TC-REPO-005 — Reject Duplicate Owner/Repository Name

| Field | Value |
|---|---|
| Priority | P2 |
| Type | Database / Negative |
| Requirement basis | FR-013 – FR-020 |
| Preconditions | Owner already has repository with requested name |
| Action | Attempt to create another repository using the same owner/name combination |
| Expected result | Duplicate creation is rejected; existing repository remains unchanged |

---
### TC-REPO-006 — Retrieve Public Repository Metadata

| Field | Value |
|---|---|
| Priority | P2 |
| Type | Functional / API |
| Requirement basis | FR-021 – FR-028 |
| Preconditions | Public repository exists |
| Action | Request repository metadata |
| Expected result | Repository metadata is returned according to approved API contract |

---
### TC-REPO-007 — Retrieve Owned Private Repository

| Field | Value |
|---|---|
| Priority | P1 |
| Type | Functional / Authorization |
| Requirement basis | FR-021 – FR-028 |
| Preconditions | Private repository exists; requester is owner |
| Action | Request metadata |
| Expected result | Repository metadata is returned |

---
### TC-REPO-008 — Conceal Private Repository from Unauthorized User

| Field | Value |
|---|---|
| Priority | P1 |
| Type | Security / Authorization |
| Requirement basis | FR-025 – FR-028, FR-061 – FR-066 |
| Preconditions | Private repository exists; requester is not owner |
| Action | Request repository metadata |
| Expected result | Request is rejected using concealment behavior defined by the system; private metadata is not disclosed |

---
### TC-REPO-009 — Update Repository as Owner

| Field | Value |
|---|---|
| Priority | P2 |
| Type | Functional / Authorization |
| Requirement basis | Repository lifecycle and metadata requirements |
| Preconditions | Repository exists; requester is owner |
| Action | Submit valid supported metadata change |
| Expected result | Repository metadata is updated and persistence remains consistent |

---
### TC-REPO-010 — Reject Repository Update by Non-Owner

| Field | Value |
|---|---|
| Priority | P1 |
| Type | Security / Authorization |
| Requirement basis | FR-061 – FR-066 |
| Preconditions | Repository exists; requester is authenticated non-owner |
| Action | Attempt update |
| Expected result | Update is rejected and repository remains unchanged |

---
## 10. Authorization Test Cases

Requirement basis:

```text
FR-061 – FR-066
AR-001 – AR-060 as applicable
NFR-001 – NFR-018
```

---
### TC-AUTHZ-001 — Owner Can Access Own Private Repository

| Field | Value |
|---|---|
| Priority | P1 |
| Type | Authorization |
| Preconditions | Private repository exists |
| Action | Owner requests repository |
| Expected result | Access succeeds |

---
### TC-AUTHZ-002 — Non-Owner Cannot Read Private Repository

| Field | Value |
|---|---|
| Priority | P1 |
| Type | Security |
| Preconditions | Private repository exists; requester is another authenticated user |
| Action | Request private repository |
| Expected result | Access is denied/concealed without private-data disclosure |

---
### TC-AUTHZ-003 — Anonymous User Cannot Read Private Repository

| Field | Value |
|---|---|
| Priority | P1 |
| Type | Security |
| Preconditions | Private repository exists |
| Action | Anonymous request attempts private access |
| Expected result | Access is denied/concealed |

---
### TC-AUTHZ-004 — Anonymous User Can Read Public Repository Where Allowed

| Field | Value |
|---|---|
| Priority | P2 |
| Type | Functional / Authorization |
| Preconditions | Public repository exists |
| Action | Anonymous requester performs supported public read |
| Expected result | Read succeeds |

---
### TC-AUTHZ-005 — Non-Owner Cannot Modify Repository Metadata

| Field | Value |
|---|---|
| Priority | P1 |
| Type | Security |
| Preconditions | Repository belongs to another user |
| Action | Attempt metadata mutation |
| Expected result | Mutation is rejected |

---
### TC-AUTHZ-006 — Non-Owner Cannot Push to Repository

| Field | Value |
|---|---|
| Priority | P1 |
| Type | Security / Git |
| Preconditions | Valid credentials belong to non-owner |
| Action | Attempt Git push |
| Expected result | Push is rejected and refs remain unchanged |

---
### TC-AUTHZ-007 — Public Repository Does Not Grant Write Permission

| Field | Value |
|---|---|
| Priority | P1 |
| Type | Security / Git |
| Preconditions | Public repository exists |
| Action | Anonymous or unauthorized requester attempts a Git write operation |
| Expected result | Write operation is rejected; public visibility does not grant write permission |

---
### TC-AUTHZ-008 — Authorized Owner PAT Can Perform Push

| Field | Value |
|---|---|
| Priority | P1 |
| Type | Authorization / Integration |
| Preconditions | Repository exists; valid active PAT belongs to repository owner |
| Action | Owner performs a valid Git push using the PAT |
| Expected result | Push is authorized and succeeds |

---
## 11. Repository Storage Test Cases

Requirement basis:

```text
FR-067 – FR-070
NFR-028 – NFR-038
```

---
### TC-STORE-001 — Repository Creation Initializes Bare Repository Storage

| Field | Value |
|---|---|
| Priority | P1 |
| Type | Integration |
| Preconditions | Valid repository creation request |
| Action | Create repository |
| Expected result | Corresponding bare Git repository exists in approved storage root |

---
### TC-STORE-002 — Repository Path Is Derived Safely

| Field | Value |
|---|---|
| Priority | P1 |
| Type | Security |
| Preconditions | Valid owner and repository identifiers |
| Action | Resolve repository storage path |
| Expected result | Canonical path remains inside configured repository root |

---
### TC-STORE-003 — Reject Path Traversal Attempt

| Field | Value |
|---|---|
| Priority | P1 |
| Type | Security / Negative |
| Action | Supply traversal-oriented repository/path input |
| Expected result | Input is rejected; storage root cannot be escaped |

---
### TC-STORE-004 — Reject Absolute Path Injection

| Field | Value |
|---|---|
| Priority | P1 |
| Type | Security |
| Action | Attempt to force resolution to an arbitrary absolute filesystem path |
| Expected result | Operation is rejected |

---
### TC-STORE-005 — Detect Missing Repository Storage

| Field | Value |
|---|---|
| Priority | P2 |
| Type | Failure / Integrity |
| Preconditions | Metadata exists but physical repository is absent |
| Action | Perform operation requiring repository storage |
| Expected result | Controlled error occurs; arbitrary repository creation/access does not occur |

---
### TC-STORE-006 — Handle Orphan Repository Storage Safely

| Field | Value |
|---|---|
| Priority | P1 |
| Type | Security / Integrity |
| Preconditions | Physical repository exists without valid expected metadata relationship |
| Action | Attempt access through application |
| Expected result | Orphan storage is not exposed as an authorized repository |

---
### TC-STORE-007 — Symlink Escape Protection

| Field | Value |
|---|---|
| Priority | P1 |
| Type | Security |
| Preconditions | Environment supports required symlink behavior |
| Action | Construct symlink-based path escaping configured storage root |
| Expected result | Escape is rejected |
| Environment note | If Windows environment cannot create required directory symlink, record BLOCKED/environment limitation rather than PASS |

---
## 12. Git Process Execution Test Cases

Requirement basis:

```text
FR-071 – FR-074
NFR-001 – NFR-018
```

---
### TC-GEXEC-001 — Execute Approved Git Operation

| Field | Value |
|---|---|
| Priority | P1 |
| Type | Integration |
| Action | Invoke approved Git operation through controlled runner |
| Expected result | Git executable runs with expected arguments and controlled repository context |

---
### TC-GEXEC-002 — Reject Unsupported Git Subcommand

| Field | Value |
|---|---|
| Priority | P1 |
| Type | Security |
| Action | Attempt unsupported/unapproved subcommand |
| Expected result | Command is rejected before unsafe execution |

---
### TC-GEXEC-003 — Reject Shell Metacharacter Injection

| Field | Value |
|---|---|
| Priority | P1 |
| Type | Security |
| Action | Supply input containing shell command separators/metacharacters |
| Expected result | No secondary command executes; malicious input is rejected or safely treated as data |

---
### TC-GEXEC-004 — Repository Argument Cannot Escape Storage Root

| Field | Value |
|---|---|
| Priority | P1 |
| Type | Security |
| Action | Provide manipulated repository path |
| Expected result | Git process cannot operate outside approved storage root |

---
### TC-GEXEC-005 — Capture Non-Zero Git Exit Status

| Field | Value |
|---|---|
| Priority | P2 |
| Type | Failure |
| Action | Execute Git operation expected to fail |
| Expected result | Failure is detected and represented through controlled application behavior |

---
### TC-GEXEC-006 — Capture Git Standard Error Safely

| Field | Value |
|---|---|
| Priority | P2 |
| Type | Failure / Observability |
| Action | Cause Git error output |
| Expected result | Error is available for controlled diagnostics without unsafe secret exposure |

---
### TC-GEXEC-007 — Enforce Git Process Timeout

| Field | Value |
|---|---|
| Priority | P1 |
| Type | Reliability |
| Action | Execute or simulate operation exceeding configured timeout |
| Expected result | Process is terminated/handled according to approved design; application does not hang indefinitely |

---
## 13. Repository Browser Test Cases

Requirement basis:

```text
FR-029 – FR-035
UC-009 – UC-014
```

---
### TC-BROWSE-001 — Browse Repository Root Tree

| Field | Value |
|---|---|
| Priority | P2 |
| Type | Functional |
| Preconditions | Repository contains commit and tree |
| Action | Request repository root at valid ref |
| Expected result | Root entries are returned correctly |

---
### TC-BROWSE-002 — Browse Nested Directory

| Field | Value |
|---|---|
| Priority | P2 |
| Type | Functional |
| Action | Request valid nested path |
| Expected result | Correct nested tree contents are returned |

---
### TC-BROWSE-003 — Read Text File Content

| Field | Value |
|---|---|
| Priority | P2 |
| Type | Functional |
| Action | Request valid text blob |
| Expected result | Correct file content and applicable metadata are returned |

---
### TC-BROWSE-004 — Reject Missing Path

| Field | Value |
|---|---|
| Priority | P2 |
| Type | Negative |
| Action | Browse nonexistent path |
| Expected result | Controlled not-found behavior is returned |

---
### TC-BROWSE-005 — Reject Invalid Ref

| Field | Value |
|---|---|
| Priority | P2 |
| Type | Negative |
| Action | Request nonexistent or invalid ref |
| Expected result | Controlled error/not-found response is returned |

---
### TC-BROWSE-006 — List Repository Commits

| Field | Value |
|---|---|
| Priority | P2 |
| Type | Functional |
| Preconditions | Repository contains multiple commits |
| Action | Request commit history |
| Expected result | Commit list is returned in expected format/order |

---
### TC-BROWSE-007 — Retrieve Commit Detail by Full Object ID

| Field | Value |
|---|---|
| Priority | P2 |
| Type | Functional |
| Requirement basis | FR-032 / UC-012 |
| Preconditions | Known valid commit exists |
| Action | Request commit using supported full commit identifier |
| Expected result | Correct commit details are returned |

---
### TC-BROWSE-008 — Reject Non-Commit Object as Commit Detail

| Field | Value |
|---|---|
| Priority | P2 |
| Type | Validation / Negative |
| Preconditions | Known blob/tree object identifier exists |
| Action | Use non-commit object ID in commit-detail request |
| Expected result | Request is rejected as invalid object type |

---
### TC-BROWSE-009 — Enforce Private Repository Browser Authorization

| Field | Value |
|---|---|
| Priority | P1 |
| Type | Security |
| Preconditions | Private repository exists |
| Action | Unauthorized requester attempts tree/blob/commit browsing |
| Expected result | Browser information is not disclosed |

---
## 14. Git Smart HTTP Read Test Cases

Requirement basis:

```text
FR-036 – FR-042
AR-045 – AR-049
NFR-026 – NFR-027
```

---
### TC-GITR-001 — Public Repository Info/Refs Advertisement

| Field | Value |
|---|---|
| Priority | P1 |
| Type | Git / Integration |
| Preconditions | Public repository exists |
| Action | Git client requests upload-pack advertisement |
| Expected result | Valid Smart HTTP advertisement is returned |

---
### TC-GITR-002 — Clone Public Repository

| Field | Value |
|---|---|
| Priority | P1 |
| Type | End-to-End |
| Preconditions | Public repository contains commit |
| Action | Run real `git clone` over supported HTTP endpoint |
| Expected result | Clone succeeds and checked-out content matches source |

---
### TC-GITR-003 — Fetch New Commit from Public Repository

| Field | Value |
|---|---|
| Priority | P1 |
| Type | End-to-End |
| Preconditions | Existing clone; source repository receives additional commit |
| Action | Run `git fetch` |
| Expected result | New refs/objects are transferred successfully |

---
### TC-GITR-004 — Clone Private Repository with Valid Owner PAT

| Field | Value |
|---|---|
| Priority | P1 |
| Type | Security / End-to-End |
| Preconditions | Private repository exists; owner has a valid active PAT |
| Action | Clone using supported credential format |
| Expected result | Clone succeeds |

---
### TC-GITR-005 — Reject Private Clone Without Credentials

| Field | Value |
|---|---|
| Priority | P1 |
| Type | Security |
| Preconditions | Private repository exists |
| Action | Attempt clone with no credentials |
| Expected result | Clone fails without repository disclosure beyond approved behavior |

---
### TC-GITR-006 — Reject Private Clone with Invalid PAT

| Field | Value |
|---|---|
| Priority | P1 |
| Type | Security |
| Action | Attempt clone using invalid token |
| Expected result | Authentication fails and repository content is not transferred |

---
### TC-GITR-007 — Reject Private Clone with Revoked PAT

| Field | Value |
|---|---|
| Priority | P1 |
| Type | Security |
| Preconditions | Token was valid and has been revoked |
| Action | Attempt clone |
| Expected result | Authentication fails |

---
### TC-GITR-008 — Preserve Smart HTTP Protocol Response Integrity

| Field | Value |
|---|---|
| Priority | P1 |
| Type | Integration |
| Action | Perform supported read negotiation with real Git client |
| Expected result | Git protocol response is accepted by the client and required objects transfer correctly |

---
## 15. Personal Access Token Test Cases

Requirement basis:

```text
FR-050 – FR-060
AR-035 – AR-044
```

V1.0.0 does not implement separate PAT read/write scopes.

A valid active PAT establishes the identity of its owning user. Repository authorization is evaluated separately according to repository visibility and ownership rules.

---
### TC-PAT-001 — Create PAT as Authenticated User

| Field | Value |
|---|---|
| Priority | P1 |
| Type | Functional / Security |
| Preconditions | User authenticated |
| Action | Create PAT with a valid name |
| Expected result | Token record is created and raw token is returned only at creation according to approved behavior |

---
### TC-PAT-002 — Reject PAT Creation Without Authentication

| Field | Value |
|---|---|
| Priority | P1 |
| Type | Security |
| Action | Unauthenticated request attempts PAT creation |
| Expected result | Request is rejected |

---
### TC-PAT-003 — Raw PAT Is Not Persisted as Plaintext

| Field | Value |
|---|---|
| Priority | P1 |
| Type | Database / Security |
| Preconditions | PAT has been created |
| Action | Inspect stored token representation |
| Expected result | Recoverable raw token value is not stored in plaintext |

---
### TC-PAT-004 — List Current User PATs

| Field | Value |
|---|---|
| Priority | P2 |
| Type | Functional |
| Preconditions | User owns one or more PATs |
| Action | Request PAT list |
| Expected result | Only current user's token metadata is returned; raw token secrets are not returned |

---
### TC-PAT-005 — User Cannot List Another User's PATs

| Field | Value |
|---|---|
| Priority | P1 |
| Type | Security |
| Preconditions | Two users exist |
| Action | User attempts to retrieve another user's token information |
| Expected result | Unauthorized token metadata is not disclosed |

---
### TC-PAT-006 — Revoke Owned PAT

| Field | Value |
|---|---|
| Priority | P1 |
| Type | Functional / Security |
| Preconditions | Valid token exists |
| Action | Owner revokes token |
| Expected result | Token can no longer authenticate subsequent Git requests |

---
### TC-PAT-007 — Reject Malformed Token

| Field | Value |
|---|---|
| Priority | P1 |
| Type | Security / Negative |
| Action | Use malformed credential |
| Expected result | Authentication fails safely |

---
### TC-PAT-008 — Valid Active PAT Resolves Correct Owner Identity

| Field | Value |
|---|---|
| Priority | P1 |
| Type | Authentication / Integration |
| Preconditions | Valid active PAT belongs to a known user |
| Action | Authenticate a protected Git request using the PAT |
| Expected result | PAT resolves to its owning user identity before repository authorization is evaluated |

---
### TC-PAT-009 — Valid PAT Does Not Elevate Repository Authority

| Field | Value |
|---|---|
| Priority | P1 |
| Type | Security / Authorization |
| Preconditions | Valid PAT belongs to a user who does not own the target repository |
| Action | Attempt a repository operation requiring owner authorization |
| Expected result | Operation is rejected; PAT validity alone does not grant repository permission |

---
## 16. Git Smart HTTP Write Test Cases

Requirement basis:

```text
FR-043 – FR-049
AR-050 – AR-055
```

---
### TC-GITW-001 — Receive-Pack Advertisement for Authorized Owner

| Field | Value |
|---|---|
| Priority | P1 |
| Type | Git / Integration |
| Preconditions | Repository owner has a valid active PAT |
| Action | Git client requests receive-pack service |
| Expected result | Service advertisement succeeds |

---
### TC-GITW-002 — Push New Commit Successfully

| Field | Value |
|---|---|
| Priority | P1 |
| Type | End-to-End |
| Preconditions | Owner has local clone and valid active PAT |
| Action | Create local commit and execute `git push` |
| Expected result | Push succeeds and remote ref advances to new commit |

---
### TC-GITW-003 — Reject Push Without Credentials

| Field | Value |
|---|---|
| Priority | P1 |
| Type | Security |
| Action | Attempt push without credentials |
| Expected result | Push is rejected |

---
### TC-GITW-004 — Reject Push with Invalid PAT

| Field | Value |
|---|---|
| Priority | P1 |
| Type | Security |
| Action | Attempt push using invalid token |
| Expected result | Push is rejected and repository refs are unchanged |

---
### TC-GITW-005 — Reject Push with Revoked PAT

| Field | Value |
|---|---|
| Priority | P1 |
| Type | Security / Authentication |
| Preconditions | Repository owner PAT has been revoked |
| Action | Attempt push using the revoked PAT |
| Expected result | Authentication fails and repository refs are unchanged |

---
### TC-GITW-006 — Reject Push by Authenticated Non-Owner

| Field | Value |
|---|---|
| Priority | P1 |
| Type | Authorization |
| Preconditions | Non-owner has an otherwise valid active PAT |
| Action | Attempt push |
| Expected result | Push is rejected |

---
### TC-GITW-007 — Verify Repository Content After Successful Push

| Field | Value |
|---|---|
| Priority | P1 |
| Type | Integrity / Integration |
| Preconditions | Successful push completed |
| Action | Inspect ref, commit history, and repository browser |
| Expected result | New commit is visible consistently through Git and application repository browser |

---
### TC-GITW-008 — Propagate Controlled Git Receive Failure

| Field | Value |
|---|---|
| Priority | P2 |
| Type | Failure |
| Action | Cause supported receive-pack failure condition |
| Expected result | Client receives controlled failure; application remains stable; repository is not corrupted |

---
## 17. Database Test Cases

---
### TC-DB-001 — All Required Migrations Are Applied

| Field | Value |
|---|---|
| Priority | P1 |
| Type | Database |
| Action | Execute migration status inspection |
| Expected result | All required V1.0.0 migrations show applied; no required migration is pending |

---
### TC-DB-002 — User Identity Constraints Are Enforced

| Field | Value |
|---|---|
| Priority | P1 |
| Type | Database / Integrity |
| Action | Attempt duplicate constrained identity values |
| Expected result | Database/application constraints prevent invalid duplicate state |

---
### TC-DB-003 — Repository Ownership Relationship Is Preserved

| Field | Value |
|---|---|
| Priority | P1 |
| Type | Database / Integration |
| Preconditions | Repository exists |
| Action | Read persisted repository and owner relationship |
| Expected result | Repository references correct owner consistently |

---
### TC-DB-004 — PAT Ownership Relationship Is Preserved

| Field | Value |
|---|---|
| Priority | P1 |
| Type | Database |
| Preconditions | PAT exists |
| Action | Inspect persisted relationship |
| Expected result | PAT belongs to correct user and cannot be reassigned through unsupported behavior |

---
### TC-DB-005 — Repository Metadata and Storage State Remain Consistent

| Field | Value |
|---|---|
| Priority | P1 |
| Type | Integrity |
| Action | Create and operate on repository |
| Expected result | Database metadata corresponds to intended physical repository storage state |

---
## 18. Web UI Test Cases

Requirement basis:

```text
FR-089 – FR-095
```

---
### TC-UI-001 — Registration Page Supports Valid Registration Flow

| Field | Value |
|---|---|
| Priority | P2 |
| Type | UI / Functional |
| Action | Register through browser UI |
| Expected result | User receives expected success/navigation state |

---
### TC-UI-002 — Login Page Supports Valid Login Flow

| Field | Value |
|---|---|
| Priority | P2 |
| Type | UI / Functional |
| Action | Login through browser |
| Expected result | Authenticated UI becomes available |

---
### TC-UI-003 — Dashboard Displays Accessible Repository Data

| Field | Value |
|---|---|
| Priority | P2 |
| Type | UI / Integration |
| Preconditions | User owns/accesses repositories |
| Action | Open dashboard |
| Expected result | Expected repository cards/list are rendered |

---
### TC-UI-004 — Repository Creation Dialog Creates Repository

| Field | Value |
|---|---|
| Priority | P2 |
| Type | UI / End-to-End |
| Action | Submit valid repository through UI |
| Expected result | Repository is created and subsequently visible in UI |

---
### TC-UI-005 — Repository Detail Page Loads

| Field | Value |
|---|---|
| Priority | P2 |
| Type | UI |
| Preconditions | Accessible repository exists |
| Action | Navigate to repository detail route |
| Expected result | Correct metadata and available repository actions are displayed |

---
### TC-UI-006 — Repository Browser Displays Tree and File Content

| Field | Value |
|---|---|
| Priority | P2 |
| Type | UI / Integration |
| Preconditions | Repository contains browsable content |
| Action | Browse tree and select file |
| Expected result | Correct directory/file view is rendered |

---
### TC-UI-007 — PAT Management UI Creates and Lists Token Metadata

| Field | Value |
|---|---|
| Priority | P2 |
| Type | UI / Security |
| Preconditions | User authenticated |
| Action | Create PAT and revisit token list |
| Expected result | Creation succeeds; secret display behavior follows approved one-time rules; list shows safe metadata |

---
### TC-UI-008 — UI Displays Controlled Validation/Error State

| Field | Value |
|---|---|
| Priority | P3 |
| Type | UI / Negative |
| Action | Submit invalid supported form input |
| Expected result | User-visible validation/error feedback appears without application crash |

---
### TC-UI-009 — Unauthorized Navigation Does Not Expose Private Data

| Field | Value |
|---|---|
| Priority | P1 |
| Type | UI / Security |
| Action | Navigate directly to inaccessible private repository route |
| Expected result | Private repository data is not rendered |

---
## 19. Security Test Cases

Requirement basis:

```text
NFR-001 – NFR-018
FR-061 – FR-078
```

---
### TC-SEC-001 — SQL Injection Attempt in Authentication Input

| Field | Value |
|---|---|
| Priority | P1 |
| Type | Security |
| Action | Submit SQL-injection-oriented credential payload |
| Expected result | Authentication is not bypassed; query semantics are not altered |

---
### TC-SEC-002 — SQL Injection Attempt in Repository Input

| Field | Value |
|---|---|
| Priority | P1 |
| Type | Security |
| Action | Submit SQL-injection-oriented repository input |
| Expected result | No arbitrary query execution occurs |

---
### TC-SEC-003 — Command Injection Through Repository Identifier

| Field | Value |
|---|---|
| Priority | P1 |
| Type | Security |
| Action | Submit command separator/metacharacter payload in repository-related input |
| Expected result | No unintended system command executes |

---
### TC-SEC-004 — Path Traversal Through Browser Path

| Field | Value |
|---|---|
| Priority | P1 |
| Type | Security |
| Action | Request traversal-style browser path |
| Expected result | Request cannot access filesystem outside repository |

---
### TC-SEC-005 — Private Repository Concealment Through API

| Field | Value |
|---|---|
| Priority | P1 |
| Type | Security |
| Action | Unauthorized requester probes known private owner/repository identifier |
| Expected result | Response does not disclose protected repository metadata |

---
### TC-SEC-006 — Private Repository Concealment Through Git HTTP

| Field | Value |
|---|---|
| Priority | P1 |
| Type | Security |
| Action | Unauthorized Git request targets private repository |
| Expected result | Git content is not disclosed |

---
### TC-SEC-007 — Raw PAT Is Never Included in Token Listing

| Field | Value |
|---|---|
| Priority | P1 |
| Type | Security |
| Preconditions | PAT exists |
| Action | Request token list |
| Expected result | Raw secret value is absent |

---
### TC-SEC-008 — Revoked PAT Cannot Authenticate

| Field | Value |
|---|---|
| Priority | P1 |
| Type | Security |
| Preconditions | PAT revoked |
| Action | Use revoked token against Git endpoint |
| Expected result | Authentication fails |

---
### TC-SEC-009 — CSRF Protection Rejects Invalid State-Changing Request

| Field | Value |
|---|---|
| Priority | P1 |
| Type | Security |
| Action | Send applicable session-authenticated state-changing request without valid CSRF context |
| Expected result | Request is rejected |

---
### TC-SEC-010 — Production Configuration Does Not Enable Unsafe Debug Defaults

| Field | Value |
|---|---|
| Priority | P1 |
| Type | Security / Configuration |
| Action | Inspect production environment example and deployment configuration |
| Expected result | Production example does not prescribe unsafe debug exposure or committed secrets |

---
### TC-SEC-011 — Sensitive Files Are Not Intended for Direct Web Exposure

| Field | Value |
|---|---|
| Priority | P1 |
| Type | Deployment Security |
| Action | Inspect Nginx/application routing configuration |
| Expected result | Sensitive backend/environment files are not intentionally exposed through web root |

---
### TC-SEC-012 — Git Process Cannot Execute Arbitrary Binary

| Field | Value |
|---|---|
| Priority | P1 |
| Type | Security |
| Action | Attempt to request execution outside controlled Git command model |
| Expected result | Arbitrary binary execution is impossible through supported request path |

---
## 20. Observability Test Cases

Requirement basis:

```text
FR-096 – FR-098
```

---
### TC-OBS-001 — Liveness Endpoint Returns Healthy Response

| Field | Value |
|---|---|
| Priority | P2 |
| Type | Observability / Integration |
| Action | Request `/up` |
| Expected result | Healthy application returns successful liveness response |

---
### TC-OBS-002 — Readiness Endpoint Returns Healthy Response

| Field | Value |
|---|---|
| Priority | P2 |
| Type | Observability |
| Action | Request `/api/v1/health/readiness` with dependencies available |
| Expected result | Response indicates ready/healthy state |

---
### TC-OBS-003 — Readiness Detects Database Dependency Failure

| Field | Value |
|---|---|
| Priority | P2 |
| Type | Failure / Observability |
| Action | Simulate or configure database unavailable condition where practical |
| Expected result | Readiness reports unhealthy/not-ready condition rather than false success |

---
### TC-OBS-004 — Readiness Detects Git Dependency Failure

| Field | Value |
|---|---|
| Priority | P2 |
| Type | Failure / Observability |
| Action | Simulate unavailable Git executable where practical |
| Expected result | Readiness reports dependency failure |

---
### TC-OBS-005 — Request Observability Produces Correlation Information

| Field | Value |
|---|---|
| Priority | P3 |
| Type | Observability |
| Action | Execute representative API request |
| Expected result | Implemented request observability/correlation behavior is produced consistently |

---
### TC-OBS-006 — Logs Do Not Expose PAT Secret

| Field | Value |
|---|---|
| Priority | P1 |
| Type | Security / Observability |
| Action | Perform authenticated Git operation using PAT and inspect applicable logs |
| Expected result | Raw PAT secret is not logged |

---
## 21. Failure and Exception Test Cases

Requirement basis:

```text
FR-079 – FR-083
NFR-028 – NFR-041 as applicable
```

---
### TC-FAIL-001 — Missing Repository Produces Controlled Response

| Field | Value |
|---|---|
| Priority | P2 |
| Type | Failure |
| Action | Request nonexistent repository |
| Expected result | Controlled not-found/concealment response occurs; server does not crash |

---
### TC-FAIL-002 — Invalid Commit Identifier Produces Controlled Response

| Field | Value |
|---|---|
| Priority | P2 |
| Type | Failure |
| Action | Request invalid commit identifier |
| Expected result | Validation/not-found response is controlled |

---
### TC-FAIL-003 — Unsupported Git Object Produces Controlled Response

| Field | Value |
|---|---|
| Priority | P2 |
| Type | Failure |
| Action | Request object as incompatible browser resource |
| Expected result | Operation is rejected without application instability |

---
### TC-FAIL-004 — Git Command Failure Does Not Corrupt Repository

| Field | Value |
|---|---|
| Priority | P1 |
| Type | Reliability |
| Action | Trigger supported Git command failure |
| Expected result | Failure is reported; repository remains structurally valid |

---
### TC-FAIL-005 — Malformed API Payload Is Rejected

| Field | Value |
|---|---|
| Priority | P2 |
| Type | API / Negative |
| Action | Send malformed or invalid request payload |
| Expected result | Controlled validation/error response is returned |

---
### TC-FAIL-006 — Storage Failure Is Reported Safely

| Field | Value |
|---|---|
| Priority | P1 |
| Type | Failure |
| Action | Simulate unavailable/unwritable repository storage where practical |
| Expected result | Operation fails safely without database/filesystem corruption |

---
### TC-FAIL-007 — Database Failure Does Not Produce False Success

| Field | Value |
|---|---|
| Priority | P1 |
| Type | Failure |
| Action | Simulate unavailable database for dependent operation where practical |
| Expected result | Operation reports failure and does not claim successful mutation |

---
## 22. System and End-to-End Test Cases

---
### TC-E2E-001 — Registration to Repository Creation Flow

| Field | Value |
|---|---|
| Priority | P1 |
| Type | End-to-End |
| Procedure | Register user → authenticate → create repository → retrieve repository |
| Expected result | Entire workflow succeeds and metadata/storage are consistent |

---
### TC-E2E-002 — Public Repository Create, Push, Browse, Clone Flow

| Field | Value |
|---|---|
| Priority | P1 |
| Type | End-to-End |
| Procedure | Create public repository → create PAT → clone/push initial commit → browse repository → clone anonymously |
| Expected result | All supported operations succeed and content remains consistent |

---
### TC-E2E-003 — Private Repository Owner Workflow

| Field | Value |
|---|---|
| Priority | P1 |
| Type | End-to-End / Security |
| Procedure | Create private repository → create valid PAT → push → browse while authenticated → clone with PAT |
| Expected result | Owner can complete all authorized operations |

---
### TC-E2E-004 — Private Repository Unauthorized User Workflow

| Field | Value |
|---|---|
| Priority | P1 |
| Type | End-to-End / Security |
| Procedure | Second user attempts metadata read, browser access, clone, and push against private repository |
| Expected result | All unauthorized operations are denied/concealed |

---
### TC-E2E-005 — PAT Revocation End-to-End

| Field | Value |
|---|---|
| Priority | P1 |
| Type | End-to-End / Security |
| Procedure | Create token → verify Git access → revoke token → repeat Git access |
| Expected result | Access works before revocation and fails afterward |

---
### TC-E2E-006 — Push-to-Browser Consistency

| Field | Value |
|---|---|
| Priority | P1 |
| Type | End-to-End / Integrity |
| Procedure | Push new commit → query commit history → open commit → browse changed file |
| Expected result | Same pushed state is visible consistently across Git and web/API interfaces |

---
### TC-E2E-007 — Logout Terminates Browser Session Access

| Field | Value |
|---|---|
| Priority | P2 |
| Type | End-to-End |
| Procedure | Login → access protected page → logout → revisit protected page/API |
| Expected result | Protected access requires authentication again |

---
## 23. Performance-Oriented Test Cases

Requirement basis:

```text
NFR-023 – NFR-027
```

Formal performance thresholds must follow the exact approved NFR text.

---
### TC-PERF-001 — Representative Web/API Response Measurement

| Field | Value |
|---|---|
| Priority | P3 |
| Type | Performance |
| Action | Measure representative supported API/browser interactions under controlled local conditions |
| Expected result | Results are recorded and compared with applicable approved NFR thresholds |

---
### TC-PERF-002 — Representative Git Clone Measurement

| Field | Value |
|---|---|
| Priority | P2 |
| Type | Performance / Git |
| Action | Measure supported clone operation using controlled repository fixture |
| Expected result | Result is recorded and evaluated against applicable Git performance NFRs |

---
### TC-PERF-003 — Representative Git Push Measurement

| Field | Value |
|---|---|
| Priority | P2 |
| Type | Performance / Git |
| Action | Measure supported push operation using controlled fixture |
| Expected result | Result is recorded and evaluated against applicable approved threshold |

---
## 24. Regression Test Cases

These are formal regression gates rather than substitutes for individual functional cases.

---
### TC-REG-001 — Complete Backend Test Suite

| Field | Value |
|---|---|
| Priority | P1 |
| Type | Automated / Regression |
| Command | `php artisan test` |
| Expected result | No failing backend test; skips documented separately |

---
### TC-REG-002 — Backend Laravel Pint Verification

| Field | Value |
|---|---|
| Priority | P2 |
| Type | Automated / Quality |
| Command | `vendor\bin\pint --test` |
| Expected result | PASS |

---
### TC-REG-003 — Frontend TypeScript Verification

| Field | Value |
|---|---|
| Priority | P1 |
| Type | Automated / Regression |
| Command | `npx.cmd tsc --noEmit` |
| Expected result | Zero TypeScript errors |

---
### TC-REG-004 — Complete Frontend Test Suite

| Field | Value |
|---|---|
| Priority | P1 |
| Type | Automated / Regression |
| Command | `npm.cmd test -- --run` |
| Expected result | All frontend tests pass |

---
### TC-REG-005 — Frontend Lint Verification

| Field | Value |
|---|---|
| Priority | P2 |
| Type | Automated / Quality |
| Command | `npm.cmd run lint` |
| Expected result | Zero blocking lint errors |

---
### TC-REG-006 — Frontend Production Build

| Field | Value |
|---|---|
| Priority | P1 |
| Type | Automated / Build |
| Command | `npm.cmd run build` |
| Expected result | Production build completes successfully |

---
## 25. Acceptance Test Cases

Acceptance cases summarize business-visible V1.0.0 outcomes.

Detailed acceptance evidence will be recorded in WP-18.

---
### TC-ACC-001 — User Can Establish an Account and Authenticate

| Field | Value |
|---|---|
| Priority | P1 |
| Acceptance basis | Identity/authentication user stories |
| Expected result | Registration, login, authenticated use, and logout operate as approved |

---
### TC-ACC-002 — User Can Create and Access Repository

| Field | Value |
|---|---|
| Priority | P1 |
| Acceptance basis | Repository creation/retrieval user stories |
| Expected result | Authenticated user can create repository and access its supported metadata |

---
### TC-ACC-003 — Public Repository Supports Intended Public Read

| Field | Value |
|---|---|
| Priority | P1 |
| Acceptance basis | Public repository/read user stories |
| Expected result | Public repository can be browsed/cloned through supported public interfaces |

---
### TC-ACC-004 — Private Repository Remains Protected

| Field | Value |
|---|---|
| Priority | P1 |
| Acceptance basis | Authorization user stories |
| Expected result | Unauthorized actors cannot retrieve protected private content |

---
### TC-ACC-005 — User Can Create and Revoke PAT

| Field | Value |
|---|---|
| Priority | P1 |
| Acceptance basis | PAT user stories |
| Expected result | User can create a usable token and revoke it successfully |

---
### TC-ACC-006 — Valid Git Clone and Fetch Work

| Field | Value |
|---|---|
| Priority | P1 |
| Acceptance basis | Git Smart HTTP read user stories |
| Expected result | Real Git client can clone and fetch supported repository |

---
### TC-ACC-007 — Valid Git Push Works

| Field | Value |
|---|---|
| Priority | P1 |
| Acceptance basis | Git Smart HTTP write user stories |
| Expected result | Authorized repository owner can push using a valid active PAT |

---
### TC-ACC-008 — Repository Can Be Browsed Through Web Application

| Field | Value |
|---|---|
| Priority | P1 |
| Acceptance basis | Repository browser user stories |
| Expected result | Required tree, file, commit-list, and commit-detail behavior works |

---
### TC-ACC-009 — GitForge Rejects Unauthorized Git Write

| Field | Value |
|---|---|
| Priority | P1 |
| Acceptance basis | Authorization and write-security requirements |
| Expected result | Unauthorized push cannot modify repository |

---
### TC-ACC-010 — GitForge Is Operationally Observable

| Field | Value |
|---|---|
| Priority | P2 |
| Acceptance basis | FR-096 – FR-098 |
| Expected result | Required health/readiness and applicable observability behavior are available |

---
## 26. Test Data Requirements

Formal execution requires controlled test identities.

At minimum:

```text
User A — repository owner
User B — authenticated non-owner
Anonymous user
```

Required repository fixtures:

```text
Public repository

Private repository

Repository containing:

- branch/reference
- root tree
- nested directory
- text file
- multiple commits
```

Required PAT states:

```text
Valid active PAT owned by User A
Valid active PAT owned by User B
Revoked PAT
Invalid PAT
Malformed PAT
```

V1.0.0 does not require separate read-capable, write-capable, or insufficient-ability PAT states.

Secrets used during testing must not be committed to the repository.

---
## 27. Environment Preconditions

Before formal case execution, WP-03 must verify:

- PHP runtime;
- Laravel application dependencies;
- PostgreSQL availability;
- database connectivity;
- migration state;
- Git executable;
- repository storage root;
- backend configuration;
- frontend dependencies;
- frontend build environment;
- browser environment;
- local HTTP endpoints.

Tests requiring an unavailable prerequisite must be marked `BLOCKED`, not `PASS`.

---
## 28. Test Evidence Requirements

For each executed test, evidence should record as applicable:

| Evidence | Example |
|---|---|
| Test ID | `TC-GITW-002` |
| Date/time | execution timestamp |
| Environment | local M5 environment |
| Preconditions | fixtures and authentication state |
| Command/request | exact command or request |
| Expected result | specification |
| Actual result | observed behavior |
| Status | PASS / FAIL / BLOCKED |
| Defect ID | when failed |
| Notes | environment limitation or observation |

---
## 29. Defect Handling Rule

When a test fails:

```text
Test FAIL
   |
   v
Reproduce
   |
   v
Classify defect
   |
   v
Correct implementation if required
   |
   v
Retest original case
   |
   v
Run affected regression
   |
   v
Close only after successful verification
```

A code correction does not automatically convert a test to `PASS`.

---
## 30. Environment Limitation Rule

An environment limitation must be distinguished from a product defect.

Example:

```text
Directory symlink security test
        |
        +--> environment supports symlink
        |       |
        |       v
        |    execute test
        |
        +--> environment cannot create required symlink
                |
                v
            BLOCKED / ENVIRONMENT LIMITATION
```

A blocked environment-specific test may not be silently counted as passed.

---
## 31. Security Test Execution Rule

P1 security cases must include explicit negative verification.

A security test passes only when:

- prohibited operation fails;
- prohibited data is not disclosed;
- system state remains valid;
- no side effect indicates partial unauthorized execution;
- relevant evidence supports the result.

HTTP status alone is insufficient if application state could still have changed.

---
## 32. Git End-to-End Execution Rule

Real Git-client verification should use actual Git commands where applicable, for example:

```text
git clone
git fetch
git add
git commit
git push
```

The exact credential-handling procedure must avoid committing secrets or exposing PAT values unnecessarily.

---
## 33. Database Verification Rule

Database tests should verify both:

1. externally observable behavior; and
2. persistence/integrity where direct inspection adds meaningful evidence.

Direct database manipulation must not be used to manufacture a false application success result.

---
## 34. UI Verification Rule

UI tests must verify more than rendering alone when the page represents a functional workflow.

Where applicable, verify:

```text
UI action
   |
   v
HTTP/API interaction
   |
   v
Backend behavior
   |
   v
Database/filesystem/Git state
   |
   v
Updated UI state
```

---
## 35. Requirements Coverage Strategy

Coverage is organized as follows:

| Requirement Family | Primary Test Families |
|---|---|
| FR-001 – FR-012 | AUTH, SEC, E2E |
| FR-013 – FR-020 | REPO, STORE, DB, E2E |
| FR-021 – FR-028 | REPO, AUTHZ, SEC |
| FR-029 – FR-035 | BROWSE, UI, E2E |
| FR-036 – FR-042 | GITR, PAT, AUTHZ, E2E |
| FR-043 – FR-049 | GITW, PAT, AUTHZ, E2E |
| FR-050 – FR-060 | PAT, SEC, UI |
| FR-061 – FR-066 | AUTHZ, SEC |
| FR-067 – FR-070 | STORE, DB, SEC |
| FR-071 – FR-074 | GEXEC, SEC, FAIL |
| FR-075 – FR-078 | AUTH, REPO, BROWSE, API negative testing |
| FR-079 – FR-083 | FAIL, OBS |
| FR-084 – FR-088 | Lifecycle testing according to implemented/conditional scope |
| FR-089 – FR-095 | UI, E2E |
| FR-096 – FR-098 | OBS, SEC |
| NFR-001 – NFR-018 | SEC, AUTHZ, STORE, GEXEC |
| NFR-019 – NFR-022 | AUTH, SEC |
| NFR-023 – NFR-027 | PERF |
| NFR-028 – NFR-038 | DB, STORE, GITR, GITW, FAIL |
| NFR-039 – NFR-041 | OBS, FAIL |

WP-19 will establish final row-level traceability where required.

---
## 36. Repository Lifecycle Conditional Scope

Requirements `FR-084 – FR-088` contain lifecycle behavior with differing priority/conditional status in the approved RTM.

Therefore:

- implemented required behavior must be tested;
- explicitly conditional/deferred behavior must not be falsely reported as failed V1.0.0 functionality;
- final scope disposition must be confirmed against approved implementation traceability before M5 closure.

No deferred capability may be accidentally reintroduced as an M5 requirement.

This rule includes separate PAT read/write scopes, which are deferred from GitForge V1.0.0 and shall not be treated as required testing behavior.

---
## 37. Test Execution Ownership

For this project, formal M5 test execution may be performed by the project owner/developer acting in the tester role.

To preserve verification quality:

- expected results must be defined before execution;
- failures must be recorded rather than rationalized away;
- implementation defects must be corrected through traceable commits;
- retesting must occur after corrections;
- milestone closure must use documented evidence.

---
## 38. Planned Work-Package Mapping

| M5 Work Package | Primary Test Cases |
|---|---|
| WP-03 Test Environment Validation | Environment prerequisites |
| WP-04 Authentication Testing | TC-AUTH-* |
| WP-05 Repository Metadata Testing | TC-REPO-* and relevant TC-DB-* |
| WP-06 Authorization Testing | TC-AUTHZ-* |
| WP-07 Repository Storage Testing | TC-STORE-* |
| WP-08 Git Process Execution Testing | TC-GEXEC-* |
| WP-09 Repository Browser Testing | TC-BROWSE-* |
| WP-10 Git Smart HTTP Read Testing | TC-GITR-* |
| WP-11 PAT Testing | TC-PAT-* |
| WP-12 Git Smart HTTP Write Testing | TC-GITW-* |
| WP-13 Web UI Testing | TC-UI-* |
| WP-14 Security Testing | TC-SEC-* |
| WP-15 Observability and Failure Testing | TC-OBS-* and TC-FAIL-* |
| WP-16 System and End-to-End Testing | TC-E2E-* |
| WP-17 Regression Testing | TC-REG-* |
| WP-18 Acceptance Testing | TC-ACC-* |
| WP-19 Test Traceability | All formal test IDs |
| WP-20 Testing Validation | Entire M5 evidence set |
| WP-21 Milestone Review | M5 exit criteria |

---
## 39. Test Inventory

Current formal test inventory:

| Area | Count |
|---|---:|
| Authentication | 10 |
| Repository metadata | 10 |
| Authorization | 8 |
| Repository storage | 7 |
| Git process execution | 7 |
| Repository browser | 9 |
| Git Smart HTTP read | 8 |
| PAT | 9 |
| Git Smart HTTP write | 8 |
| Database | 5 |
| Web UI | 9 |
| Security | 12 |
| Observability | 6 |
| Failure/exception | 7 |
| System/end-to-end | 7 |
| Performance | 3 |
| Regression | 6 |
| Acceptance | 10 |
| **Total** | **141** |

---
## 40. Initial Execution Status

At WP-02 creation:

```text
Total formal test cases: 141

Executed: 0
Passed: 0
Failed: 0
Blocked: 0
Not executed: 141
```

Formal execution begins in subsequent testing work packages.

WP-02 defines tests; it does not pre-mark unexecuted tests as successful.

---
## 41. WP-02 Quality Review Checklist

WP-02 review must verify:

- test IDs are unique;
- test families are complete;
- P1 security paths exist;
- positive cases exist;
- negative cases exist;
- authorization cases distinguish owner/non-owner/anonymous;
- Git read cases exist;
- Git write cases exist;
- PAT lifecycle cases exist;
- repository-storage boundary cases exist;
- Git-process security cases exist;
- UI cases exist;
- database cases exist;
- observability cases exist;
- failure cases exist;
- end-to-end cases exist;
- regression cases exist;
- acceptance cases exist;
- requirements families are represented;
- deferred/conditional scope is not treated as automatically required;
- separate PAT read/write scopes are not treated as V1.0.0 requirements;
- blocked-test handling is defined;
- evidence requirements are defined.

---
## 42. WP-02 Exit Criteria

WP-02 — Test Case Specification is complete when:

1. test identifier conventions are defined;
2. result states are defined;
3. priorities are defined;
4. authentication tests are specified;
5. repository metadata tests are specified;
6. authorization tests are specified;
7. repository storage tests are specified;
8. Git process execution tests are specified;
9. repository browser tests are specified;
10. Git Smart HTTP read tests are specified;
11. PAT tests are specified;
12. Git Smart HTTP write tests are specified;
13. database tests are specified;
14. web UI tests are specified;
15. security tests are specified;
16. observability tests are specified;
17. failure/exception tests are specified;
18. end-to-end tests are specified;
19. performance-oriented tests are identified;
20. regression tests are specified;
21. acceptance tests are specified;
22. evidence rules are defined;
23. environment limitation rules are defined;
24. requirement-family coverage is established;
25. deferred PAT scope behavior is excluded from mandatory V1.0.0 testing;
26. this document has been reviewed and approved.

---
## 43. Current WP-02 Status

```text
WP-02 — Test Case Specification

Status: APPROVED

Formal test cases specified: 141
Formal test cases executed: 0

Scope correction:
- separate PAT read/write scopes are deferred from V1.0.0
- PAT tests use valid/invalid/revoked credential state and repository ownership authorization
- no token-specific read/write ability is required

Next:

- WP-02 approved
- begin WP-03 Test Environment Validation
```

---
## 44. Conclusion

This specification establishes the formal V1.0.0 test inventory for GitForge M5.

It provides:

- requirements-driven test coverage;
- functional coverage;
- negative coverage;
- authorization coverage;
- security coverage;
- Git protocol coverage;
- frontend coverage;
- database coverage;
- storage coverage;
- observability coverage;
- system-level coverage;
- regression coverage;
- acceptance coverage.

Separate PAT read/write scopes are explicitly outside the mandatory GitForge V1.0.0 testing scope.

A valid active PAT establishes the identity of its owner, while repository authorization remains determined by the approved repository visibility and ownership model.

No test result is implied by specification alone.

Actual execution evidence will be recorded in the corresponding M5 work packages.

Current document decision:

```text
APPROVED
```