# GitForge V1.0.0 Architecture Decisions

## 1. Document Information

| Field | Value |

|---|---|

| Project | GitForge - Web Git Hosting Platform |

| Target release | V1.0.0 |

| Document | Architecture Decisions |

| SDLC phase | System Design |

| Milestone | M3 - System Design |

| Project owner | Farhad Valizadegan |

| Status | Approved |

| Last updated | 2026-08-17 |

---

## 2. Purpose

This document records the architecture-shaping decisions for GitForge V1.0.0.

It translates approved requirements and System Analysis conclusions into concrete architectural choices that will guide:

```text

Container architecture

Component design

Database design

API design

Authorization design

Repository-storage design

Git process-execution design

Deployment design

Observability design

Security design

Implementation

Testing

```

Each Architecture Decision Record identifies:

```text

Context

Decision

Rationale

Consequences

Alternatives

Traceability

```

---

## 3. Architecture Decision Principles

All architectural decisions follow these principles:

```text

Preserve approved V1.0.0 scope

Prefer simplicity over premature distribution

Keep security-critical boundaries explicit

Separate authentication from authorization

Separate application metadata from Git-native state

Use PostgreSQL for application metadata authority

Use physical Git repositories for Git-native authority

Keep repository paths internally controlled

Avoid arbitrary shell execution

Use Git-native operations where Git is authoritative

Fail closed on security-sensitive uncertainty

Keep implementation testable

Keep architecture realistic for portfolio deployment

```

---

## 4. Architecture Style Overview

GitForge V1.0.0 will use a:

```text

Modular Monolith

```

with clearly separated application components inside a single Laravel application.

The runtime architecture is centered around:

```text

Web/Git Clients

       ↓

Web Server / Application Entry

       ↓

Laravel Application

       ↓

+------------------------------+

| Application Components       |

| Authentication               |

| Repository Management        |

| Authorization                |

| Repository Browser           |

| PAT Management               |

| Git Smart HTTP               |

| Repository Storage           |

| Git Process Execution        |

+------------------------------+

       ↓                  ↓

PostgreSQL        Physical Git Repositories

                          ↑

                     Git Executable

```

This structure avoids premature microservices while preserving logical separation.

---

# ADR-001 — Modular Monolith Architecture

## 5. Context

GitForge V1.0.0 requires multiple distinct responsibilities:

```text

Authentication

Repository management

Authorization

Repository browsing

PAT management

Git Smart HTTP

Repository storage

Git execution

Persistence

Diagnostics

```

The system is portfolio-scale and currently targets a single deployment unit.

A distributed architecture would increase:

```text

Deployment complexity

Network boundaries

Operational overhead

Debugging complexity

Testing cost

Failure modes

```

without providing necessary V1.0.0 value.

---

## 6. Decision

GitForge V1.0.0 will use a:

```text

Modular Monolith

```

implemented as one Laravel application.

Internal responsibilities will remain separated into explicit components and interfaces.

---

## 7. Rationale

The modular monolith provides:

```text

Simple deployment

Single application runtime

Clear transaction boundaries

Low operational complexity

Strong internal modularity

Straightforward integration testing

Good portfolio demonstrability

```

---

## 8. Consequences

Positive:

```text

Lower infrastructure complexity

Simpler development

Simpler local deployment

Simpler debugging

Straightforward database transactions

```

Trade-offs:

```text

Components share one process

Independent horizontal scaling is limited

Component isolation is logical rather than network-enforced

```

These trade-offs are acceptable for V1.0.0.

---

## 9. Alternatives Considered

Rejected:

```text

Microservices

Service-oriented distributed deployment

Serverless decomposition

```

Reason:

```text

Unnecessary for approved V1.0.0 scope.

```

---

## 10. Traceability

Supports:

```text

System Analysis logical decomposition

Pragmatic V1.0.0 architecture principle

Maintainability requirements

Deployment simplicity

Testing feasibility

```

Decision:

```text

ACCEPTED

```

---

# ADR-002 — Laravel as Application Framework

## 11. Context

The project already uses:

```text

Laravel 12

PHP 8.4

```

The required capabilities include:

```text

Routing

Authentication

Session handling

Validation

Authorization

Database persistence

HTTP responses

Middleware

Logging

Testing

```

---

## 12. Decision

Laravel 12 will be the primary application framework for GitForge V1.0.0.

Laravel will host:

```text

Web routes

Application/API routes

Session authentication

Authorization enforcement

Repository services

PAT management

Git Smart HTTP endpoints

Persistence integration

Operational logging

```

---

## 13. Rationale

Laravel provides mature support for the application-facing concerns while allowing dedicated components for Git-native operations.

---

## 14. Consequences

Laravel conventions may be used where they preserve architectural clarity.

Framework constructs must not become substitutes for explicit domain and security decisions.

---

## 15. Alternatives Considered

Not selected for V1.0.0:

```text

Custom PHP framework

Symfony

Node.js backend

Python backend

Java backend

```

Reason:

```text

Laravel is already established in the project and sufficiently supports approved requirements.

```

Decision:

```text

ACCEPTED

```

---

# ADR-003 — Single Primary Application Database

## 16. Context

The application requires authoritative metadata for:

```text

Users

Repositories

Ownership

Visibility

Personal Access Tokens

PAT revocation

Application timestamps

```

M2 established PostgreSQL as application metadata authority.

---

## 17. Decision

GitForge V1.0.0 will use:

```text

PostgreSQL 18

```

as the single primary relational database for application metadata.

---

## 18. Rationale

A single relational database provides:

```text

Strong integrity constraints

Transactional behavior

Foreign keys

Unique constraints

Indexing

Reliable ownership relationships

Simple deployment

```

---

## 19. Consequences

Application metadata transactions remain centralized.

Git object data will not be stored as application database records.

Decision:

```text

ACCEPTED

```

---

# ADR-004 — Git-Native State Remains Outside PostgreSQL

## 20. Context

Git repositories contain native structures:

```text

Refs

Branches

Tags

Commits

Trees

Blobs

History

File content

```

M2 established physical Git repositories as authoritative for these structures.

---

## 21. Decision

GitForge will not replicate Git-native repository state into PostgreSQL as an authoritative copy.

PostgreSQL will contain repository metadata only.

Physical Git repositories remain authoritative for Git-native content.

---

## 22. Rationale

This avoids:

```text

Duplicate sources of truth

Synchronization complexity

Stale Git metadata

Database bloat

Custom Git object modeling

```

---

## 23. Consequences

Repository browsing operations must query Git-native data through controlled Git operations or equivalent safe Git access.

