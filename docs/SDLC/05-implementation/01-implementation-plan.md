# GitForge V1.0.0 Implementation Plan

## 1. Document Information

| Field | Value |
|---|---|
| Project | GitForge - Web Git Hosting Platform |
| Target release | V1.0.0 |
| Document | Implementation Plan |
| SDLC phase | Implementation |
| Milestone | M4 - Implementation |
| Project owner | Farhad Valizadegan |
| Status | Approved |
| Last updated | 2026-08-18 |

---

## 2. Purpose

This document defines the implementation plan for GitForge V1.0.0.

M4 converts the approved System Design into working application code while preserving:

```text

Approved requirements

Approved architecture

Approved authorization rules

Approved database design

Approved API contracts

Approved repository-storage design

Approved Git process-execution design

Approved UI structure

Approved deployment design

Approved observability design

Approved security design

Approved design traceability

```

Implementation must not silently redefine approved product behavior.

---

## 3. SDLC Position

GitForge follows:

```text

1. Project Definition           ✅ Complete

2. Requirements Engineering    ✅ Complete

3. System Analysis             ✅ Complete

4. System Design               ✅ Complete

5. Implementation              🟡 M4 In Progress

6. Testing

7. Deployment

8. Maintenance

```

---

## 4. Milestone

```text

M4 — Implementation

```

Phase:

```text

SDLC Phase 5 — Implementation

```

---

## 5. M4 Objective

The objective of M4 is to implement the approved GitForge V1.0.0 design as a working Laravel-based Git hosting platform.

The implementation must provide working code for:

```text

Application foundation

Database schema

Domain models

Authentication

Repository management

Repository authorization

Repository storage

Git process execution

Repository browser

Git Smart HTTP read

Personal Access Tokens

Git Smart HTTP write

Web UI

Observability

Security controls

Deployment configuration

Implementation traceability

Implementation validation

```

---

# Implementation Principles

## 6. Design-Driven Implementation

Implementation follows:

```text

Requirements

     ↓

System Analysis

     ↓

System Design

     ↓

Implementation

```

Code must trace back to approved design decisions.

---

## 7. No Silent Redesign

If implementation reveals a genuine design defect:

```text

Identify the defect

Document the deviation

Evaluate impact

Correct the relevant design artifact if necessary

Then implement

```

Implementation convenience alone is not sufficient reason to violate approved design.

---

## 8. Incremental Implementation

M4 is implemented in controlled work packages.

Each work package should be:

```text

Implemented

Locally verified

Tested where practical

Reviewed against design

Committed coherently

```

---

## 9. Clean Git History

Implementation commits should represent meaningful units of work.

Avoid:

```text

Random checkpoint commits

Broken intermediate commits

Unrelated changes mixed together

Generated junk

Temporary debug artifacts

Secrets

```

---

## 10. Working Tree Discipline

Before each implementation commit:

```text

git diff --check

Tests pass

No accidental files

No secrets

No unrelated changes

```

---

# Technology Baseline

## 11. Backend Framework

```text

Laravel 12

```

---

## 12. PHP

```text

PHP 8.4

```

---

## 13. Database

```text

PostgreSQL 18

```

---

## 14. Git Engine

```text

Configured system Git executable

```

---

## 15. Repository Storage

```text

Bare Git repositories

```

under a configured repository root.

---

## 16. Web Authentication

```text

Laravel session authentication

```

---

## 17. Git Authentication

```text

Personal Access Token

```

over Git Smart HTTP.

---

# Approved Architectural Constraints

## 18. Architectural Style

```text

Modular Monolith

```

---

## 19. Application Metadata Authority

```text

PostgreSQL

```

---

## 20. Git-Native Data Authority

```text

Physical Git repository

```

---

## 21. Repository Physical Mapping

```text

Configured repository root

+

Internal repository ID

```

Conceptually:

```text

<repository-root>/repositories/<repository-id>.git

```

---

## 22. Process Execution Boundary

