# GitForge V1.0.0 Business Rules

## 1. Document Information

| Field          | Value                               |
| -------------- | ----------------------------------- |
| Project        | GitForge - Web Git Hosting Platform |
| Target release | V1.0.0                              |
| Document       | Business Rules                      |
| SDLC phase     | Requirements Engineering            |
| Milestone      | M1 - Requirements Engineering       |
| Project owner  | Farhad Valizadegan                  |
| Status         | Approved                            |
| Last updated   | 2026-08-17                          |

---

## 2. Purpose

This document defines the authoritative business and domain rules for GitForge V1.0.0.

Business rules constrain valid system behavior across multiple functional requirements.

They define policies such as:

* who may perform protected operations;
* how repository ownership works;
* what repository visibility means;
* how repository names are constrained;
* how Personal Access Tokens behave;
* how Git read and write access is governed;
* how relational metadata and physical repositories must remain consistent.

These rules are independent of a specific controller, database schema, or implementation technique.

---

## 3. Rule Identification

Business rules use the following identifier format:

```text
BR-XXX
```

Example:

```text
BR-001
```

Each rule expresses one domain-level policy or constraint.

---

# 4. User Identity Rules

## BR-001 â€” Unique User Identity

Each registered user shall have a unique persistent application identity.

---

## BR-002 â€” Unique Email

A normalized email address used as a unique login identity shall not belong to more than one active user account.

---

## BR-003 â€” Password Confidentiality

A user's plaintext password shall never be treated as persistent application data.

---

## BR-004 â€” Authentication Required for Protected User Actions

Operations designated as authenticated-user actions shall require a valid authenticated identity.

---

# 5. Repository Ownership Rules

## BR-005 â€” Single Primary Owner

Each repository shall have exactly one primary owner in GitForge V1.0.0.

---

## BR-006 â€” Repository Owner Must Exist

A repository owner shall reference an existing GitForge user.

---

## BR-007 â€” Creator Becomes Owner

When an authenticated user creates a repository, that user shall become the repository owner.

---

## BR-008 â€” Ownership Is Contextual

Repository ownership shall be evaluated per repository.

A user who owns one repository shall not automatically gain owner privileges over another repository.

---

## BR-009 â€” No Organization Ownership

Repositories shall not be owned by organizations in V1.0.0.

---

## BR-010 â€” No Team Ownership

Repositories shall not be owned by teams in V1.0.0.

---

# 6. Repository Namespace Rules

## BR-011 â€” Owner-Scoped Namespace

Repository names shall be unique within the repository owner's namespace.

Conceptually:

```text
owner/repository
```

shall uniquely identify a repository.

---

## BR-012 â€” Duplicate Repository Name Rejection

The same owner shall not have two repositories with the same normalized repository name.

---

## BR-013 â€” Repository Name Required

Every repository shall have a non-empty logical name.

---

## BR-014 â€” Repository Name Validation

Repository names shall satisfy the approved V1.0.0 repository naming policy before persistence or filesystem creation.

---

## BR-015 â€” Repository Name Must Be Safe for Storage Resolution

A repository name shall not contain values that can cause resolution outside the configured repository storage namespace.

---

## BR-016 â€” Reserved Path Components Prohibited

Repository names shall not use path traversal or equivalent reserved path components.

Examples include:

```text
.
..
../repo
..\\repo
```

or equivalent encoded forms.

---

# 7. Repository Visibility Rules

## BR-017 â€” Supported Visibility Values

V1.0.0 shall support exactly the following repository visibility values unless scope is formally changed:

```text
public
private
```

---

## BR-018 â€” Public Repository Meaning

A public repository may be read by actors permitted under the relevant public-access interface policy.

Public visibility shall never imply write permission.

---

## BR-019 â€” Private Repository Meaning

A private repository shall be accessible only to authenticated actors explicitly authorized for that repository.

---

## BR-020 â€” Owner Private Access