Application metadata queries use PostgreSQL.

Decision:

```text

ACCEPTED

```

---

# ADR-005 — Bare Git Repositories for Hosted Storage

## 24. Context

GitForge is a Git hosting platform, not a developer working-directory manager.

Hosted repositories must support:

```text

Clone

Fetch

Push

Branch/ref access

Commit/tree/blob inspection

```

A server-side working tree is unnecessary.

---

## 25. Decision

Hosted repositories will be stored as:

```text

Bare Git repositories

```

under a configured GitForge repository root.

Conceptually:

```text

<repository-root>/

     <internal-repository-location>.git

```

The exact physical mapping is defined in the repository-storage design.

---

## 26. Rationale

Bare repositories are appropriate for Git hosting because they:

```text

Contain repository database directly

Do not maintain checked-out working trees

Support upload-pack

Support receive-pack

Reduce unnecessary filesystem state

Match standard Git server behavior

```

---

## 27. Consequences

Repository browsing must read Git-native objects rather than filesystem working-copy files.

Decision:

```text

ACCEPTED

```

---

# ADR-006 — Logical Namespace Separated from Physical Path

## 28. Context

The externally meaningful repository namespace is:

```text

<owner>/<repository>

```

This must not become an uncontrolled filesystem path.

M2 explicitly requires safe logical-to-physical resolution.

---

## 29. Decision

GitForge will maintain two distinct concepts:

```text

Logical Repository Identity

```

and:

```text

Physical Repository Location

```

External input will never directly specify an absolute or arbitrary physical repository path.

---

## 30. Rationale

This prevents:

```text

Path traversal

Repository-root escape

Filesystem probing

Unsafe direct path composition

Unexpected aliasing

```

---

## 31. Consequences

A dedicated repository-storage component must:

```text

Resolve repository metadata

Derive internal physical location

Normalize the path

Verify repository-root containment

Return only safe physical targets

```

Decision:

```text

ACCEPTED

```

---

# ADR-007 — Configured Repository Storage Root

## 32. Context

Physical Git repositories require a predictable storage boundary.

---

## 33. Decision

All hosted Git repositories will exist beneath one explicitly configured repository storage root.

Conceptually:

```text

GITFORGE_REPOSITORY_ROOT

```

All repository operations must remain within this boundary.

---

## 34. Rationale

A single controlled root simplifies:

```text

Containment validation

Filesystem permissions

Backup

Deployment

Diagnostics

Operational management

```

---

## 35. Consequences

Any resolved physical path outside the configured root is treated as:

```text

Security Failure

```

and Git execution must not occur.

Decision:

```text

ACCEPTED

```

---

# ADR-008 — Git Executable as Git-Native Execution Engine

## 36. Context

GitForge requires Git-native behavior for:

```text

Smart HTTP

Repository initialization

Ref inspection

Commit history

Tree inspection

Blob retrieval

Push validation

```

Reimplementing Git behavior inside the application would be unsafe and unnecessary.

---

## 37. Decision

GitForge will use the system Git executable as the authoritative Git-native execution engine.

Only explicitly approved Git operations may be executed.

---

## 38. Rationale

The Git executable provides:

```text

Protocol correctness

Repository-format compatibility

Git-native validation

Ref semantics

Object traversal

Pack protocol behavior

```

---

## 39. Consequences

Git process execution becomes a security-sensitive architectural boundary.

It requires:

```text

Controlled executable path

Controlled subcommands

Controlled arguments

Controlled environment

Safe repository target

Timeout

Exit-code handling

stdout/stderr handling

No arbitrary shell invocation

```

Decision:

```text

ACCEPTED

```

---

# ADR-009 — No Arbitrary Shell Execution

## 40. Context

Git process invocation receives inputs influenced by:

```text

HTTP requests

Git protocol requests

Repository identifiers

Branch/ref identifiers

```

Passing these through a general shell would increase command injection risk.

---

## 41. Decision

GitForge will not construct arbitrary shell command strings.

Git processes must be executed using structured process invocation with:

```text

Explicit executable

Explicit argument list

Controlled environment

Explicit working/target context

```

---

## 42. Rationale

This reduces:

```text

Shell injection

Argument interpretation ambiguity

Command chaining

Environment leakage

```

---

## 43. Consequences

Implementation must use a process API capable of argument-safe execution.

Decision:

```text

ACCEPTED

```

---

# ADR-010 — Git Smart HTTP as V1 Git Transport

## 44. Context

Approved V1.0.0 requirements include:

```text

Clone

Fetch

Push

```

using Git Smart HTTP.

SSH transport is excluded.

---

## 45. Decision

GitForge V1.0.0 will expose Git repository operations through:

```text

Git Smart HTTP

```

using:

```text

git-upload-pack

git-receive-pack

```

---

## 46. Rationale

Git Smart HTTP:

```text

Uses standard HTTP infrastructure

Works with existing Git clients

Supports authenticated requests

Avoids SSH key management

Matches approved scope

```

---

## 47. Consequences

The application must support:

```text

Service discovery

Protocol request forwarding

Content-type handling

Authentication

Authorization

Git subprocess streaming

Git-native failure propagation

```

Decision:

```text

ACCEPTED

```

---

# ADR-011 — Read and Write Git Services Remain Separate

## 48. Context

Git Smart HTTP has distinct read and write services.

Read:

```text

git-upload-pack

```

Write:

```text

git-receive-pack

```

Their authorization requirements differ significantly.

---

## 49. Decision

GitForge will model and enforce read and write Git services separately.

---

## 50. Rationale

Read behavior depends on repository visibility.

Write behavior always requires repository ownership.

Keeping them separate prevents accidental reuse of permissive read authorization for writes.

---

## 51. Consequences

Separate service handlers or explicitly separate execution paths will exist for:

```text

Upload Pack

Receive Pack

```

Decision:

```text

ACCEPTED

```

---

# ADR-012 — Session Authentication for Web UI

## 52. Context

Interactive browser users require:

```text

Registration

Login

Authenticated navigation

Logout

```

---

## 53. Decision

Web UI authentication will use Laravel session-based authentication.

---

## 54. Rationale

Session authentication is appropriate for a server-rendered or browser-oriented application and provides:

```text

Established Laravel integration

Secure cookie support

CSRF integration

Straightforward logout

Simple user experience

```

---

## 55. Consequences

Web session authentication remains separate from PAT authentication used by Git clients.

Decision:

```text

ACCEPTED

```

---

# ADR-013 — PAT Authentication for Git Clients

## 56. Context