All Git process execution goes through:

```text

Git Process Executor

```

---

## 23. Shell Prohibition

Normal Git operations must not invoke:

```text

cmd.exe /c

PowerShell -Command

sh -c

bash -c

```

---

## 24. Authorization Rule

Protected repository access requires authorization before:

```text

Repository storage access

Git process execution

```

---

# M4 Work Packages

## 25. WP-01 — Implementation Foundation

Purpose:

Prepare the Laravel application for implementation.

Includes:

```text

Application configuration

Environment configuration

Core service structure

Directory/module organization

Shared exceptions

Shared result/value objects

Configuration contracts

Development conventions

```

Primary design references:

```text

02-architecture-decisions.md

03-container-architecture.md

04-component-design.md

12-deployment-design.md

14-security-design.md

```

Exit condition:

```text

Application foundation supports subsequent domain implementation

```

---

## 26. WP-02 — Database Schema and Models

Includes:

```text

users

repositories

personal_access_tokens

Migrations

Eloquent models

Relationships

Database constraints

Indexes

```

Primary design reference:

```text

05-database-schema-design.md

```

Exit condition:

```text

Approved V1.0.0 metadata model exists and migrations execute successfully

```

---

## 27. WP-03 — Authentication

Includes:

```text

Registration

Login

Logout

Session handling

Password hashing

CSRF

Login rate limiting

Authentication validation

```

Primary references:

```text

04-component-design.md

06-api-specification.md

10-ui-information-architecture.md

11-wireframes.md

14-security-design.md

```

Exit condition:

```text

Users can register, authenticate, and logout according to approved behavior

```

---

## 28. WP-04 — Repository Management

Includes:

```text

Create repository

List owned repositories

View repository metadata

Change visibility

Repository-name validation

Namespace uniqueness

```

Primary references:

```text

04-component-design.md

05-database-schema-design.md

06-api-specification.md

10-ui-information-architecture.md

```

Exit condition:

```text

Repository metadata lifecycle required by V1.0.0 is implemented

```

---

## 29. WP-05 — Repository Authorization

Includes:

```text

AUTH-READ

AUTH-WRITE

AUTH-MANAGE

AUTH-PAT-MANAGE

Private repository concealment

Central repository policy

```

Approved matrix:

```text

Public read:

Anonymous                → Allow

Authenticated Non-Owner  → Allow

Owner                    → Allow

Private read:

Anonymous                → Deny

Authenticated Non-Owner  → Deny

Owner                    → Allow

Write:

Anonymous                → Deny

Authenticated Non-Owner  → Deny

Owner                    → Allow

```

Primary reference:

```text

07-authorization-design-matrix.md

```

Exit condition:

```text

Authorization behavior matches the approved matrix

```

---

## 30. WP-06 — Repository Storage

Includes:

```text

Configured repository root

Repository ID-based physical mapping

Root containment

Existing repository resolution

New repository target resolution

Bare repository initialization coordination

Missing-storage classification

Orphan/inconsistency handling

Safe compensation cleanup

```

Primary reference:

```text

08-repository-storage-design.md

```

Exit condition:

```text

Application can resolve and manage repository storage without accepting arbitrary client filesystem paths

```

---

## 31. WP-07 — Git Process Execution

Includes approved operation categories:

```text

INIT_BARE

UPLOAD_PACK_ADVERTISE

UPLOAD_PACK_RPC

RECEIVE_PACK_ADVERTISE

RECEIVE_PACK_RPC

LIST_REFS

COMMIT_HISTORY

COMMIT_DETAILS

TREE_LIST

BLOB_READ

REPOSITORY_VERIFY

```

Implementation must include:

```text

Configured Git binary

No shell

Structured arguments

Controlled environment

stdin handling

stdout handling

stderr handling

Timeout

Exit-code handling

Process-failure classification

```

Primary reference:

```text

09-git-process-execution-design.md

```

Exit condition:

```text

Approved Git operations can execute safely through one controlled component

```

---

