# GitForge V1.0.0 Authorization Requirements Matrix

## 1. Document Information

| Field          | Value                               |
| -------------- | ----------------------------------- |
| Project        | GitForge - Web Git Hosting Platform |
| Target release | V1.0.0                              |
| Document       | Authorization Requirements Matrix   |
| SDLC phase     | Requirements Engineering            |
| Milestone      | M1 - Requirements Engineering       |
| Project owner  | Farhad Valizadegan                  |
| Status         | Approved                            |
| Last updated   | 2026-08-17                          |

---

## 2. Purpose

This document defines the authoritative authorization requirements for GitForge V1.0.0.

It specifies which actors may perform protected operations against GitForge resources under defined authentication, ownership, visibility, and credential conditions.

The matrix distinguishes:

* authentication from authorization;
* public read access from private read access;
* repository ownership from ordinary authenticated-user status;
* browser-session authentication from Personal Access Token authentication;
* read operations from write operations;
* user-interface visibility from server-side permission enforcement.

This document provides direct input to:

* System Analysis;
* System Design;
* middleware and policy design;
* Git Smart HTTP authorization;
* security testing;
* requirements traceability.

---

## 3. Authorization Requirement Identification

Authorization requirements use the following identifier format:

```text
AR-XXX
```

Example:

```text
AR-001
```

Each authorization requirement expresses one access-control obligation.

---

## 4. Authorization Principles

GitForge V1.0.0 follows these authorization principles:

1. authentication establishes identity;
2. authorization determines whether that identity may perform a specific operation;
3. authorization shall be evaluated server-side;
4. repository ownership is contextual;
5. public visibility grants only explicitly permitted read access;
6. public visibility never grants write access;
7. private repositories require explicit authorization;
8. repository writes require authenticated identity;
9. Personal Access Tokens authenticate users but do not independently grant repository ownership;
10. UI controls do not constitute authorization enforcement.

---

# 5. Actor Classes

The authorization model uses these actor classes.

| Actor                                | Description                                                               |
| ------------------------------------ | ------------------------------------------------------------------------- |
| Visitor                              | Unauthenticated human user                                                |
| Authenticated User                   | User authenticated through a valid web session                            |
| Repository Owner                     | Authenticated user who owns the target repository                         |
| Git Client — Anonymous               | Git client without valid authenticated identity                           |
| Git Client — Authenticated Owner     | Git client authenticated as repository owner                              |
| Git Client — Authenticated Non-Owner | Git client authenticated as a user who does not own the target repository |
| System Operator                      | Infrastructure/operator role outside normal repository ownership          |

---

# 6. Authorization Decision Values

The matrices use the following values:

| Value       | Meaning                                                                                            |
| ----------- | -------------------------------------------------------------------------------------------------- |
| Allow       | Operation is permitted                                                                             |
| Deny        | Operation is prohibited                                                                            |
| Conditional | Permission depends on repository visibility, authentication, token scope, or another explicit rule |
| N/A         | Operation does not meaningfully apply to that actor                                                |

---

# 7. General Authorization Requirements

## AR-001 — Server-Side Authorization

GitForge shall evaluate protected authorization decisions on the server before executing the protected operation.

---

## AR-002 — Authentication Does Not Imply Global Access

An authenticated user shall not automatically gain access to every repository or protected resource.

---

## AR-003 — Ownership Is Repository-Specific

Repository ownership shall be evaluated against the specific target repository.

---

## AR-004 — UI Independence

Authorization shall remain effective even when a protected endpoint is invoked directly without using the normal web interface.

---

## AR-005 — Unauthorized Operation Denial

When authorization conditions are not satisfied, GitForge shall deny the protected operation.

---

# 8. User Account Authorization

## AR-006 — Registration Access

A Visitor shall be permitted to access the account registration workflow.

---

## AR-007 — Login Access

A Visitor shall be permitted to access the login workflow.

---

## AR-008 — Logout Access

Only an authenticated session shall meaningfully perform logout.