The owner of a private repository shall be authorized to access that repository after successful authentication.

---

## BR-021 â€” Visibility Enforcement Is Server-Side

Repository visibility shall be enforced by server-side authorization logic.

Client-side UI state shall not determine repository access.

---

# 8. Repository Read Rules

## BR-022 â€” Public Read Policy

Public repositories may permit unauthenticated read operations only where the relevant interface explicitly supports anonymous access.

---

## BR-023 â€” Private Read Policy

Private repository read operations shall require authenticated and authorized access.

---

## BR-024 â€” Read Authorization Before Repository Exposure

Protected repository content shall not be returned before repository access authorization succeeds.

---

## BR-025 â€” Git Read Authorization

Git clone and fetch operations shall obey the same repository visibility and authorization policy as equivalent repository read access.

---

# 9. Repository Write Rules

## BR-026 â€” Authentication Required for Write

Every repository write operation shall require authentication.

---

## BR-027 â€” Anonymous Write Prohibited

Unauthenticated actors shall never be allowed to modify a hosted repository.

---

## BR-028 â€” Owner Write Permission

A repository owner shall have repository write permission for the owned repository when using a valid supported credential.

---

## BR-029 â€” Non-Owner Write Prohibited by Default

A non-owner shall not have repository write permission in V1.0.0 unless a later approved requirement explicitly introduces collaboration permissions.

---

## BR-030 â€” Public Does Not Mean Writable

Public repository visibility shall not grant write permission.

---

## BR-031 â€” Write Authorization Before Receive-Pack

Git receive-pack execution shall not begin until repository write authorization succeeds.

---

# 10. Repository Creation Rules

## BR-032 â€” Authentication Required for Repository Creation

Only an authenticated user may create a repository.

---

## BR-033 â€” Repository Metadata and Git Repository Must Correspond

A successfully created application repository shall correspond to a successfully initialized physical Git repository.

---

## BR-034 â€” No Partial Success Reporting

Repository creation shall not be reported as successful if required metadata persistence or physical repository initialization fails.

---

## BR-035 â€” Repository Creation Uses Controlled Storage

Physical repository creation shall occur only within the configured repository storage root.

---

# 11. Repository Storage Rules

## BR-036 â€” Configured Repository Root

All GitForge-managed repositories shall exist below the configured repository storage root.

---

## BR-037 â€” No Arbitrary Filesystem Access

Repository identifiers shall not permit clients to select arbitrary filesystem locations.

---

## BR-038 â€” Repository Path Derived by Application

Physical repository paths shall be derived through controlled application logic.

---

## BR-039 â€” GitForge Metadata Is Not Git History

Application metadata such as repository owner and visibility shall remain conceptually separate from Git-native commit and object data.

---

## BR-040 â€” Git Is Authoritative for Git History

Commit history, branches, tags, trees, and blobs shall primarily be derived from the physical Git repository.

---

# 12. Personal Access Token Rules

## BR-041 â€” Token Has One Owner

Each Personal Access Token shall belong to exactly one GitForge user.

---

## BR-042 â€” Token Owner Must Exist

A Personal Access Token shall not reference a nonexistent user.

---

## BR-043 â€” Token Creation Requires Authentication

Only an authenticated user may create a Personal Access Token.

---

## BR-044 â€” Token Secret Is Sensitive

The plaintext Personal Access Token secret shall be treated as reusable authentication material.

---

## BR-045 â€” Token Secret Storage Prohibited

The original plaintext token secret shall not be persistently stored after creation.

---

## BR-046 â€” Token Authentication Resolves to User

A valid Personal Access Token shall resolve to the user who owns that token before repository authorization is evaluated.

---

## BR-047 â€” Revoked Token Invalid

A revoked Personal Access Token shall not authenticate subsequent requests.

---

## BR-048 â€” Invalid Token Has No Identity

An invalid Personal Access Token shall not resolve to an authenticated user.