## 32. WP-08 — Repository Browser

Includes:

```text

Branches

Commit history

Commit details

Tree listing

Blob/file reading

Empty repository handling

Binary-file handling

Large-file display limits

```

Primary references:

```text

04-component-design.md

06-api-specification.md

09-git-process-execution-design.md

10-ui-information-architecture.md

11-wireframes.md

```

Exit condition:

```text

Authorized users can browse Git-native repository data safely

```

---

## 33. WP-09 — Git Smart HTTP Read

Includes:

```text

git-upload-pack service discovery

git-upload-pack RPC

Anonymous public clone/fetch

PAT-authenticated private clone/fetch

Streaming protocol responses

Protocol-specific content types

```

Primary references:

```text

06-api-specification.md

07-authorization-design-matrix.md

09-git-process-execution-design.md

12-deployment-design.md

14-security-design.md

```

Exit condition:

```text

Real Git clients can clone/fetch according to approved public/private authorization rules

```

---

## 34. WP-10 — Personal Access Tokens

Includes:

```text

PAT creation

PAT listing

PAT revocation

Token identifier

Secret generation

Secure token hashing

One-time plaintext display

Revoked-token rejection

```

Excluded:

```text

PAT scopes

PAT expiration

```

Primary references:

```text

05-database-schema-design.md

06-api-specification.md

10-ui-information-architecture.md

11-wireframes.md

14-security-design.md

```

Exit condition:

```text

PAT lifecycle and authentication behavior match approved design

```

---

## 35. WP-11 — Git Smart HTTP Write

Includes:

```text

git-receive-pack service discovery

git-receive-pack RPC

PAT authentication

Owner-only authorization

Streaming push

Git-native rejection handling

```

Required authorization:

```text

Anonymous     → Deny

Non-Owner     → Deny

Owner         → Allow

```

Primary references:

```text

06-api-specification.md

07-authorization-design-matrix.md

09-git-process-execution-design.md

14-security-design.md

```

Exit condition:

```text

Repository owners can push using PAT authentication and unauthorized writes are rejected

```

---

## 36. WP-12 — Web UI

Includes approved screens:

```text

Home

Register

Login

My Repositories

Create Repository

Repository Overview

Files

Branches

Commits

Commit Details

Repository Settings

PAT List

PAT Create

PAT One-Time Secret

Error States

Empty States

```

Primary references:

```text

10-ui-information-architecture.md

11-wireframes.md

```

Exit condition:

```text

Required V1.0.0 browser workflows are available through the designed UI

```

---

## 37. WP-13 — Observability

Includes:

```text

Structured logging

Request IDs

Authentication events

Authorization events

Repository lifecycle events

Git process diagnostics

Storage diagnostics

Health

Readiness

Safe failure classification

```

Never log:

```text

Passwords

Plaintext PAT

PAT hash

Authorization header

APP_KEY

Database password

Repository blob contents

Git protocol payloads

```

Primary reference:

```text

13-observability-design.md

```

Exit condition:

```text

Core operations and failures are diagnosable without sensitive-data leakage

```

---

## 38. WP-14 — Security Hardening

Includes:

```text

Input validation

CSRF

Output encoding

SQL injection prevention

Path traversal protection

Root containment

Command injection protection

Git option injection protection

Secure sessions

PAT protection

HTTPS-oriented configuration

Least privilege

Safe errors

Resource limits

```

Primary reference:

```text

14-security-design.md

```

Exit condition:

```text

Approved security controls are implemented and verifiable

```

---

## 39. WP-15 — Deployment Configuration

Includes:

```text

Environment configuration

Repository-root configuration

Git-binary configuration

Web-server integration requirements

Database configuration

Health/readiness endpoints

Production-safe settings

```

Primary reference:

```text

12-deployment-design.md

```

Exit condition:

```text

Application is configuration-ready for the Deployment phase

```

---

## 40. WP-16 — Implementation Traceability

Purpose:

Map implemented code to approved:

```text

Requirements

Use cases

Design components

Routes

Models

Services

Policies

Storage adapters

Git adapters

Tests

```

Primary reference:

```text

15-design-traceability.md

```

Exit condition:

```text

Major implemented capabilities remain traceable to approved design

```

---

## 41. WP-17 — Implementation Validation

Validate:

```text

Required capabilities implemented

No deferred scope accidentally introduced

Application builds/runs

Migrations execute

Core tests pass

Git process integration works

Git Smart HTTP works

Security controls implemented

No blocking implementation defects

```

Exit condition:

```text

Implementation is ready for formal Testing phase

```

---

## 42. WP-18 — M4 Milestone Review

Formal M4 closure.

Must confirm:

```text

Implementation deliverables complete

Implementation exit criteria passed

Blocking defects = 0

Blocking implementation ambiguities = 0

Unapproved scope additions = 0

Testing readiness = READY

```

---

# Dependency Order

## 43. Primary Dependency Chain

```text

WP-01 Foundation

       ↓

WP-02 Database

       ↓

WP-03 Authentication

       ↓

WP-04 Repository Management

       ↓

WP-05 Authorization

       ↓

WP-06 Repository Storage

       ↓

WP-07 Git Process Execution

       ↓

WP-08 Repository Browser

       ↓

WP-09 Git Smart HTTP Read

       ↓

WP-10 PAT

       ↓

WP-11 Git Smart HTTP Write

       ↓

WP-12 UI

       ↓

WP-13 Observability

       ↓

WP-14 Security Hardening

       ↓

WP-15 Deployment Configuration

       ↓

WP-16 Traceability

       ↓

WP-17 Validation

       ↓

WP-18 Milestone Review

```

---

# Implementation Structure

## 44. Recommended Laravel Responsibility Structure

Conceptual structure:

```text

app/

├── Http/

│   ├── Controllers/

│   ├── Middleware/

│   └── Requests/

├── Models/

├── Policies/

├── Services/

│   ├── Authentication/

│   ├── Tokens/

│   ├── Repositories/

│   ├── RepositoryBrowser/

│   └── Git/

├── Support/

├── Exceptions/

└── Providers/

```

Exact namespaces may evolve during implementation while preserving approved component boundaries.

---

## 45. Storage-Oriented Services

Expected conceptual services include:

```text

RepositoryStorage

GitProcessExecutor

RepositoryService

RepositoryBrowserService

GitReadService

GitWriteService

PersonalAccessTokenService

```

---

## 46. Thin Controller Rule

Controllers should primarily perform:

```text

Receive request

Validate input

Resolve authenticated context

Invoke application service

Return response

```

They should not directly own:

```text

Git commands

Filesystem path construction

Complex authorization

Database transactions spanning business workflows

```

---

# Database Implementation Rules

## 47. Migrations

Database structure must be implemented through Laravel migrations.

---

## 48. Constraints

Important integrity rules must exist in the database where designed.

Examples:

```text

Repository owner FK

Repository owner/name uniqueness

Visibility restriction

PAT owner FK

PAT token identifier uniqueness

```

---

## 49. Transactions

Use database transactions where approved workflows require atomic metadata changes.

---

# Repository Creation Implementation

## 50. Creation Flow

Conceptually:

```text

Validate request

     ↓

Authenticate

     ↓

Create metadata in controlled workflow

     ↓

Resolve safe physical target

     ↓

Initialize bare Git repository

     ↓

Complete operation

```

---

## 51. Creation Failure

If physical Git initialization fails:

```text

Operation fails

Metadata compensation occurs

Partial safe storage cleanup occurs if appropriate

No success response is returned

```

---

# Git Process Implementation Rules

## 52. Process Invocation

Use a process API capable of:

```text

Executable separation

Argument separation

stdin/stdout/stderr handling

Timeout

Exit status

```

---

## 53. Prohibited Implementation

Do not implement Git operations using arbitrary strings such as:

```php

shell_exec("git " . $userInput);

```