---

## AR-009 — Current User Context

Authenticated user-specific functionality shall operate against the current authenticated user identity unless an approved requirement explicitly permits another target.

---

# 9. Repository Creation Authorization

## AR-010 — Repository Creation Requires Authentication

Only an authenticated user shall be permitted to create a repository.

---

## AR-011 — Creator Ownership

The authenticated user creating a repository shall become the repository owner.

---

## AR-012 — Ownership Assignment Protection

A normal repository creation request shall not permit the client to arbitrarily assign another user as repository owner.

---

# 10. Repository Read Authorization

## AR-013 — Public Repository Read

A public repository may permit unauthenticated read access only for operations explicitly classified as public-readable.

---

## AR-014 — Public Repository Authenticated Read

An authenticated user shall be permitted to read public repository information that is classified as publicly accessible.

---

## AR-015 — Private Repository Owner Read

A repository owner shall be permitted to read the owner's private repository after successful authentication.

---

## AR-016 — Private Repository Visitor Denial

A Visitor shall not be permitted to access protected private repository information.

---

## AR-017 — Private Repository Non-Owner Denial

Under the default V1.0.0 ownership model, an authenticated non-owner shall not be permitted to access a private repository unless a later approved requirement explicitly grants such access.

---

## AR-018 — Repository Identifier Insufficient

Knowledge of a repository URL, identifier, owner name, or repository name shall not itself grant private repository access.

---

# 11. Repository Write Authorization

## AR-019 — Write Requires Authentication

Every repository write operation shall require valid authentication.

---

## AR-020 — Owner Write Permission

The repository owner shall be permitted to perform supported write operations against the owned repository when using a valid supported credential.

---

## AR-021 — Anonymous Write Denial

An unauthenticated actor shall never be permitted to modify a hosted Git repository.

---

## AR-022 — Non-Owner Write Denial

Under the default V1.0.0 model, an authenticated non-owner shall not be permitted to modify another user's repository.

---

## AR-023 — Public Visibility Does Not Grant Write

A public repository shall remain protected from unauthorized write operations.

---

## AR-024 — Write Authorization Before Git Execution

GitForge shall complete write authorization before executing `git-receive-pack`.

---

# 12. Repository Settings Authorization

## AR-025 — Owner Repository Settings

Only the repository owner shall be permitted to modify owner-controlled repository settings.

---

## AR-026 — Visibility Change

Only the repository owner shall be permitted to change repository visibility.

---

## AR-027 — Repository Deletion

If repository deletion is included in V1.0.0, only the repository owner shall be permitted to initiate ordinary repository deletion.

---

## AR-028 — Repository Rename

If repository rename is included in V1.0.0, only the repository owner shall be permitted to rename the repository.

---

# 13. Repository Browser Authorization

## AR-029 — Repository Browser Read Policy

Repository browser access shall follow repository read authorization.

---

## AR-030 — Branch Listing Authorization

A branch list shall be returned only when the requesting actor has repository read permission.

---

## AR-031 — Commit History Authorization

Commit history shall be returned only when the requesting actor has repository read permission.

---

## AR-032 — Commit Detail Authorization

Commit information shall be returned only when the requesting actor has repository read permission.

---

## AR-033 — Tree Browsing Authorization

Repository tree data shall be returned only when the requesting actor has repository read permission.

---

## AR-034 — File Content Authorization

Repository file content shall be returned only when the requesting actor has repository read permission.

---

# 14. Personal Access Token Management Authorization

## AR-035 — Token Creation Requires Authentication

Only an authenticated user shall be permitted to create a Personal Access Token.

---

## AR-036 — Token Ownership

A newly created Personal Access Token shall belong to the authenticated user who created it.

---

## AR-037 — Token Listing

A user shall be permitted to view only Personal Access Token metadata that belongs to that user.

---

## AR-038 — Token Revocation

A user shall be permitted to revoke only Personal Access Tokens owned by that user.

---