Git clients require non-interactive credentials for private reads and all writes.

Approved requirements define Personal Access Tokens.

---

## 57. Decision

Git HTTP authentication will use:

```text

Personal Access Token

```

as the V1.0.0 Git credential.

The PAT resolves an authenticated GitForge user identity.

---

## 58. Rationale

PATs avoid:

```text

Direct account-password use by Git

SSH key management

Browser session coupling

```

and support explicit revocation.

---

## 59. Consequences

PAT authentication must include:

```text

Token candidate resolution

Secure verification

Revocation check

Owner identity resolution

Failure without identity establishment

```

Decision:

```text

ACCEPTED

```

---

# ADR-014 — PAT Plaintext Is One-Time Only

## 60. Context

Approved requirements state that PAT plaintext must not be recoverably persisted.

---

## 61. Decision

A generated PAT will be:

```text

Displayed once at creation

```

and only a secure verification representation will be stored afterward.

---

## 62. Rationale

A database compromise should not immediately expose usable plaintext PAT credentials.

---

## 63. Consequences

The system cannot later display the original PAT.

Users must create a replacement token if the value is lost.

Decision:

```text

ACCEPTED

```

---

# ADR-015 — PAT Scopes and Expiration Deferred

## 64. Context

Approved V1.0.0 requirements defer:

```text

PAT scopes

PAT expiration

```

---

## 65. Decision

The V1.0.0 PAT authorization model will not introduce token-specific scopes or expiration.

Authorization remains based on:

```text

Authenticated token owner

+

Repository ownership/visibility

+

Requested operation

```

---

## 66. Rationale

Adding scopes or expiration would exceed approved V1.0.0 scope and add unnecessary policy complexity.

Decision:

```text

ACCEPTED

```

---

# ADR-016 — Centralized Repository Authorization Policy

## 67. Context

Repository authorization must remain consistent across:

```text

Web repository view

Repository browser

Git clone

Git fetch

Git push

Visibility changes

```

Duplicated authorization logic creates drift risk.

---

## 68. Decision

GitForge will use a centralized repository authorization design.

The application will expose explicit policy decisions for:

```text

Read Repository

Write Repository

Manage Repository Metadata

```

---

## 69. Rationale

Centralization provides:

```text

Consistent rules

Testability

Reduced duplication

Auditability

Lower security drift

```

---

## 70. Consequences

Controllers and protocol handlers must not independently reimplement repository access rules.

Decision:

```text

ACCEPTED

```

---

# ADR-017 — Authorization Before Protected Storage Access

## 71. Context

M2 established that private repository access must be denied before protected content becomes available.

---

## 72. Decision

For protected repository operations:

```text

Authentication

       ↓

Repository Resolution

       ↓

Authorization

       ↓

Physical Storage Resolution

       ↓

Git/Content Operation

```

will be the architectural ordering.

Public read may establish public authorization without user authentication.

---

## 73. Rationale

This minimizes exposure of:

```text

Private repository paths

Repository existence details

Protected content

Git process behavior

```

---

## 74. Consequences

Protected storage and Git components receive requests only after authorization succeeds.

Decision:

```text

ACCEPTED

```

---

# ADR-018 — Owner-Only Repository Write Model

## 75. Context

V1.0.0 excludes:

```text

Collaborators

Teams

Organizations

Custom repository permissions

```

---

## 76. Decision

Repository writes will be authorized only when:

```text

Authenticated User ID

=

Repository Owner ID

```

---

## 77. Rationale

This directly implements approved V1.0.0 authorization semantics.

---

## 78. Consequences

No collaborator ACL table or write-role system is required.

Decision:

```text

ACCEPTED

```

---

# ADR-019 — Visibility Controls Read, Not Write

## 79. Context

Repository visibility determines public accessibility.

It must not grant write access.

---

## 80. Decision

Visibility affects repository read authorization only.

Write authorization remains owner-only for both:

```text

Public repositories

Private repositories

```

---

## 81. Rationale

Public Git hosting means public readability, not public mutability.

Decision:

```text

ACCEPTED

```

---

# ADR-020 — Repository Browser Uses Git-Native Reads

## 82. Context

Repository browser capabilities include:

```text

Branches

Commit history

Commit details

Trees

Files/blobs

```

These are Git-native structures.

---

## 83. Decision

Repository-browser data will be obtained from the authoritative physical Git repository through controlled Git-native read operations.

---

## 84. Rationale

This prevents creation of a secondary Git object database inside PostgreSQL.

---

## 85. Consequences

Repository browser components depend on:

```text

Repository authorization

Safe repository resolution

Git process execution

Safe result parsing/rendering

```

Decision:

```text

ACCEPTED

```

---

# ADR-021 — Repository Content Is Untrusted

## 86. Context

Repository owners can push arbitrary content.

Potentially hostile content includes:

```text

File names

Paths

Commit messages

Blob content

Markup

Source code

Binary content

```

---

## 87. Decision

All repository-derived content will be treated as untrusted when rendered or processed by the web application.

---

## 88. Rationale

Repository ownership does not imply content safety.

---

## 89. Consequences

Design must include:

```text

Output encoding

Safe text rendering

Binary-content handling

Content-size limits where needed

No implicit execution

No unsafe HTML interpretation

```

Decision:

```text

ACCEPTED

```

---

# ADR-022 — Thin HTTP Controllers

## 90. Context

Application responsibilities include complex business and security rules.

Embedding these directly in controllers would create:

```text

Duplication

Poor testability

Tight HTTP coupling

Authorization drift

```

---

## 91. Decision

HTTP controllers will remain thin.

They will primarily:

```text

Receive request

Trigger validation

Delegate application operation

Return response

```

Business, storage, authorization, PAT, and Git execution behavior will live in dedicated components.

---

## 92. Rationale

This improves:

```text

Separation of concerns

Testability

Reuse

Readability

Maintainability

```

Decision:

```text

ACCEPTED

```

---

# ADR-023 — Application Services Coordinate Use Cases

## 93. Context

Many operations span multiple responsibilities.

Example repository creation:

```text

Validate

Authorize

Persist metadata

Initialize Git repository

Handle partial failure

Return result

```

---

## 94. Decision

Use-case orchestration will be placed in dedicated application/service-layer components rather than controllers or database models.

---

## 95. Rationale

Application services provide explicit coordination boundaries for:

```text

Transactions

Failure handling

Authorization

Storage operations

Git operations

Persistence

```

Decision:

```text

ACCEPTED

```

---

# ADR-024 — Explicit Repository Storage Component

## 96. Context