---

## BR-049 â€” Token Management Is Owner-Only

A user shall manage only Personal Access Tokens owned by that user.

---

## BR-050 â€” Token Scope Cannot Increase Repository Authority

If token scopes are introduced, a token shall not grant repository permissions that the owning user does not otherwise possess.

---

# 13. Authentication and Authorization Rules

## BR-051 â€” Authentication and Authorization Are Separate

Successful authentication establishes identity.

It does not automatically authorize every protected action.

---

## BR-052 â€” Authorization Uses Current Context

Authorization shall consider the current:

* actor;
* repository;
* repository visibility;
* repository ownership;
* requested operation;
* credential state;
* token scope where applicable.

---

## BR-053 â€” Authorization Must Precede Protected Action

A protected operation shall not execute before the applicable authorization decision succeeds.

---

## BR-054 â€” UI State Is Not Authorization

Hidden links, disabled buttons, or undisplayed controls shall not be considered sufficient authorization controls.

---

## BR-055 â€” Private Repository Identifier Is Not Authorization

Knowledge of a private repository identifier or URL shall not grant access to that repository.

---

# 14. Git Smart HTTP Rules

## BR-056 â€” Supported Git Transport

V1.0.0 shall use Git Smart HTTP as the supported remote Git transport.

---

## BR-057 â€” SSH Out of Scope

SSH-based Git transport shall not be required by V1.0.0.

---

## BR-058 â€” Read Service

`git-upload-pack` shall be used only for supported read operations.

---

## BR-059 â€” Write Service

`git-receive-pack` shall be used only for supported write operations.

---

## BR-060 â€” Git Service Must Match Requested Operation

GitForge shall not execute a write-capable Git service for a request authorized only for read access.

---

## BR-061 â€” Repository Authorization Applies to Git Clients

A Git Client shall not bypass ordinary repository access policy.

---

# 15. Git Process Execution Rules

## BR-062 â€” Approved Commands Only

GitForge shall execute only approved Git commands needed by V1.0.0.

---

## BR-063 â€” No Arbitrary Shell Execution

Client-controlled values shall not enable arbitrary shell command execution.

---

## BR-064 â€” Repository Must Be Authorized Before Process Binding

The target repository shall be resolved and authorized before being supplied to the Git process.

---

## BR-065 â€” Process Failure Is Not Success

A failed Git service process shall not be reported as a successful Git operation.

---

# 16. Validation Rules

## BR-066 â€” External Input Is Untrusted

Input received from browsers, Git clients, URLs, request bodies, and query parameters shall be treated as untrusted.

---

## BR-067 â€” Validation Before Persistence

Persistent application data shall satisfy applicable validation rules before normal persistence.

---

## BR-068 â€” Validation Before Filesystem Resolution

Repository identifiers shall be validated before filesystem path resolution.

---

## BR-069 â€” Validation Before Git Execution

Values used to select a repository or Git operation shall be validated before Git process execution.

---

## BR-070 â€” Unsupported Visibility Rejected

Repository visibility values other than those approved for V1.0.0 shall be rejected.

---

# 17. Data Integrity Rules

## BR-071 â€” Repository Owner Referential Integrity

A repository shall not persist with an invalid owner relationship.

---

## BR-072 â€” Token Owner Referential Integrity

A Personal Access Token shall not persist with an invalid owner relationship.

---

## BR-073 â€” Repository Namespace Integrity

Repository namespace uniqueness shall be enforced persistently, not only through user-interface validation.

---

## BR-074 â€” User Identity Integrity

User identity uniqueness constraints shall be protected at the persistence layer where practical.

---

## BR-075 â€” Application and Physical Repository Consistency

Operations affecting both application metadata and physical repository state shall use a defined consistency strategy.

---

# 18. Repository Lifecycle Rules

## BR-076 â€” Owner Controls Repository Settings