## AR-039 — Other User Token Denial

A user shall not be permitted to view sensitive token-management information for or manage another user's Personal Access Tokens.

---

# 15. Personal Access Token Authentication Authorization

## AR-040 — Valid Token Identity Resolution

A valid active Personal Access Token shall resolve to its owning GitForge user.

---

## AR-041 — Invalid Token Denial

An invalid Personal Access Token shall establish no authenticated identity.

---

## AR-042 — Revoked Token Denial

A revoked Personal Access Token shall establish no authenticated identity.

---

## AR-043 — Token Scope Restriction

If token scopes are implemented, the token scope may restrict the operations available to the authenticated user.

---

## AR-044 — Token Scope Cannot Elevate User Authority

A Personal Access Token shall not grant repository access that its owning user does not otherwise possess.

---

# 16. Git Smart HTTP Read Authorization

## AR-045 — Public Anonymous Clone

If anonymous public Git access is enabled, an anonymous Git Client shall be permitted to perform supported read operations against a public repository.

---

## AR-046 — Private Clone Authentication

A private repository clone or fetch shall require valid authentication.

---

## AR-047 — Private Clone Owner Authorization

A Git Client authenticated as the repository owner shall be permitted to clone or fetch the owner's private repository.

---

## AR-048 — Private Clone Non-Owner Denial

Under the default V1.0.0 model, a Git Client authenticated as a non-owner shall not be permitted to clone or fetch another user's private repository.

---

## AR-049 — Upload-Pack Authorization

`git-upload-pack` shall execute only after repository read authorization succeeds.

---

# 17. Git Smart HTTP Write Authorization

## AR-050 — Receive-Pack Authentication

A request for `git-receive-pack` shall require valid authentication.

---

## AR-051 — Owner Push

A Git Client authenticated as the repository owner shall be permitted to push to the owned repository when all applicable credential conditions are satisfied.

---

## AR-052 — Anonymous Push Denial

An anonymous Git Client shall not be permitted to push.

---

## AR-053 — Non-Owner Push Denial

Under the default V1.0.0 model, a Git Client authenticated as a non-owner shall not be permitted to push to another user's repository.

---

## AR-054 — Public Repository Push Protection

Public repository visibility shall not alter write authorization.

---

## AR-055 — Receive-Pack Authorization

`git-receive-pack` shall execute only after repository write authorization succeeds.

---

# 18. Web Authorization Matrix

| Operation                        |           Visitor | Authenticated User — Non-Owner |  Repository Owner |
| -------------------------------- | ----------------: | -----------------------------: | ----------------: |
| Register account                 |             Allow |                            N/A |               N/A |
| Login                            |             Allow |                            N/A |               N/A |
| Logout                           |        Deny / N/A |                          Allow |             Allow |
| Create repository                |              Deny |                          Allow |             Allow |
| List own repositories            |              Deny |                          Allow |             Allow |
| View public repository metadata  | Conditional Allow |                          Allow |             Allow |
| View private repository metadata |              Deny |                           Deny |             Allow |
| Browse public repository         | Conditional Allow |                          Allow |             Allow |
| Browse private repository        |              Deny |                           Deny |             Allow |
| List branches — public           | Conditional Allow |                          Allow |             Allow |
| List branches — private          |              Deny |                           Deny |             Allow |
| View commits — public            | Conditional Allow |                          Allow |             Allow |
| View commits — private           |              Deny |                           Deny |             Allow |
| View files — public              | Conditional Allow |                          Allow |             Allow |
| View files — private             |              Deny |                           Deny |             Allow |
| Change repository visibility     |              Deny |                           Deny |             Allow |
| Delete repository                |              Deny |                           Deny | Conditional Allow |
| Rename repository                |              Deny |                           Deny | Conditional Allow |
| Create PAT                       |              Deny |                          Allow |             Allow |
| List own PATs                    |              Deny |                          Allow |             Allow |
| Revoke own PAT                   |              Deny |                          Allow |             Allow |
| Manage another user's PAT        |              Deny |                           Deny |              Deny |