Repository filesystem access is security-sensitive and reused by multiple features.

---

## 97. Decision

GitForge will have a dedicated repository-storage component responsible for:

```text

Repository root configuration

Logical-to-physical mapping

Containment validation

Repository initialization

Existence validation

Storage-state inspection

Safe target return

```

---

## 98. Rationale

Filesystem logic must not be scattered across controllers or Git handlers.

Decision:

```text

ACCEPTED

```

---

# ADR-025 — Explicit Git Process Component

## 99. Context

Multiple capabilities require safe Git process execution.

---

## 100. Decision

A dedicated Git process-execution component will be the only normal application boundary permitted to launch Git subprocesses.

---

## 101. Rationale

Centralization allows consistent enforcement of:

```text

Approved commands

Argument safety

Timeouts

Environment

Exit handling

Diagnostics

Secret suppression

```

Decision:

```text

ACCEPTED

```

---

# ADR-026 — Repository Creation Uses Coordinated Compensation

## 102. Context

Repository creation affects two authoritative resources:

```text

PostgreSQL metadata

Physical Git repository

```

These cannot participate in one native distributed transaction.

Partial success is possible.

---

## 103. Decision

Repository creation will use coordinated application-level failure handling with compensation.

The design must ensure:

```text

No false successful repository creation

```

when required storage or metadata operations fail.

---

## 104. Rationale

A distributed transaction coordinator is unjustified for V1.0.0.

Compensation is simpler and sufficient.

---

## 105. Consequences

The detailed repository-storage design must define:

```text

Operation ordering

Cleanup behavior

Failure reporting

Orphan prevention/recovery

```

Decision:

```text

ACCEPTED

```

---

# ADR-027 — Database Constraints Enforce Core Metadata Integrity

## 106. Context

Application-level validation alone is insufficient for critical invariants such as repository namespace uniqueness.

---

## 107. Decision

PostgreSQL constraints will enforce core metadata invariants where possible.

Examples:

```text

Repository owner foreign key

Unique owner/repository-name combination

Required visibility

PAT owner foreign key

Unique PAT lookup identifier where applicable

Required timestamps/state

```

---

## 108. Rationale

Database constraints protect integrity across concurrent requests and future implementation paths.

Decision:

```text

ACCEPTED

```

---

# ADR-028 — API Uses Explicit Validation Contracts

## 109. Context

Application endpoints receive untrusted user input.

---

## 110. Decision

Every mutating application endpoint will use explicit validation contracts before application-service execution.

Validation will cover relevant:

```text

Types

Required fields

Lengths

Formats

Allowed values

Repository naming rules

```

---

## 111. Rationale

This keeps malformed input away from business and persistence components.

Decision:

```text

ACCEPTED

```

---

# ADR-029 — Framework ORM for Application Metadata

## 112. Context

PostgreSQL contains application metadata with conventional relational relationships.

Laravel provides Eloquent ORM.

---

## 113. Decision

GitForge will use Laravel Eloquent for normal application metadata persistence.

Direct SQL may be used only where justified by a concrete design or performance requirement.

---

## 114. Rationale

Eloquent provides:

```text

Framework consistency

Relationship modeling

Transaction integration

Testability

Migration compatibility

```

Decision:

```text

ACCEPTED

```

---

# ADR-030 — Database Transactions for Metadata Operations

## 115. Context

Some application metadata operations require atomic changes.

---

## 116. Decision

Database transactions will be used where multiple database mutations form one logical application operation.

---

## 117. Rationale

This prevents partially persisted metadata states.

---

## 118. Limitation

Database transactions do not make filesystem operations transactional.

Filesystem compensation remains required where operations cross PostgreSQL and repository storage.

Decision:

```text

ACCEPTED

```

---

# ADR-031 — Web UI and Application Interfaces Share Core Services

## 119. Context

The web UI and Git Smart HTTP interfaces have different protocols but rely on overlapping capabilities:

```text

Repository resolution

Authorization

PAT identity

Storage

Git execution

```

---

## 120. Decision

Protocol-specific handlers will remain separate while reusing shared core application services.

Conceptually:

```text

Web UI/API

       ↓

Application Services

       ↑

Git Smart HTTP Handlers

```

with protocol-specific adaptation at the edges.

---

## 121. Rationale

This prevents protocol logic from duplicating domain/security behavior.

Decision:

```text

ACCEPTED

```

---

# ADR-032 — Git Protocol Responses Remain Protocol-Specific

## 122. Context

Git clients expect Git Smart HTTP protocol semantics, not conventional browser/API JSON behavior.

---

## 123. Decision

Git Smart HTTP endpoints will preserve required Git protocol:

```text

Content types

Service discovery format

Request body forwarding

Response body streaming

Status behavior where applicable

```

They will not be forced into ordinary application JSON response conventions.

---

## 124. Rationale

Protocol correctness is required for compatibility with standard Git clients.

Decision:

```text

ACCEPTED

```

---

# ADR-033 — Streaming Preferred for Git Protocol Payloads

## 125. Context

Git pack/protocol payloads may become larger than ordinary application responses.

Buffering entire payloads in application memory creates avoidable resource pressure.

---

## 126. Decision

Git Smart HTTP process input/output should use streaming where supported and practical.

---

## 127. Rationale

Streaming reduces:

```text

Memory amplification

Large-response buffering

Latency before transfer

```

---

## 128. Consequences

Detailed streaming mechanics are defined in Git process-execution design.

Decision:

```text

ACCEPTED

```

---

# ADR-034 — Explicit Process Timeout

## 129. Context

Git subprocesses may:

```text

Hang

Wait indefinitely

Consume resources

Encounter corrupted repositories

Receive problematic input

```

---

## 130. Decision

Every application-launched Git subprocess will have an explicit timeout policy.

---

## 131. Rationale

No external or repository-controlled operation should be able to occupy application resources indefinitely.

---

## 132. Consequences

Exact timeout values may vary by operation and will be defined in the Git process-execution design.

Decision:

```text

ACCEPTED

```

---

# ADR-035 — Explicit Git Process Environment

## 133. Context

Git behavior can be influenced by environment variables and host configuration.

---

## 134. Decision

GitForge will launch Git processes with an explicit, minimal, controlled environment.

Application behavior must not depend on arbitrary interactive shell configuration.

---

## 135. Rationale

This improves:

```text

Predictability

Security

Portability

Testing

Deployment consistency

```

Decision:

```text

ACCEPTED

```

---

# ADR-036 — Fail-Closed Security Architecture

## 136. Context

M2 requires fail-closed behavior for uncertain security-sensitive states.