Only the repository owner may modify owner-controlled repository settings in V1.0.0.

---

## BR-077 â€” Visibility Change Is Owner-Controlled

If visibility updates are supported, only the repository owner may change repository visibility.

---

## BR-078 â€” Repository Deletion Is Owner-Controlled

If repository deletion is included in V1.0.0, only the repository owner may initiate ordinary repository deletion.

---

## BR-079 â€” Destructive Repository Action Requires Confirmation

A destructive repository deletion workflow exposed through the web interface shall require explicit confirmation before execution.

---

## BR-080 â€” Repository Deletion Must Define Metadata and Storage Behavior

Repository deletion shall not leave deletion behavior between PostgreSQL metadata and physical Git storage undefined.

---

# 19. Repository Browser Rules

## BR-081 â€” Browser Access Follows Repository Authorization

Repository browser access shall use the same repository access policy as other protected repository read operations.

---

## BR-082 â€” Revision Must Resolve

Repository browsing operations requiring a revision shall use a valid resolvable Git revision.

---

## BR-083 â€” Repository Content Is Untrusted

Repository file content shall not automatically be treated as trusted application HTML or executable application code.

---

## BR-084 â€” Binary Data Need Not Be Rendered as Text

Unsupported binary repository content shall not be required to render as ordinary text.

---

# 20. Logging Rules

## BR-085 â€” Plaintext Credentials Must Not Be Logged

GitForge shall not intentionally log plaintext passwords or plaintext Personal Access Token secrets.

---

## BR-086 â€” Logs May Contain Operational Context

Logs may contain non-secret identifiers required for diagnosis, such as:

* user identifier;
* repository identifier;
* operation;
* failure category.

---

## BR-087 â€” Security Logging Must Preserve Confidentiality

Security-related logging shall not defeat credential confidentiality.

---

# 21. Configuration Rules

## BR-088 â€” Secrets Outside Source Control

Deployment secrets shall not be committed to the source repository.

---

## BR-089 â€” Environment-Specific Configuration

Environment-specific service locations and credentials shall be externally configurable.

---

## BR-090 â€” Repository Root Is Configuration

The physical repository storage root shall be deployment configuration, not a user-controlled value.

---

# 22. Scope Control Rules

## BR-091 â€” V1.0.0 Scope Is Authoritative

Capabilities outside the approved V1.0.0 scope shall not be treated as implicit requirements.

---

## BR-092 â€” No Collaboration Permission Model

V1.0.0 shall not assume collaborator, team, or organization permission models unless scope is formally changed.

---

## BR-093 â€” No Pull Request Domain

Pull-request and merge-request workflows are outside V1.0.0.

---

## BR-094 â€” No Issue-Tracking Domain

Issue tracking is outside V1.0.0.

---

## BR-095 â€” No CI/CD Domain

Built-in CI/CD pipeline functionality is outside V1.0.0.

---

## BR-096 â€” No Billing Domain

Billing and subscription management are outside V1.0.0.

---

# 23. Business Rule Summary

| Range           | Rule area                        |
| --------------- | -------------------------------- |
| BR-001 â€“ BR-004 | User identity                    |
| BR-005 â€“ BR-010 | Repository ownership             |
| BR-011 â€“ BR-016 | Repository namespace             |
| BR-017 â€“ BR-021 | Repository visibility            |
| BR-022 â€“ BR-025 | Repository reads                 |
| BR-026 â€“ BR-031 | Repository writes                |
| BR-032 â€“ BR-035 | Repository creation              |
| BR-036 â€“ BR-040 | Repository storage               |
| BR-041 â€“ BR-050 | Personal Access Tokens           |
| BR-051 â€“ BR-055 | Authentication and authorization |
| BR-056 â€“ BR-061 | Git Smart HTTP                   |
| BR-062 â€“ BR-065 | Git process execution            |
| BR-066 â€“ BR-070 | Validation                       |
| BR-071 â€“ BR-075 | Data integrity                   |
| BR-076 â€“ BR-080 | Repository lifecycle             |
| BR-081 â€“ BR-084 | Repository browser               |
| BR-085 â€“ BR-087 | Logging                          |
| BR-088 â€“ BR-090 | Configuration                    |
| BR-091 â€“ BR-096 | Scope control                    |