or equivalent shell construction.

---

## 54. Controlled Operations

Each Git operation should correspond to an explicit application method.

Examples:

```text

initializeBare()

advertiseUploadPack()

uploadPack()

advertiseReceivePack()

receivePack()

listBranches()

commitHistory()

commitDetails()

listTree()

readBlob()

```

---

# Security Implementation Rules

## 55. Client Input

All client-controlled values remain untrusted.

---

d

## 56. Repository Content

All Git repository content remains untrusted.

---

## 57. Physical Paths

Only Repository Storage may derive authoritative physical repository paths.

---

## 58. Authorization

Storage or Git execution must not be used to infer authorization.

Authorization is decided first.

---

## 59. Secrets

Never commit:

```text

.env

Database password

APP_KEY

Plaintext PAT

Real production credentials

```

---

# Testing During Implementation

## 60. Implementation-Level Testing

Although formal SDLC Testing is Phase 6, M4 must still include developer verification.

This includes:

```text

Unit tests

Feature tests

Integration tests required to prove implementation correctness

```

Phase 6 will perform formal system-level testing and verification against the completed implementation.

---

## 61. Test-After-Work-Package Rule

Each implementation work package should finish with relevant automated tests before progressing where practical.

---

## 62. Critical Integration Tests During M4

Required before implementation closure:

```text

Registration

Login/logout

Repository creation

Repository authorization

Repository storage initialization

Repository browser

Public clone

Private clone

Fetch

PAT authentication

PAT revocation

Owner push

Non-owner push denial

Anonymous push denial

```

---

# Git Workflow

## 63. Current M4 Branch

```text

implementation/m4

```

Base:

```text

da3909a docs: complete M3 system design

```

---

## 64. Commit Strategy

Unlike documentation milestones, implementation should use multiple coherent commits.

Examples:

```text

chore: establish implementation foundation

feat: implement repository persistence

feat: implement repository authorization

feat: add repository storage service

feat: add controlled git process execution

feat: implement repository browser

feat: add git smart http read support

feat: implement personal access tokens

feat: add git smart http write support

feat: complete repository web interface

feat: add observability and security controls

```

---

## 65. Commit Gate

Before each commit:

```powershell

git diff --check

git status

```

Relevant tests must pass.

---

# Scope Control

## 66. Deferred Features

Do not implement:

```text

Repository deletion

Repository rename

Repository transfer

Account deletion

PAT scopes

PAT expiration

```

---

## 67. Excluded Features

Do not implement:

```text

Organizations

Teams

Collaborators

Forks

Pull Requests

Issues

Project boards

Wiki

CI/CD

Git LFS

SSH Git

Package registry

Container registry

Billing

Enterprise ACL

Federation

Social features

```

---

# Implementation Risks

## 68. RISK-01 — Git Process Safety

Risk:

```text

Unsafe process construction

```

Control:

```text

No shell

Operation allowlist

Structured arguments

```

---

## 69. RISK-02 — Repository Path Safety

Risk:

```text

Filesystem path traversal/root escape

```

Control:

```text

Internal ID mapping

Configured root

Containment verification

```

---

## 70. RISK-03 — Cross-Resource Creation Failure

Risk:

```text

Database and filesystem state diverge

```

Control:

```text

Compensation

Explicit failure handling

Consistency checks

```

---

## 71. RISK-04 — Git Protocol Integration

Risk:

```text

Clone/fetch/push interoperability failure

```

Control:

```text

Real Git client integration tests

Correct Smart HTTP protocol responses

Streaming

```

---

## 72. RISK-05 — Authorization Bypass

Risk:

```text

Protected storage/Git operation runs before policy decision

```

Control:

```text

Central authorization

Application-service ordering

Tests

```

---

## 73. RISK-06 — PAT Leakage

Risk:

```text

Plaintext PAT enters logs/database/history

```

Control:

```text

One-time display

Hash-only persistence

Safe logs

Tests

```

---