---

## 137. Decision

The architecture will deny or fail whenever it cannot establish required:

```text

Identity

Authorization

Repository mapping

Storage safety

PAT validity

```

---

## 138. Rationale

Security uncertainty must never implicitly produce access.

Decision:

```text

ACCEPTED

```

---

# ADR-037 — No Application-Level Administrator Role

## 139. Context

V1.0.0 does not require an application administrator role.

A System Operator exists only for operational responsibilities.

---

## 140. Decision

No admin application role, admin ACL system, or privileged repository bypass will be designed for V1.0.0.

---

## 141. Rationale

Introducing one would expand scope and alter authorization semantics.

Decision:

```text

ACCEPTED

```

---

# ADR-038 — System Operator Outside Normal Repository Authorization

## 142. Context

Operational personnel may need to:

```text

Operate deployment

Inspect logs

Manage backups

Diagnose infrastructure

```

This does not imply normal application access to private repositories.

---

## 143. Decision

System Operator responsibilities remain an operational deployment concern and do not become a repository authorization role.

---

## 144. Rationale

Operational access and application authorization are separate trust domains.

Decision:

```text

ACCEPTED

```

---

# ADR-039 — Structured Application Logging

## 145. Context

V1.0.0 requires diagnosable failures across:

```text

HTTP

Database

Storage

Git execution

Authentication

Authorization

```

---

## 146. Decision

Application diagnostics will use structured logging through the Laravel logging infrastructure.

Logs should include safe context such as:

```text

Request/correlation identifier

Operation type

Repository identifier where safe

Authenticated user ID where appropriate

Failure category

Git process exit status where appropriate

```

---

## 147. Rationale

Structured logs improve troubleshooting without requiring an enterprise observability platform.

Decision:

```text

ACCEPTED

```

---

# ADR-040 — Sensitive Data Excluded from Logs

## 148. Context

Diagnostics may encounter credentials and internal security-sensitive values.

---

## 149. Decision

Logs must not intentionally include:

```text

Plaintext password

Plaintext PAT

Raw Authorization header

Application secret

Database password

Session secret

Unnecessary repository content

Unnecessary absolute filesystem paths

```

---

## 150. Rationale

Diagnostics must not become a secondary credential store.

Decision:

```text

ACCEPTED

```

---

# ADR-041 — Application-Level Error Mapping

## 151. Context

Internal failures may expose sensitive implementation detail if returned directly to clients.

---

## 152. Decision

GitForge will separate:

```text

Internal diagnostic detail

```

from:

```text

External client error representation

```

---

## 153. Rationale

Users need actionable errors without exposure of:

```text

Stack traces

Filesystem internals

Database details

Secrets

Unsafe process output

```

Decision:

```text

ACCEPTED

```

---

# ADR-042 — Framework Password Hashing

## 154. Context

User passwords require secure one-way verification.

---

## 155. Decision

GitForge will use Laravel-supported password hashing APIs rather than custom cryptography.

---

## 156. Rationale

Application-defined password cryptography introduces unnecessary risk.

Decision:

```text

ACCEPTED

```

---

# ADR-043 — Framework CSRF Protection for Session-Based Mutations

## 157. Context

Session-authenticated browser mutations are susceptible to CSRF.

---

## 158. Decision

Laravel CSRF protection will be preserved for session-authenticated state-changing web requests.

---

## 159. Rationale

This directly addresses cross-site request forgery in browser-session workflows.

Decision:

```text

ACCEPTED

```

---

# ADR-044 — Database Parameterization Through Framework Persistence

## 160. Context

Application input must not become executable SQL.

---

## 161. Decision

Normal database operations will use Eloquent/query-builder parameterization.

Raw SQL string concatenation with untrusted values is prohibited.

---

## 162. Rationale

This reduces SQL injection risk.

Decision:

```text

ACCEPTED

```

---

# ADR-045 — Output Encoding for Web Rendering

## 163. Context

User and repository-controlled text can contain executable markup.

---

## 164. Decision

Web rendering will encode untrusted textual output by default.

Raw HTML rendering requires an explicit, reviewed need and safe handling.

---

## 165. Rationale

This reduces XSS risk from:

```text

Repository names

Commit messages

File names

Repository content

Other user-controlled values

```

Decision:

```text

ACCEPTED

```

---

# ADR-046 — REST-Oriented Application Endpoints

## 166. Context

V1.0.0 application operations require predictable web/API interactions.

---

## 167. Decision

Non-Git application endpoints will follow REST-oriented semantics where practical.

Resource-oriented areas include:

```text

Repositories

Repository visibility

Repository browser resources

Personal Access Tokens

Authentication/session actions

```

---

## 168. Rationale

REST-oriented contracts provide predictable routing and HTTP semantics without forcing every action into strict REST purity.

Decision:

```text

ACCEPTED

```

---

# ADR-047 — No Repository Deletion Design in V1.0.0

## 169. Context

Repository deletion is explicitly deferred.

---

## 170. Decision

M3 will not design an executable repository deletion workflow.

Database and storage decisions must avoid requiring deletion as part of normal V1.0.0 operation.

---

## 171. Rationale

Designing deferred functionality would increase scope and introduce destructive-operation complexity.

Decision:

```text

ACCEPTED

```

---

# ADR-048 — No Repository Rename or Transfer Design

## 172. Context

Repository rename and transfer are deferred.

---

## 173. Decision

Repository identity is treated as stable for V1.0.0 after creation.

No rename or owner-transfer workflow will be implemented.

---

## 174. Rationale

This simplifies:

```text

Namespace stability

Physical mapping

URLs

Authorization

Storage design

```

Decision:

```text

ACCEPTED

```

---

# ADR-049 — Deployment as a Small Number of Runtime Units

## 175. Context

The project needs realistic deployment without unnecessary orchestration.

---

## 176. Decision

V1.0.0 deployment will use a small set of runtime responsibilities:

```text

Web Server / Reverse Proxy

Laravel/PHP Runtime

PostgreSQL

Repository Filesystem

Git Executable

```

These may reside on a single host in the initial deployment profile, while remaining conceptually separated.

---

## 177. Rationale

This provides sufficient realism while maintaining deployment simplicity.

Decision:

```text

ACCEPTED

```

---

# ADR-050 — Persistent Repository Storage Required

## 178. Context

Physical Git repositories are authoritative system data.

Ephemeral application storage would cause repository loss.

---

## 179. Decision

The repository root must use persistent storage independent of application process lifetime.

---

## 180. Rationale

Repository storage must survive:

```text

Application restart

PHP process restart

Deployment

Web server restart

```

Decision:

```text

ACCEPTED

```

---

# ADR-051 — Persistent PostgreSQL Storage Required

## 181. Context

PostgreSQL contains authoritative application metadata.

---

## 182. Decision

PostgreSQL data must use persistent database storage.

---

## 183. Rationale

Application metadata must survive runtime lifecycle events.

Decision:

```text

ACCEPTED

```

---

# ADR-052 — Secrets Provided Through Deployment Configuration

## 184. Context

The application requires secrets such as:

```text

Application key

Database credentials

Environment-specific configuration

```

---

## 185. Decision

Secrets will be supplied through deployment/environment configuration and will not be committed to source control.

---

## 186. Rationale

Source repositories must not become a secret-distribution mechanism.

Decision:

```text

ACCEPTED

```

---

# ADR-053 — Explicit Health Verification

## 187. Context

Operational deployment requires a way to determine whether major dependencies are available.

---

## 188. Decision

Deployment design will define lightweight health verification for relevant:

```text

Application runtime

Database connectivity

Required repository-storage availability

```

Health checks must not expose sensitive internals.

---

## 189. Rationale

This improves basic operational diagnostics without introducing a large monitoring platform.

Decision:

```text

ACCEPTED

```

---

# ADR-054 — Backups Treat Database and Git Storage as Separate Authorities

## 190. Context

GitForge contains two independent persistent authorities:

```text

PostgreSQL

Physical Git repositories

```

---

## 191. Decision

Backup design must account for both persistent authorities.

Neither one alone represents a complete GitForge installation.

---

## 192. Rationale

Losing either:

```text

Application metadata

```

or:

```text

Git-native repository state

```

creates incomplete or inconsistent recovery.

Decision:

```text

ACCEPTED

```

---

# ADR-055 — Architecture Avoids Distributed Cache Dependency

## 193. Context

A distributed cache such as Redis could add:

```text

Session storage

Caching

Queues

Locks

```

but is not required by approved V1.0.0 behavior.

---

## 194. Decision

Redis or another distributed cache is not a mandatory architecture dependency for V1.0.0.

It may be introduced later only if a concrete requirement justifies it.

---

## 195. Rationale

This reduces unnecessary runtime dependencies.

Decision:

```text

ACCEPTED

```

---

# ADR-056 — No Mandatory Background Queue for Core Operations

## 196. Context

Core V1.0.0 operations require immediate user-visible results:

```text

Repository creation

Repository browsing

PAT management

Clone

Fetch

Push

```

---

## 197. Decision

Core V1.0.0 workflows will not depend on asynchronous background queues.

---

## 198. Rationale

Synchronous behavior simplifies:

```text

Consistency

Failure handling

Testing

Deployment

```

for the current scope.

Decision:

```text

ACCEPTED

```

---

# ADR-057 — No Event-Driven Architecture Requirement

## 199. Context

The approved system does not require independent downstream consumers or complex integration workflows.

---

## 200. Decision

GitForge V1.0.0 will not require an event bus or event-driven distributed architecture.

Internal framework events may be used for local concerns where useful, but they are not an architectural dependency.

---

## 201. Rationale

This avoids infrastructure and consistency complexity not justified by current requirements.

Decision:

```text

ACCEPTED

```

---

# ADR-058 — Central Configuration for Security-Critical Paths and Executables

## 202. Context

Git executable and repository root are security-sensitive deployment values.

---

## 203. Decision

Security-critical environment-specific values will be configured centrally.

Examples:

```text

Git executable path

Repository root

Database connection

Application environment

Application secret

Process timeout defaults

```

---

## 204. Rationale

Scattered configuration increases drift and unsafe assumptions.

Decision:

```text

ACCEPTED

```

---

# ADR-059 — Repository Name Is Not the Sole Physical Identifier

## 205. Context

The external repository namespace contains owner and repository name.

Future safety and collision prevention should not rely on uncontrolled path concatenation.

---

## 206. Decision

Physical repository mapping will be derived internally from authoritative repository identity and validated metadata.

The repository-storage design will determine the exact representation.

---

## 207. Rationale

This allows physical storage safety to evolve without changing external repository identity.

Decision:

```text

ACCEPTED

```

---

# ADR-060 — Database IDs as Internal Entity Identity

## 208. Context

Usernames or repository names are human-readable identifiers but may be unsuitable as primary relational identity.

---

## 209. Decision

Application entities will use database primary keys as internal relational identity.

Human-readable names remain domain identifiers and route/namespace values where required.

---

## 210. Rationale

Internal keys provide:

```text

Stable relationships

Efficient foreign keys

Separation from mutable presentation data

Predictable ORM behavior

```

Even though repository rename is deferred, this remains structurally cleaner.

Decision:

```text

ACCEPTED

```

---

# ADR-061 — Unique Repository Namespace Enforced in Database

## 211. Context

Repository names must be unique within an owner namespace.

Concurrent repository creation could bypass application-only checks.

---

## 212. Decision

The database schema will enforce uniqueness equivalent to:

```text

(owner_id, repository_name)

```

---

## 213. Rationale

Database uniqueness is the final concurrency-safe metadata invariant.

Decision:

```text

ACCEPTED

```

---

# ADR-062 — Explicit Visibility Representation

## 214. Context

Only two visibility states are approved:

```text

Public

Private

```

---

## 215. Decision

Repository visibility will be represented explicitly in application metadata using a constrained value.

The exact database representation will be defined in database schema design.

---

## 216. Rationale

Visibility must not rely on nullable or ambiguous semantics.

Decision:

```text

ACCEPTED

```

---

# ADR-063 — Git Failure Remains Distinct from Authorization Failure

## 217. Context

A Git operation can fail after successful authentication and authorization.

Examples:

```text

Invalid ref update

Non-fast-forward rejection

Repository corruption

Git execution error

Protocol failure

```

---

## 218. Decision

Git-native failures will remain distinct from:

```text

Authentication Failure

Authorization Denial

```

---

## 219. Rationale

This preserves accurate failure semantics and diagnostics.

Decision:

```text

ACCEPTED

```

---

# ADR-064 — Empty Repository Is a Valid Repository State

## 220. Context

A newly created bare repository may have no commits or refs.

---

## 221. Decision

An empty physical repository is considered a valid repository.

The application and Git interfaces must not classify it as missing or corrupt solely because it has no history.

---

## 222. Rationale

This supports repository creation before first push.

Decision:

```text

ACCEPTED

```

---

# ADR-065 — Missing Storage Is a Consistency Failure