`Conditional Allow` means the capability depends on an unresolved V1.0.0 scope decision or interface-specific public-access policy.

---

# 19. Git Smart HTTP Authorization Matrix

| Operation                                       | Anonymous Git Client | Authenticated Non-Owner | Authenticated Repository Owner |
| ----------------------------------------------- | -------------------: | ----------------------: | -----------------------------: |
| Discover public read service                    |    Conditional Allow |                   Allow |                          Allow |
| Clone public repository                         |    Conditional Allow |                   Allow |                          Allow |
| Fetch public repository                         |    Conditional Allow |                   Allow |                          Allow |
| Discover private read service                   |                 Deny |                    Deny |                          Allow |
| Clone private repository                        |                 Deny |                    Deny |                          Allow |
| Fetch private repository                        |                 Deny |                    Deny |                          Allow |
| Discover write service                          |                 Deny |                    Deny |                          Allow |
| Push public repository                          |                 Deny |                    Deny |                          Allow |
| Push private repository                         |                 Deny |                    Deny |                          Allow |
| Execute `git-upload-pack` on public repository  |    Conditional Allow |                   Allow |                          Allow |
| Execute `git-upload-pack` on private repository |                 Deny |                    Deny |                          Allow |
| Execute `git-receive-pack`                      |                 Deny |                    Deny |                          Allow |

---

# 20. Personal Access Token Authorization Matrix

| Operation                             | Unauthenticated Actor | Authenticated Token Owner |      Other Authenticated User |
| ------------------------------------- | --------------------: | ------------------------: | ----------------------------: |
| Create token                          |                  Deny |                     Allow |      Allow for own token only |
| List token metadata                   |                  Deny |                     Allow | Deny for another user's token |
| Revoke token                          |                  Deny |                     Allow |                          Deny |
| Authenticate using valid active token |                   N/A |                     Allow |                           N/A |
| Authenticate using invalid token      |                  Deny |                      Deny |                          Deny |
| Authenticate using revoked token      |                  Deny |                      Deny |                          Deny |
| Recover stored plaintext token        |                  Deny |                      Deny |                          Deny |

---

# 21. Repository Visibility Matrix

| Repository Visibility | Anonymous Web Read | Authenticated Non-Owner Read | Owner Read | Anonymous Write | Non-Owner Write | Owner Write |
| --------------------- | -----------------: | ---------------------------: | ---------: | --------------: | --------------: | ----------: |
| Public                |  Conditional Allow |                        Allow |      Allow |            Deny |            Deny |       Allow |
| Private               |               Deny |                         Deny |      Allow |            Deny |            Deny |       Allow |

This matrix expresses the default V1.0.0 ownership authorization model.

---

# 22. Authorization Decision Flow

The general protected-operation decision is:

```text
Request
   ↓
Identify target resource
   ↓
Classify requested operation
   ↓
Is authentication required?
   |
   +-- Yes --> Authenticate identity
   |              ↓
   |          Authentication valid?
   |              |
   |              +-- No --> DENY
   |
   ↓
Evaluate resource visibility / ownership / permission
   ↓
Evaluate token scope if applicable
   ↓
Authorization satisfied?
   |
   +-- No --> DENY
   |
   +-- Yes --> ALLOW
```

---

# 23. Repository Read Decision

```text
Repository read requested
        ↓
Repository exists?
        |
        +-- No --> Not Found / Deny
        |
        v
Repository public?
        |
        +-- Yes --> Is anonymous read enabled for this interface?
        |                |
        |                +-- Yes --> ALLOW
        |                |
        |                +-- No --> Require authenticated policy
        |
        +-- No --> Private repository
                         ↓
                  Authenticated?
                         |
                         +-- No --> DENY
                         |
                         v
                  Authorized owner?
                         |
                         +-- Yes --> ALLOW
                         |
                         +-- No --> DENY
```