# Implementation Exit Criteria

## 74. EC-01 — Foundation Implemented

```text

Application foundation supports approved component structure

```

---

## 75. EC-02 — Database Implemented

```text

Required migrations/models/constraints exist

```

---

## 76. EC-03 — Authentication Implemented

```text

Register/login/logout function correctly

```

---

## 77. EC-04 — Repository Management Implemented

```text

Create/list/view/change visibility work correctly

```

---

## 78. EC-05 — Authorization Implemented

```text

Approved access matrix is enforced

```

---

## 79. EC-06 — Repository Storage Implemented

```text

Safe physical mapping and initialization work correctly

```

---

## 80. EC-07 — Git Process Execution Implemented

```text

Approved Git operations execute through controlled process boundary

```

---

## 81. EC-08 — Repository Browser Implemented

```text

Branches/commits/trees/blobs are available according to authorization

```

---

## 82. EC-09 — Git Smart HTTP Read Implemented

```text

Clone/fetch operate according to public/private rules

```

---

## 83. EC-10 — PAT Lifecycle Implemented

```text

Create/authenticate/revoke work safely

```

---

## 84. EC-11 — Git Smart HTTP Write Implemented

```text

Owner push works and unauthorized push is rejected

```

---

## 85. EC-12 — Required Web UI Implemented

```text

Approved V1.0.0 browser workflows are available

```

---

## 86. EC-13 — Observability Implemented

```text

Core operations/failures are diagnosable without sensitive leakage

```

---

## 87. EC-14 — Security Controls Implemented

```text

Approved security controls are present

```

---

## 88. EC-15 — Deployment Configuration Implemented

```text

Application is configuration-ready for Deployment

```

---

## 89. EC-16 — Implementation Traceability Complete

```text

Major requirements/design elements map to implementation and tests

```

---

## 90. EC-17 — No Blocking Implementation Defect

```text

Blocking defects = 0

```

---

## 91. EC-18 — Ready for Testing

```text

Formal SDLC Testing can begin

```

---

# M4 Deliverables

## 92. Implementation Deliverables

M4 produces:

```text

1. Working application source code

2. Database migrations and models

3. Authentication implementation

4. Repository management implementation

5. Authorization implementation

6. Repository storage implementation

7. Git process execution implementation

8. Repository browser implementation

9. Git Smart HTTP read implementation

10. PAT implementation

11. Git Smart HTTP write implementation

12. Web UI implementation

13. Observability implementation

14. Security hardening

15. Deployment configuration

16. Implementation traceability

17. Implementation validation

18. M4 milestone review

```

---

# Completion Definition

## 93. M4 Completion

M4 is complete when:

- all required V1.0.0 capabilities are implemented;

- approved database structures exist;

- authentication works;

- repository metadata management works;

- authorization rules are enforced;

- repository storage is safe;

- controlled Git execution works;

- repository browsing works;

- Git clone/fetch work;

- PAT lifecycle works;

- Git push works;

- required web UI exists;

- observability controls exist;

- security controls are implemented;

- deployment configuration is ready;

- implementation remains traceable to design;

- developer-level automated tests pass;

- no deferred/excluded scope is accidentally implemented;

- blocking implementation defects equal zero;

- implementation validation passes;

- M4 exit criteria pass;

- the project is ready for SDLC Phase 6 — Testing.

---

## 94. Current M4 Status

```text

M4 — Implementation

IN PROGRESS

```

Current work package:

```text

WP-01 — Implementation Foundation

```

---

## 95. Next Step

After approval of this plan:

```text

Begin WP-01 — Implementation Foundation

```

The first implementation activity is to inspect and normalize the current Laravel application structure and configuration against the approved M3 architecture before adding new feature code.

---

## 96. Document Status

This document is currently:

```text

Approved

```

It will be changed to:

```text

Approved

```

after the implementation work packages, dependency order, Git strategy, testing approach, scope controls, risks, deliverables, and M4 exit criteria have been reviewed.