## 223. Context

Application metadata may reference a repository whose physical Git storage is unexpectedly absent.

---

## 224. Decision

If authoritative repository metadata exists but expected Git storage does not:

```text

Repository Storage Consistency Failure

```

will be raised.

The application must not silently create replacement storage during ordinary read/write operations.

---

## 225. Rationale

Silent recreation could hide data loss.

Decision:

```text

ACCEPTED

```

---

# ADR-066 — No Implicit Repair During User Requests

## 226. Context

Operational inconsistencies may require administrator intervention.

Automatically repairing storage during normal user traffic could destroy diagnostic evidence or create false state.

---

## 227. Decision

Normal application requests will detect and fail on repository metadata/storage inconsistency rather than implicitly repairing it.

---

## 228. Rationale

Repair is an operational concern and should be explicit.

Decision:

```text

ACCEPTED

```

---

# ADR-067 — Repository Browser Is Read-Only

## 229. Context

V1.0.0 repository browser requirements concern viewing Git-native content.

---

## 230. Decision

Repository browser components will expose read-only Git operations.

They will not modify:

```text

Refs

Commits

Trees

Blobs

Working trees

Repository configuration

```

---

## 231. Rationale

Browser functionality must not accidentally become a repository mutation surface.

Decision:

```text

ACCEPTED

```

---

# ADR-068 — UI Architecture Remains Server-Oriented and API-Compatible

## 232. Context

The application needs a usable web UI while retaining clean application interfaces.

---

## 233. Decision

The V1.0.0 UI may use Laravel-rendered views and JavaScript-enhanced interactions while application logic remains behind reusable services and explicit routes/contracts.

A separate frontend microservice or SPA architecture is not required.

---

## 234. Rationale

This keeps deployment and authentication simple while preserving future API evolution.

Decision:

```text

ACCEPTED

```

---

# ADR-069 — No Separate Identity Service

## 235. Context

The application has one user domain and no enterprise federation requirement.

---

## 236. Decision

Authentication and identity remain within the Laravel application and PostgreSQL database.

No separate identity microservice is introduced.

---

## 237. Rationale

A dedicated identity service would add unnecessary operational and network complexity.

Decision:

```text

ACCEPTED

```

---

# ADR-070 — No Separate Git Storage Service in V1.0.0

## 238. Context

A separate Git storage daemon could provide isolation and independent scaling.

However, V1.0.0 is a single-system portfolio deployment.

---

## 239. Decision

Repository storage and Git execution will remain local architectural dependencies of the Laravel application deployment rather than a separate network service.

---

## 240. Rationale

This keeps:

```text

Deployment simple

Authorization integration direct

Storage mapping straightforward

Testing manageable

```

---

## 241. Future Implication

The internal repository-storage and Git-process boundaries should remain explicit enough that a future version could extract them if justified.

Decision:

```text

ACCEPTED

```

---

# Architecture Security Invariants

## 242. Authentication Invariant

```text

No unverified credential establishes identity.

```

---

## 243. Authorization Invariant

```text

Authentication never automatically grants repository access.

```

---

## 244. Public Read Invariant

```text

Public Repository + Read Operation

=

Allowed for Visitor, Authenticated Non-Owner, and Owner

```

---

## 245. Private Read Invariant

```text

Private Repository + Read Operation

=

Owner only

```

---

## 246. Write Invariant

```text

Repository Write

=

Authenticated Owner only

```

---

## 247. Storage Invariant

```text

No Git operation receives an unvalidated physical repository target.

```

---

## 248. Process Invariant

```text

No user-controlled arbitrary command is executed.

```

---

## 249. Data Authority Invariant

```text

PostgreSQL

≠

Git object database

```

and:

```text

Physical Git repository

≠

Application metadata database

```

---

## 250. PAT Invariant

```text

Plaintext PAT is not recoverably persisted.

```

---

## 251. Logging Invariant

```text

Credentials and application secrets are not intentionally logged.

```

---

## 252. Failure Invariant

```text

Security-sensitive uncertainty fails closed.

```

---

# Architecture Dependency Direction

## 253. Preferred Dependency Direction

Conceptually:

```text

HTTP / Git Protocol Adapters

             ↓

Application Services

             ↓

Domain / Authorization Rules

             ↓

Infrastructure Ports

        ↙           ↘

PostgreSQL      Git/Storage

```

Protocol adapters should not contain core authorization or repository-storage policy.

---

## 254. Infrastructure Boundary

Infrastructure-specific concerns include:

```text

Eloquent/PostgreSQL

Filesystem access

Git process execution

Laravel HTTP/session mechanisms

Logging framework

Deployment configuration

```

Core application behavior should interact with these through clear responsibility boundaries.

---

# Architecture Failure Boundaries

## 255. Database Failure Boundary

Database failure must not become:

```text

Successful repository operation

```

---

## 256. Storage Failure Boundary

Storage failure must stop:

```text

Repository initialization

Repository browsing

Git read

Git write

```

when storage is required.

---

## 257. Git Process Failure Boundary

Git process failure must propagate as an operation failure without false success.

---

## 258. Authorization Failure Boundary

Authorization denial must stop protected:

```text

Storage resolution

Repository-content access

Git write

Private Git read

Metadata mutation

```

---

## 259. Protocol Failure Boundary

Malformed Git protocol input must not escape into arbitrary process execution or application internals.

---

# Architecture Scope Preservation

## 260. Explicitly Excluded

Architecture does not introduce:

```text

Microservices

Organizations

Teams

Collaborator ACLs

Forks

Pull requests

Issues

Project boards

CI/CD platform

Wiki

Package registry

Container registry

Git LFS

SSH transport

Billing

Enterprise identity federation

Multi-region deployment

Distributed repository replication

Analytics platform

Social features

```

---

## 261. Explicitly Deferred

Architecture does not require:

```text

Repository deletion

Repository rename

Repository transfer

Repository description

Account deletion

PAT scopes

PAT expiration

```

---

# Architecture Decision Summary

## 262. Decision Matrix

| ADR | Decision | Status |

|---|---|---|

| ADR-001 | Modular Monolith | Accepted |

| ADR-002 | Laravel Application Framework | Accepted |

| ADR-003 | PostgreSQL Application Database | Accepted |

| ADR-004 | Git-Native State Outside PostgreSQL | Accepted |

| ADR-005 | Bare Git Repositories | Accepted |

| ADR-006 | Logical Namespace Separate from Physical Path | Accepted |

| ADR-007 | Configured Repository Root | Accepted |

| ADR-008 | System Git Executable | Accepted |