---

# 24. Repository Write Decision

```text
Repository write requested
        ↓
Repository exists?
        |
        +-- No --> DENY
        |
        v
Authenticated?
        |
        +-- No --> DENY
        |
        v
Credential valid and active?
        |
        +-- No --> DENY
        |
        v
Authenticated user owns repository?
        |
        +-- No --> DENY
        |
        v
Token scope permits write, if scopes exist?
        |
        +-- No --> DENY
        |
        v
ALLOW
```

---

# 25. Authorization Trust Boundaries

Authorization enforcement applies at the following trust boundaries.

## 25.1 Browser to GitForge

Browser requests are untrusted.

Protected web actions require server-side authorization.

---

## 25.2 Git Client to GitForge

Git requests are untrusted.

Authentication credentials and repository operation type must be validated before Git service execution.

---

## 25.3 GitForge to Filesystem

Authorization must be completed before a protected repository path is used for repository content access or mutation.

---

## 25.4 GitForge to Git Process

Authorization must be completed before executing a Git service against the target repository.

---

# 26. Authentication Versus Authorization Examples

## Example 1 — Authenticated Non-Owner

```text
User authenticated successfully
        ↓
Target = another user's private repository
        ↓
Authentication = SUCCESS
Authorization = DENY
```

---

## Example 2 — Repository Owner

```text
User authenticated successfully
        ↓
Target = user's own private repository
        ↓
Authentication = SUCCESS
Authorization = ALLOW
```

---

## Example 3 — Public Repository Anonymous Read

```text
No authenticated identity
        ↓
Target = public repository
        ↓
Operation = permitted anonymous read
        ↓
Authentication = NOT REQUIRED
Authorization = ALLOW
```

if anonymous public read is enabled.

---

## Example 4 — Public Repository Anonymous Push

```text
No authenticated identity
        ↓
Target = public repository
        ↓
Operation = write
        ↓
Authentication required
        ↓
Authorization = DENY
```

---

# 27. Authorization Failure Requirements

## AR-056 — No Protected Side Effects

A denied authorization decision shall not execute the protected state-changing operation.

---

## AR-057 — No Git Write Process on Denial

When write authorization fails, GitForge shall not execute authorized `git-receive-pack` processing against the repository.

---

## AR-058 — No Private Data Exposure

When private repository read authorization fails, protected repository content shall not be returned.

---

## AR-059 — Safe Failure Response

Authorization failure responses shall avoid exposing reusable credentials or sensitive internal implementation details.

---

## AR-060 — Authorization Logging

Security-relevant authorization failures may be logged using non-secret contextual information.

---

# 28. Authorization and Repository Storage

Authorization shall occur before access to protected repository content where practical.

The intended sequence is:

```text
Validate repository identifier
        ↓
Resolve application repository
        ↓
Authenticate if required
        ↓
Authorize operation
        ↓
Resolve controlled physical path
        ↓
Perform permitted repository operation
```

A client-provided repository identifier shall not itself authorize filesystem access.

---

# 29. Authorization and Personal Access Tokens

A Personal Access Token participates in authorization as follows:

```text
PAT credential
    ↓
Validate token
    ↓
Resolve token owner
    ↓
Establish user identity
    ↓
Check token scope if applicable
    ↓
Evaluate repository authorization
```

A token is therefore an authentication credential and possible capability limiter.

It is not an independent repository ownership mechanism.

---

# 30. Requirement Mapping

Examples:

```text
AR-015 Private Repository Owner Read
        ↓
FR-028 Owner Access to Private Repository
BR-020 Owner Private Access
UC-023 Authorize Repository Read
US-022 Protect Private Repository Reads
```

```text
AR-022 Non-Owner Write Denial
        ↓
FR-046 Push Authorization
FR-048 Unauthorized Push Rejection
BR-029 Non-Owner Write Prohibited by Default
UC-024 Authorize Repository Write
US-023 Protect Repository Writes
```