Total business rules:

```text
96
```

---

## 24. Rule Dependencies

Important policy dependencies include:

```text
Repository Ownership
&#x20;       â†“
Read Authorization
&#x20;       â†“
Write Authorization
```

and:

```text
Repository Name Validation
&#x20;       â†“
Safe Path Resolution
&#x20;       â†“
Repository Isolation
```

and:

```text
Personal Access Token
&#x20;       â†“
Authentication
&#x20;       â†“
User Resolution
&#x20;       â†“
Repository Authorization
```

---

## 25. Relationship to Functional Requirements

Examples:

```text
BR-032 Authentication Required for Repository Creation
&#x20;       â†“
FR-013 Repository Creation
```

```text
BR-029 Non-Owner Write Prohibited by Default
&#x20;       â†“
FR-046 Push Authorization
FR-048 Unauthorized Push Rejection
```

```text
BR-047 Revoked Token Invalid
&#x20;       â†“
FR-059 Revoked Token Rejection
```

```text
BR-037 No Arbitrary Filesystem Access
&#x20;       â†“
FR-067 Controlled Repository Root
FR-068 Safe Repository Path Resolution
```

---

## 26. Relationship to Data Requirements

Examples:

```text
BR-005 Single Primary Owner
&#x20;       â†“
DR-010 Repository Owner
DR-050 Repository-to-Owner Relationship
```

```text
BR-011 Owner-Scoped Namespace
&#x20;       â†“
DR-014 Repository Namespace Uniqueness
```

```text
BR-045 Token Secret Storage Prohibited
&#x20;       â†“
DR-033 Token Credential Representation
DR-034 Plaintext Token Persistence Prohibition
```

---

## 27. Relationship to Authorization Requirements

This document establishes domain policies that will be represented more explicitly in:

```text
12-authorization-requirements-matrix.md
```

The authorization matrix will convert relevant business rules into actor-resource-operation decisions.

---

## 28. Open Business Decisions

The following policy decisions remain to be resolved before M1 closes:

1. whether anonymous web browsing of public repository content is enabled;
2. whether anonymous Git clone/fetch of public repositories is enabled by default;
3. exact repository naming policy;
4. whether Personal Access Tokens have separate read and write scopes;
5. whether repository deletion is mandatory for V1.0.0;
6. whether repository rename is supported;
7. whether any non-owner write authorization exists;
8. whether account deletion is supported;
9. whether repository descriptions are included;
10. whether tokens require expiration in V1.0.0.

Any resolved decision shall update affected requirements and traceability.

---

## 29. Traceability

These business rules derive primarily from:

* Project Definition;
* Requirements Elicitation;
* Actors and External Systems;
* Functional Requirements;
* Non-Functional Requirements;
* Data Requirements;
* Interface Requirements.

Detailed traceability will be maintained in:

```text
14-requirements-traceability-matrix.md
```

---

## 30. Completion Criteria

This document is considered complete when:

* repository ownership policy is explicit;
* repository namespace policy is explicit;
* repository visibility semantics are explicit;
* read/write access rules are explicit;
* Personal Access Token rules are explicit;
* Git Smart HTTP policy is explicit;
* filesystem and process-safety rules are explicit;
* lifecycle rules are defined;
* scope boundaries are enforced through explicit rules;
* no critical business-policy ambiguity remains unresolved.

---

## 31. Document Status

This document is currently:

```text
Approved
```

It will be changed to:

```text
Approved
```

after the business rules have been reviewed for consistency, completeness, enforceability, traceability, and V1.0.0 scope alignment.