| ADR-009 | No Arbitrary Shell Execution | Accepted |

| ADR-010 | Git Smart HTTP Transport | Accepted |

| ADR-011 | Separate Git Read/Write Services | Accepted |

| ADR-012 | Session Authentication for Web UI | Accepted |

| ADR-013 | PAT Authentication for Git | Accepted |

| ADR-014 | One-Time PAT Plaintext | Accepted |

| ADR-015 | PAT Scopes/Expiration Deferred | Accepted |

| ADR-016 | Centralized Repository Authorization | Accepted |

| ADR-017 | Authorization Before Protected Storage | Accepted |

| ADR-018 | Owner-Only Repository Write | Accepted |

| ADR-019 | Visibility Controls Read Only | Accepted |

| ADR-020 | Git-Native Repository Browser Reads | Accepted |

| ADR-021 | Repository Content Is Untrusted | Accepted |

| ADR-022 | Thin HTTP Controllers | Accepted |

| ADR-023 | Application Services Coordinate Use Cases | Accepted |

| ADR-024 | Explicit Repository Storage Component | Accepted |

| ADR-025 | Explicit Git Process Component | Accepted |

| ADR-026 | Repository Creation Compensation | Accepted |

| ADR-027 | Database Integrity Constraints | Accepted |

| ADR-028 | Explicit API Validation | Accepted |

| ADR-029 | Eloquent for Metadata Persistence | Accepted |

| ADR-030 | Database Transactions | Accepted |

| ADR-031 | Shared Core Services Across Protocols | Accepted |

| ADR-032 | Protocol-Specific Git Responses | Accepted |

| ADR-033 | Git Payload Streaming | Accepted |

| ADR-034 | Git Process Timeout | Accepted |

| ADR-035 | Controlled Git Process Environment | Accepted |

| ADR-036 | Fail-Closed Architecture | Accepted |

| ADR-037 | No Application Admin Role | Accepted |

| ADR-038 | System Operator Outside Repository ACL | Accepted |

| ADR-039 | Structured Application Logging | Accepted |

| ADR-040 | Sensitive Data Excluded from Logs | Accepted |

| ADR-041 | Application-Level Error Mapping | Accepted |

| ADR-042 | Framework Password Hashing | Accepted |

| ADR-043 | Framework CSRF Protection | Accepted |

| ADR-044 | Parameterized Database Access | Accepted |

| ADR-045 | Output Encoding | Accepted |

| ADR-046 | REST-Oriented Application Endpoints | Accepted |

| ADR-047 | No Repository Deletion Design | Accepted |

| ADR-048 | No Repository Rename/Transfer Design | Accepted |

| ADR-049 | Small Runtime Deployment Model | Accepted |

| ADR-050 | Persistent Repository Storage | Accepted |

| ADR-051 | Persistent PostgreSQL Storage | Accepted |

| ADR-052 | Deployment-Provided Secrets | Accepted |

| ADR-053 | Explicit Health Verification | Accepted |

| ADR-054 | Dual-Authority Backup Model | Accepted |

| ADR-055 | No Mandatory Distributed Cache | Accepted |

| ADR-056 | No Mandatory Background Queue | Accepted |

| ADR-057 | No Event-Driven Architecture Requirement | Accepted |

| ADR-058 | Central Security-Critical Configuration | Accepted |

| ADR-059 | Internal Physical Repository Mapping | Accepted |

| ADR-060 | Database IDs as Internal Identity | Accepted |

| ADR-061 | Database-Enforced Repository Namespace Uniqueness | Accepted |

| ADR-062 | Explicit Visibility Representation | Accepted |

| ADR-063 | Git Failure Separate from Authorization Failure | Accepted |

| ADR-064 | Empty Repository Is Valid | Accepted |

| ADR-065 | Missing Storage Is Consistency Failure | Accepted |

| ADR-066 | No Implicit Repair During Requests | Accepted |

| ADR-067 | Repository Browser Is Read-Only | Accepted |

| ADR-068 | Server-Oriented API-Compatible UI | Accepted |

| ADR-069 | No Separate Identity Service | Accepted |

| ADR-070 | No Separate Git Storage Service | Accepted |

---

# Architecture Validation

## 263. Requirement Alignment

Architecture decisions preserve approved V1.0.0 requirements.

Result:

```text

PASS

```

---

## 264. System Analysis Alignment

Architecture decisions preserve M2 conclusions regarding:

```text

Authentication

Authorization

Repository lifecycle

Repository browser

Git read

Git write

PAT lifecycle

Repository storage

Data authority

Trust boundaries

Failure behavior

```

Result:

```text

PASS

```

---

## 265. Security Alignment

Architecture preserves:

```text

Fail-closed behavior

Owner-only writes

Private owner-only reads

Public reads

Safe path resolution

Controlled Git execution

Credential confidentiality

Untrusted repository content

```

Result:

```text

PASS

```

---

## 266. Scope Alignment

Unapproved functionality introduced:

```text

0

```

Result:

```text

PASS

```

---

## 267. Implementation Suitability

The architecture is sufficiently concrete to proceed to:

```text

Container Architecture

Component Design

Database Design

API Design

Storage Design

Git Execution Design

```

Result:

```text

READY

```

---

# Architecture Decision Completion

## 268. Completion Criteria

This architecture decision document is complete when:

- architecture style is selected;

- framework role is defined;

- application metadata authority is defined;

- Git-native authority is defined;

- physical repository form is defined;

- logical and physical repository identities are separated;

- repository-root boundary is defined;

- Git execution approach is defined;

- arbitrary shell execution is prohibited;

- Git transport is defined;

- authentication mechanisms are defined;

- authorization architecture is defined;

- repository-browser approach is defined;

- PAT architecture is defined;

- persistence approach is defined;

- repository creation consistency strategy is defined;

- process safety principles are defined;

- deployment architecture direction is defined;

- observability direction is defined;

- security invariants are defined;

- excluded/deferred scope remains preserved;

- no blocking architecture decision remains unresolved.

---

## 269. Architecture Decision Result

Current architecture decision result:

```text

ARCHITECTURE DECISIONS DEFINED

```

Blocking architecture decisions:

```text

0

```

Architecture readiness for detailed design:

```text

READY

```

Next design artifact:

```text

03-container-architecture.md

```

---

## 270. Document Status

This document is currently:

```text

Approved

```

It will be changed to:

```text

Approved

```

after architecture style, framework, data authority, repository storage, Git execution, authentication, authorization, security, deployment, operational, failure, and scope decisions have been reviewed.