```text
AR-038 Token Revocation
        ↓
FR-058 Token Revocation
BR-049 Token Management Is Owner-Only
UC-022 Revoke Personal Access Token
US-021 Revoke Personal Access Token
```

---

# 31. Authorization Requirement Summary

| Range           | Area                           |
| --------------- | ------------------------------ |
| AR-001 – AR-005 | General authorization          |
| AR-006 – AR-009 | User account                   |
| AR-010 – AR-012 | Repository creation            |
| AR-013 – AR-018 | Repository reads               |
| AR-019 – AR-024 | Repository writes              |
| AR-025 – AR-028 | Repository settings            |
| AR-029 – AR-034 | Repository browser             |
| AR-035 – AR-039 | PAT management                 |
| AR-040 – AR-044 | PAT authentication and scope   |
| AR-045 – AR-049 | Git read authorization         |
| AR-050 – AR-055 | Git write authorization        |
| AR-056 – AR-060 | Authorization failure behavior |

Total authorization requirements:

```text
60
```

---

# 32. Critical V1.0.0 Authorization Rules

The following are release-critical:

```text
Private repository access protection
Repository owner write authorization
Anonymous push prohibition
Non-owner push prohibition
Server-side authorization
Personal Access Token ownership
Revoked token rejection
Authorization before git-receive-pack
Authorization before protected repository content exposure
```

Failure of any of these controls may be considered release-blocking.

---

# 33. Scope Decisions Still Open

The following decisions affect the final authorization matrix:

1. whether anonymous web browsing of public repositories is enabled;
2. whether anonymous Git clone and fetch of public repositories are enabled;
3. whether Personal Access Tokens use separate read and write scopes;
4. whether repository deletion remains in V1.0.0;
5. whether repository rename is included;
6. whether any non-owner repository access is introduced;
7. whether token expiration affects authorization;
8. whether an application-level administrator role is required.

Until explicitly resolved, the default V1.0.0 model remains:

```text
Repository owner = repository write authority

Private repository = owner-only access

Public repository = readable according to approved public interface policy

Public repository != publicly writable
```

---

# 34. Relationship to System Design

This document defines:

```text
Who may do what
under which conditions
```

System Design will later define:

```text
Policies
Middleware
Authorization services
Route protection
Controller authorization
Git HTTP authorization components
Token guards
Repository access services
```

The implementation mechanism may change without changing these requirements.

---

# 35. Verification Expectations

Authorization requirements shall eventually be verified using combinations of:

* automated feature tests;
* integration tests;
* security tests;
* Git client interoperability tests;
* direct protected-endpoint tests;
* ownership boundary tests;
* anonymous access tests;
* token revocation tests;
* negative authorization tests.

Particular emphasis shall be placed on denied operations, not only successful operations.

---

# 36. Traceability

These authorization requirements derive from:

* Actors and External Systems;
* Functional Requirements;
* Non-Functional Requirements;
* Data Requirements;
* Interface Requirements;
* Business Rules;
* Use Case Model;
* Detailed Use Case Specifications;
* User Stories and Acceptance Criteria.

Detailed mappings will be maintained in:

```text
14-requirements-traceability-matrix.md
```

---

# 37. Completion Criteria

This document is considered complete when:

* principal actor classes are represented;
* public and private repository access is explicitly distinguished;
* repository read authorization is explicit;
* repository write authorization is explicit;
* repository ownership semantics are explicit;
* Personal Access Token management authorization is explicit;
* Git clone, fetch, and push authorization are explicit;
* denied-operation behavior is defined;
* authorization does not depend on UI controls;
* server-side enforcement is mandatory;
* authorization is sufficiently defined to support later policy and middleware design;
* unresolved authorization decisions are explicitly tracked.

---

# 38. Document Status

This document is currently:

```text
Approved
```

It will be changed to:

```text
Approved
```

after the authorization model has been reviewed for security, completeness, consistency, actor coverage, least-privilege behavior, and V1.0.0 scope alignment.
