# GitForge V1.0.0 Design Traceability

## 1. Document Information

| Field | Value |

|---|---|

| Project | GitForge - Web Git Hosting Platform |

| Target release | V1.0.0 |

| Document | Design Traceability |

| SDLC phase | System Design |

| Milestone | M3 - System Design |

| Project owner | Farhad Valizadegan |

| Status | Approved |

| Last updated | 2026-08-18 |

---

## 2. Purpose

This document establishes traceability between the approved GitForge V1.0.0 requirements, System Analysis artifacts, and System Design artifacts.

Its purpose is to demonstrate that major approved requirements are mapped to:

```text

Architecture decisions

Containers

Components

Database structures

Application interfaces

Authorization controls

Repository storage

Git process execution

UI structures

Deployment controls

Observability controls

Security controls

Verification approaches

```

This document also confirms that System Design has not introduced functionality outside the approved V1.0.0 scope.

---

## 3. Traceability Objectives

The traceability model must demonstrate:

```text

Requirements → Analysis → Design

```

and:

```text

Design → Verification Approach

```

The intended result is:

```text

No major requirement without design coverage

No major design element without requirement justification

No blocking traceability gap before Implementation

```

---

# Source Artifacts

## 4. Requirements Engineering Sources

Primary Requirements Engineering artifacts include:

```text

01-requirements-engineering-plan.md

02-requirements-elicitation.md

03-actors-and-external-systems.md

04-functional-requirements.md

05-non-functional-requirements.md

06-data-requirements.md

07-interface-requirements.md

08-business-rules.md

09-use-case-model.md

10-detailed-use-case-specifications.md

11-user-stories-and-acceptance-criteria.md

12-authorization-requirements-matrix.md

13-requirements-prioritization.md

14-requirements-traceability-matrix.md

15-requirements-validation.md

16-milestone-review.md

```

---

## 5. Approved Requirement Sets

Approved V1.0.0 requirement identifiers include:

```text

FR-001 through FR-098

NFR-001 through NFR-098

DR-001 through DR-094

IR-001 through IR-100

BR-001 through BR-096

AR-001 through AR-060

UC-001 through UC-025

US-001 through US-029

```

---

## 6. System Analysis Sources

Primary M2 System Analysis artifacts include:

```text

01-system-analysis-plan.md

02-system-context-analysis.md

03-domain-analysis.md

04-logical-system-decomposition.md

05-authentication-analysis.md

06-authorization-analysis.md

07-repository-lifecycle-analysis.md

08-repository-browser-analysis.md

09-git-smart-http-read-analysis.md

10-git-smart-http-write-analysis.md

11-personal-access-token-analysis.md

12-repository-storage-analysis.md

13-data-flow-analysis.md

14-state-analysis.md

15-trust-boundary-analysis.md

16-failure-and-exception-analysis.md

17-analysis-sequence-models.md

18-analysis-activity-models.md

19-analysis-traceability.md

20-system-analysis-validation.md

21-milestone-review.md

```

---

## 7. System Design Sources

Primary M3 design artifacts covered by this document:

```text

01-system-design-plan.md

02-architecture-decisions.md

03-container-architecture.md

04-component-design.md

05-database-schema-design.md

06-api-specification.md

07-authorization-design-matrix.md

08-repository-storage-design.md

09-git-process-execution-design.md

10-ui-information-architecture.md

11-wireframes.md

12-deployment-design.md

13-observability-design.md

14-security-design.md

```

---

# Traceability Model

## 8. Traceability Chain

The target chain is:

```text

Requirement

     ↓

Use Case / Analysis Concern

     ↓

Architecture Decision

     ↓

Container / Component

     ↓

Data / API / Storage / Git / UI Design

     ↓

Security / Deployment / Observability Controls

     ↓

Verification Approach

```

---

## 9. Design Coverage Dimensions

Major requirements are traced across these dimensions:

```text

D1 Architecture

D2 Component Responsibility

D3 Data/Persistence

D4 Interface/API

D5 Authorization

D6 Repository Storage

D7 Git Execution

D8 UI

D9 Deployment

D10 Observability

D11 Security

D12 Verification

```

Not every requirement requires all twelve dimensions.

---

# Actor Traceability

## 10. Visitor

Requirement/analysis concern:

```text

Anonymous public repository read

Anonymous Git clone/fetch

No private repository access

No repository write

```

Design mapping:

```text

Architecture:

ADR-016 Centralized Repository Authorization

ADR-018 Owner-Only Repository Write

ADR-019 Visibility Controls Read Only

Components:

Repository Authorization

Repository Browser Service

Git Read Service

API:

Public repository GET routes

git-upload-pack discovery/RPC

Authorization:

Public read = Allow

Private read = Deny

Write = Deny

UI:

Public repository pages available

Private repository concealed

Security:

Private repository concealment

Authorization before storage/Git

```

Verification:

```text

Anonymous public repository view succeeds

Anonymous public clone succeeds

Anonymous private repository access fails

Anonymous push fails

```

---

## 11. Authenticated User

Requirement/analysis concern:

```text

Own repositories

Create repositories

Manage own PATs

Read public repositories

No non-owner private access

No non-owner write

```

Design mapping:

```text

Authentication Service

Repository Service

PAT Service

Repository Authorization

Session-authenticated APIs

Dashboard

PAT Settings

```

Verification:

```text

Authenticated user can create own repository

Authenticated user sees own repository list

Authenticated user cannot manage another user's repository

Authenticated user cannot revoke another user's PAT

```

---

## 12. Repository Owner

Requirement/analysis concern:

```text

Read own public/private repository

Write own repository

Change repository visibility

Manage own PATs

```

Design mapping:

```text

repositories.owner_id

RepositoryPolicy / Authorization component

AUTH-READ

AUTH-WRITE

AUTH-MANAGE

AUTH-PAT-MANAGE

```

Verification:

```text

Owner can read private repository

Owner can push

Owner can change visibility

Owner can create/revoke own PAT

```

---

## 13. Git Client

Requirement/analysis concern:

```text

Clone

Fetch

Push

PAT authentication

Git Smart HTTP interoperability

```

Design mapping:

```text

Git Smart HTTP Interface

Git Read Service

Git Write Service

PAT Authentication

Git Process Executor

upload-pack

receive-pack

```

Verification:

```text

Public clone

Public fetch

Private PAT-authenticated clone/fetch

Owner PAT-authenticated push

Denied non-owner push

```

---

## 14. System Operator

Requirement/analysis concern:

```text

Operate deployment

Diagnose failures

Maintain infrastructure

No automatic repository authorization role

```

Design mapping:

```text

Deployment Design

Observability Design

Health checks

Logs

Backups

No application admin authorization bypass

```

Verification:

```text

Operational health visible

Failures diagnosable

Operator identity does not automatically grant repository permissions through application policy

```

---

# Functional Capability Traceability

## 15. Registration

Primary coverage:

```text

UC-001 Register

```

Design mapping:

```text

Authentication Service

users table

POST /register

Register UI

Password hashing

Input validation

CSRF

Authentication logging

```

Verification:

```text

Valid registration succeeds

Duplicate email rejected

Invalid fields rejected

Password stored only as secure hash

```

---

## 16. Authentication

Primary coverage:

```text

UC-002 Authenticate

```

Design mapping:

```text

Session Authentication

Authentication Service

POST /login

Login UI

Session regeneration

Rate limiting

Authentication events

```

Verification:

```text

Valid login succeeds

Invalid credentials fail

Session ID regenerated

No password logged

```

---

## 17. Logout

Primary coverage:

```text

UC-003 Logout

```

Design mapping:

```text

POST /logout

Session invalidation

CSRF protection

Global authenticated navigation

```

Verification:

```text

Logout invalidates authenticated session

Subsequent protected request requires authentication

```

---

## 18. Create Repository

Primary coverage:

```text

UC-004 Create Repository

```

Design mapping:

```text

Repository Service

repositories table

UNIQUE(owner_id, name)

POST /repositories

Repository Storage

Git Process Executor

git init --bare

Creation compensation

Create Repository UI

```

Verification:

```text

Valid creation succeeds

Duplicate namespace rejected

Invalid name rejected

Bare Git repository created

Partial failure does not report success

```

---

## 19. List Owned Repositories

Primary coverage:

```text

UC-005 List Owned Repositories

```

Design mapping:

```text

Repository Service

repositories.owner_id

GET /repositories

My Repositories UI

```

Verification:

```text

Only repositories owned by current user are returned

Filesystem enumeration is not used as source of truth

```

---

## 20. View Repository

Primary coverage:

```text

UC-006 View Repository

```

Design mapping:

```text

Repository Service

Repository Authorization

GET /{owner}/{repository}

Repository Overview UI

Public/private access matrix

```

Verification:

```text

Public repository visible anonymously

Private repository visible only to owner

Unauthorized private repository concealed

```

---

## 21. Change Visibility

Primary coverage:

```text

UC-007 Change Visibility

```

Design mapping:

```text

repositories.visibility

CHECK(public/private)

PATCH /{owner}/{repository}/visibility

AUTH-MANAGE

Repository Settings UI

repository_visibility_changed event

```

Verification:

```text

Owner can change public ↔ private

Non-owner cannot change visibility

Invalid visibility rejected

```

---

## 22. Repository Deletion

Primary coverage:

```text

UC-008 Delete Repository

```

Status:

```text

Deferred from V1.0.0

```

Design treatment:

```text

No DELETE endpoint

No delete UI

No permanent repository deletion workflow

No delete authorization design

```

Verification:

```text

No V1.0.0 user-facing delete functionality exists

```

---

# Repository Browser Traceability

## 23. Browse Repository

Primary coverage:

```text

UC-009 Browse Repository

```

Design mapping:

```text

Repository Browser Service

AUTH-READ

Repository Storage

Git Process Executor

Repository Files UI

```

Verification:

```text

Public browse works anonymously

Private browse owner-only

Browser is read-only

```

---

## 24. List Branches

Primary coverage:

```text

UC-010 List Branches

```

Design mapping:

```text

GET /{owner}/{repository}/branches

LIST_REFS

git for-each-ref

Branches UI

```

Verification:

```text

Branches returned accurately

Empty repository returns empty list/state

```

---

## 25. Commit History

Primary coverage:

```text

UC-011 Commit History

```

Design mapping:

```text

GET /{owner}/{repository}/commits

COMMIT_HISTORY

git log / git rev-list

Bounded pagination

Commit History UI

```

Verification:

```text

Commit history returned

Pagination/bounds enforced

Empty repository handled

```

---

## 26. Commit Details

Primary coverage:

```text

UC-012 Commit Details

```

Design mapping:

```text

GET /{owner}/{repository}/commits/{commit}

COMMIT_DETAILS

git show / plumbing

Commit Details UI

```

Verification:

```text

Valid commit displayed

Unknown commit returns safe not-found

Commit metadata safely encoded

```

---

## 27. Tree

Primary coverage:

```text

UC-013 Tree

```

Design mapping:

```text

GET /{owner}/{repository}/tree/{ref}

TREE_LIST

git ls-tree

Breadcrumb/tree UI

```

Verification:

```text

Tree entries returned

Branch/ref context preserved

Tree paths cannot escape host filesystem

```

---

## 28. File

Primary coverage:

```text

UC-014 File

```

Design mapping:

```text

GET /{owner}/{repository}/blob/{ref}

BLOB_READ

git cat-file

Text/binary/large-file UI states

```

Verification:

```text

Text safely displayed

Binary not blindly rendered as text

Large file display limit enforced

Repository content not executed

```

---

# Git Smart HTTP Traceability

## 29. Discover Read Service

Primary coverage:

```text

UC-015 Discover Read Service

```

Design mapping:

```text

GET /{owner}/{repository}.git/info/refs?service=git-upload-pack

Git Read Service

UPLOAD_PACK_ADVERTISE

```

Verification:

```text

Public advertisement works anonymously

Private advertisement requires owner PAT identity

```

---

## 30. Clone

Primary coverage:

```text

UC-016 Clone

```

Design mapping:

```text

POST .../git-upload-pack

UPLOAD_PACK_RPC

Streaming

AUTH-READ

```

Verification:

```text

Public anonymous clone succeeds

Private owner PAT clone succeeds

Unauthorized private clone fails

```

---

## 31. Fetch

Primary coverage:

```text

UC-017 Fetch

```

Design mapping:

```text

Git upload-pack

Git Read Service

AUTH-READ

Streaming

```

Verification:

```text

Authorized fetch succeeds

Unauthorized private fetch fails

```

---

## 32. Discover Write Service

Primary coverage:

```text

UC-018 Discover Write Service

```

Design mapping:

```text

GET /{owner}/{repository}.git/info/refs?service=git-receive-pack

PAT Authentication

AUTH-WRITE

RECEIVE_PACK_ADVERTISE

```

Verification:

```text

Owner receives write advertisement

Anonymous/non-owner denied

```

---

## 33. Push

Primary coverage:

```text

UC-019 Push

```

Design mapping:

```text

POST .../git-receive-pack

Git Write Service

PAT Authentication

AUTH-WRITE

RECEIVE_PACK_RPC

Streaming

```

Verification:

```text

Owner push succeeds

Anonymous push denied

Non-owner push denied

Git-native rejection remains distinct from auth failure

```

---

# PAT Traceability

## 34. Create PAT

Primary coverage:

```text

UC-020 Create PAT

```

Design mapping:

```text

personal_access_tokens table

PAT Service

POST /settings/tokens

Create PAT UI

One-Time Secret UI

Secure token hashing

```

Verification:

```text

PAT successfully created

Plaintext shown once

Plaintext not stored

Hash never exposed

```

---

## 35. Authenticate Git with PAT

Primary coverage:

```text

UC-021 Authenticate Git with PAT

```

Design mapping:

```text

token_identifier

token_hash

revoked_at

PAT Authentication

Git Smart HTTP

```

Verification:

```text

Valid PAT establishes identity

Invalid PAT fails

Revoked PAT fails

Valid non-owner PAT still cannot push

```

---

## 36. Revoke PAT

Primary coverage:

```text

UC-022 Revoke PAT

```

Design mapping:

```text

POST /settings/tokens/{token}/revoke

AUTH-PAT-MANAGE

revoked_at

PAT list/revoke UI

pat_revoked event

```

Verification:

```text

Owner can revoke

Other user cannot revoke

Revoked PAT cannot authenticate

Repeated revocation is safe

```

---

# Authorization Traceability

## 37. Authorize Read

Primary coverage:

```text

UC-023 Authorize Read

AR-001 through AR-060 as applicable

```

Design mapping:

```text

Repository Authorization Policy

AUTH-READ

Public/private matrix

Authorization before protected storage

Authorization before Git execution

```

Verification:

```text

Public anonymous → Allow

Public authenticated → Allow

Private owner → Allow

Private anonymous → Deny

Private non-owner → Deny

```

---

## 38. Authorize Write

Primary coverage:

```text

UC-024 Authorize Write

```

Design mapping:

```text

AUTH-WRITE

Owner ID comparison

Git Write Service

receive-pack gate

```

Verification:

```text

Owner → Allow

Anonymous → Deny

Non-owner → Deny

Visibility does not change write permission

```

---

# Failure Diagnosis Traceability

## 39. Diagnose Failure

Primary coverage:

```text

UC-025 Diagnose Failure

```

Design mapping:

```text

Failure classification

Observability Design

request_id

Structured logs

Health checks

Git process result model

Storage state model

Safe error mapping

```

Verification:

```text

Authentication failures distinguishable

Authorization failures distinguishable

Storage failures distinguishable

Git failures distinguishable

Database failures distinguishable

No secrets exposed externally

```

---

# Functional Requirement Coverage

## 40. Functional Requirement Families

Approved functional requirements are covered through these design areas:

| Functional Area | Primary Design Artifacts |

|---|---|

| User registration/authentication | 04, 05, 06, 10, 11, 14 |

| Repository creation | 04, 05, 06, 08, 09, 14 |

| Repository listing/view | 04, 05, 06, 07, 10, 11 |

| Visibility management | 05, 06, 07, 10, 11 |

| Repository browsing | 04, 06, 07, 08, 09, 10, 11 |

| Git clone/fetch | 03, 04, 06, 07, 08, 09, 12, 14 |

| Git push | 03, 04, 06, 07, 08, 09, 12, 14 |

| PAT lifecycle | 04, 05, 06, 07, 10, 11, 14 |

| Failure diagnosis | 09, 12, 13, 14 |

Artifact numbers refer to M3 design document numbers.

---

## 41. Functional Coverage Result

Blocking functional requirements without design coverage:

```text

0

```

Result:

```text

PASS

```

---

# Non-Functional Requirement Traceability

## 42. Security

Primary design coverage:

```text

02 Architecture Decisions

07 Authorization Design Matrix

08 Repository Storage Design

09 Git Process Execution Design

12 Deployment Design

13 Observability Design

14 Security Design

```

Verification approach:

```text

Security tests

Authorization tests

Injection tests

Path containment tests

Credential logging tests

Deployment hardening tests

```

---

## 43. Reliability

Design coverage:

```text

Fail-closed architecture

Repository consistency states

Creation compensation

No silent repair

Explicit failure classification

Persistent storage

Backup/restore design

```

Verification:

```text

Failure-injection tests

Missing-storage tests

Creation-failure tests

Restore/smoke tests

```

---

## 44. Performance

Design coverage:

```text

Git Smart HTTP streaming

Bounded commit history

Blob display limits

Process timeout

Avoid unnecessary payload buffering

```

Verification:

```text

Large clone/fetch/push tests

Large blob tests

Timeout tests

Pagination tests

```

---

## 45. Availability

Design coverage:

```text

Health checks

Readiness checks

Persistent PostgreSQL

Persistent repository storage

Git binary diagnostics

Backup/restore

```

Verification:

```text

Dependency outage tests

Readiness tests

Restart tests

Backup/restore tests

```

---

## 46. Maintainability

Design coverage:

```text

Modular monolith

Thin controllers

Application services

Central authorization

Explicit storage abstraction

Explicit Git process abstraction

Central configuration

Structured logging

```

Verification:

```text

Architecture review

Component dependency review

Code review during Implementation

```

---

## 47. Portability

Design coverage:

```text

Configurable repository root

Configurable Git binary

Windows development support

Linux production-like support

Platform-safe path APIs

```

Verification:

```text

Development environment tests

Deployment validation

Configuration tests

```

---

## 48. Recoverability

Design coverage:

```text

Dual-authority backup

Database + Git storage restore

Post-restore consistency checks

No silent recreation

No orphan adoption

```

Verification:

```text

Backup/restore exercise

Post-restore repository tests

```

---

# Data Requirement Traceability

## 49. User Data

Design mapping:

```text

users table

Eloquent User model

Authentication Service

Password hashing

```

---

## 50. Repository Metadata

Design mapping:

```text

repositories table

owner_id

name

visibility

timestamps

UNIQUE(owner_id, name)

```

---

## 51. Git-Native Repository Data

Design mapping:

```text

Bare Git repository

Repository Storage

Git executable

Not duplicated into PostgreSQL

```

---

## 52. PAT Data

Design mapping:

```text

personal_access_tokens

user_id

name

token_identifier

token_hash

revoked_at

timestamps

```

---

## 53. Data Authority Matrix

| Data | Authority |

|---|---|

| User metadata | PostgreSQL |

| Repository identity/owner/visibility | PostgreSQL |

| PAT metadata/hash/revocation | PostgreSQL |

| Git refs | Physical Git repository |

| Git commits | Physical Git repository |

| Git trees | Physical Git repository |

| Git blobs | Physical Git repository |

| Git history | Physical Git repository |

---

## 54. Data Coverage Result

Blocking data requirement gaps:

```text

0

```

Result:

```text

PASS

```

---

# Interface Requirement Traceability

## 55. Browser Interface

Design mapping:

```text

06 API Specification

10 UI Information Architecture

11 Wireframes

```

---

## 56. Git Client Interface

Design mapping:

```text

06 API Specification

09 Git Process Execution Design

12 Deployment Design

14 Security Design

```

---

## 57. PostgreSQL Interface

Design mapping:

```text

05 Database Schema Design

12 Deployment Design

```

---

## 58. Repository Storage Interface

Design mapping:

```text

08 Repository Storage Design

09 Git Process Execution Design

```

---

## 59. Git Executable Interface

Design mapping:

```text

09 Git Process Execution Design

12 Deployment Design

14 Security Design

```

---

# Business Rule Traceability

## 60. Repository Namespace

Business rule:

```text

Repository unique within owner namespace

```

Design:

```text

UNIQUE(owner_id, name)

```

---

## 61. Repository Name Safety

Business rule:

```text

Safe restricted repository name format

```

Design:

```text

API validation

Security validation

Internal ID-based storage mapping

```

---

## 62. Visibility

Business rule:

```text

public/private only

```

Design:

```text

CHECK visibility

Validation

Authorization Matrix

```

---

## 63. Public Read

Business rule:

```text

Public repository readable anonymously

```

Design:

```text

AUTH-READ

Public repository UI

upload-pack public access

```

---

## 64. Private Read

Business rule:

```text

Private repository owner-only

```

Design:

```text

AUTH-READ owner check

Private concealment

```

---

## 65. Write

Business rule:

```text

Repository write owner-only

```

Design:

```text

AUTH-WRITE

PAT identity

receive-pack gate

```

---

## 66. PAT Plaintext

Business rule:

```text

Plaintext PAT visible once

```

Design:

```text

One-time creation response

No plaintext persistence

PAT One-Time Secret UI

```

---

## 67. Repository Deletion

Business rule/scope decision:

```text

Deferred

```

Design consequence:

```text

No endpoint

No UI

No storage deletion workflow

```

---

# Authorization Requirement Traceability

## 68. Authorization Requirement Set

Approved authorization requirements:

```text

AR-001 through AR-060

```

The V1.0.0 design maps them into four policy categories:

```text

AUTH-READ

AUTH-WRITE

AUTH-MANAGE

AUTH-PAT-MANAGE

```

---

## 69. Read Authorization Coverage

Design:

```text

07-authorization-design-matrix.md

06-api-specification.md

14-security-design.md

```

Verification:

```text

Visitor/public

Authenticated non-owner/public

Owner/public

Visitor/private

Authenticated non-owner/private

Owner/private

```

---

## 70. Write Authorization Coverage

Design:

```text

Owner-only write

PAT-authenticated Git identity

receive-pack enforcement

```

Verification:

```text

Anonymous deny

Non-owner deny

Owner allow

```

---

## 71. Management Authorization Coverage

Design:

```text

Visibility change owner-only

```

Verification:

```text

Owner allow

Non-owner deny

Anonymous deny

```

---

## 72. PAT Authorization Coverage

Design:

```text

PAT list/create/revoke tied to authenticated user

```

Verification:

```text

Own token operations allowed

Other user's token operations denied

```

---

# Architecture Decision Traceability

## 73. ADR Group — Application Architecture

Key ADRs:

```text

ADR-001 Modular Monolith

ADR-002 Laravel 12

ADR-022 Thin HTTP Controllers

ADR-023 Application Services

ADR-031 Shared Core Services

ADR-068 Server-Oriented UI

```

Requirement justification:

```text

Maintainability

Implementation simplicity

Shared web/Git domain behavior

Portfolio-scale deployment

```

---

## 74. ADR Group — Persistence

Key ADRs:

```text

ADR-003 PostgreSQL

ADR-004 Git-Native State Outside PostgreSQL

ADR-027 DB Constraints

ADR-029 Eloquent

ADR-030 DB Transactions

ADR-060 DB IDs

ADR-061 Owner/Name Uniqueness

ADR-062 Explicit Visibility

```

Requirement justification:

```text

Application metadata integrity

Repository ownership

Namespace uniqueness

Clear data authority

```

---

## 75. ADR Group — Git Hosting

Key ADRs:

```text

ADR-005 Bare Git Repositories

ADR-008 System Git Executable

ADR-010 Git Smart HTTP

ADR-011 Separate Read/Write Git Services

ADR-020 Git-Native Repository Browser Reads

ADR-025 Git Process Component

ADR-032 Protocol-Specific Responses

ADR-033 Streaming

```

Requirement justification:

```text

Clone

Fetch

Push

Git-native browsing

Protocol interoperability

```

---

## 76. ADR Group — Authorization/Security

Key ADRs:

```text

ADR-012 Session Authentication

ADR-013 PAT Authentication

ADR-014 PAT Plaintext One-Time

ADR-016 Central Authorization

ADR-017 Authorization Before Storage

ADR-018 Owner-Only Write

ADR-019 Visibility Controls Read

ADR-021 Repository Content Untrusted

ADR-036 Fail Closed

ADR-040 Sensitive Logging Exclusion

ADR-042 Password Hashing

ADR-043 CSRF

ADR-044 Parameterized DB Access

ADR-045 Output Encoding

```

Requirement justification:

```text

Credential protection

Private repository confidentiality

Owner-only mutation

Web security

Git authentication security

```

---

## 77. ADR Group — Storage

Key ADRs:

```text

ADR-006 Logical Namespace Separate from Physical Path

ADR-007 Configured Repository Root

ADR-024 Repository Storage Component

ADR-026 Creation Compensation

ADR-050 Persistent Repository Storage

ADR-054 Dual Backup

ADR-059 Internal Physical Mapping

ADR-064 Empty Repository Valid

ADR-065 Missing Storage Failure

ADR-066 No Implicit Repair

```

Requirement justification:

```text

Safe repository creation

Path traversal prevention

Persistence

Consistency

Recoverability

```

---

## 78. ADR Group — 07-deployment/Operations

Key ADRs:

```text

ADR-039 Structured Logging

ADR-049 Small Runtime Deployment

ADR-051 Persistent PostgreSQL

ADR-052 Secrets via Configuration

ADR-053 Health Verification

ADR-055 No Mandatory Redis

ADR-056 No Mandatory Queue

ADR-058 Central Configuration

```

Requirement justification:

```text

Simple deployment

Operational diagnosis

Persistence

Secure configuration

Health monitoring

```

---

# Component Traceability

## 79. Authentication Service

Supports:

```text

UC-001

UC-002

UC-003

Session security

```

---

## 80. PAT Service

Supports:

```text

UC-020

UC-021

UC-022

PAT lifecycle/security

```

---

## 81. Repository Service

Supports:

```text

UC-004

UC-005

UC-006

UC-007

```

---

## 82. Repository Authorization

Supports:

```text

UC-023

UC-024

Public/private read rules

Owner-only write/manage

```

---

## 83. Repository Browser Service

Supports:

```text

UC-009

UC-010

UC-011

UC-012

UC-013

UC-014

```

---

## 84. Repository Storage

Supports:

```text

Repository physical mapping

Safe repository initialization

Existing repository resolution

Consistency detection

```

---

## 85. Git Read Service

Supports:

```text

UC-015

UC-016

UC-017

```

---

## 86. Git Write Service

Supports:

```text

UC-018

UC-019

```

---

## 87. Git Process Executor

Supports:

```text

Bare initialization

upload-pack

receive-pack

Repository browser Git operations

Timeout/failure handling

```

---

# Database Traceability

## 88. users

Requirement coverage:

```text

User registration

Authentication

Repository ownership identity

PAT ownership identity

```

---

## 89. repositories

Requirement coverage:

```text

Repository identity

Owner

Namespace

Visibility

Repository listing

Repository authorization

```

---

## 90. personal_access_tokens

Requirement coverage:

```text

PAT identity

Secure verification

Revocation

PAT ownership

```

---

# API Traceability

## 91. Authentication Endpoints

```text

POST /register

POST /login

POST /logout

```

Support:

```text

UC-001

UC-002

UC-003

```

---

## 92. Repository Endpoints

```text

POST /repositories

GET /repositories

GET /{owner}/{repository}

PATCH /{owner}/{repository}/visibility

```

Support:

```text

UC-004

UC-005

UC-006

UC-007

```

---

## 93. Browser Endpoints

```text

GET /{owner}/{repository}/branches

GET /{owner}/{repository}/commits

GET /{owner}/{repository}/commits/{commit}

GET /{owner}/{repository}/tree/{ref}

GET /{owner}/{repository}/blob/{ref}

```

Support:

```text

UC-009 through UC-014

```

---

## 94. PAT Endpoints

```text

GET /settings/tokens

POST /settings/tokens

POST /settings/tokens/{token}/revoke

```

Support:

```text

UC-020

UC-022

```

---

## 95. Git Smart HTTP Endpoints

```text

GET  /{owner}/{repository}.git/info/refs?service=git-upload-pack

POST /{owner}/{repository}.git/git-upload-pack

GET  /{owner}/{repository}.git/info/refs?service=git-receive-pack

POST /{owner}/{repository}.git/git-receive-pack

```

Support:

```text

UC-015

UC-016

UC-017

UC-018

UC-019

UC-021

UC-023

UC-024

```

---

# UI Traceability

## 96. Authentication Screens

```text

Home

Register

Login

```

Support:

```text

UC-001

UC-002

UC-003

```

---

## 97. Repository Screens

```text

My Repositories

Create Repository

Repository Overview

Repository Settings

```

Support:

```text

UC-004

UC-005

UC-006

UC-007

```

---

## 98. Browser Screens

```text

Files

Branches

Commit History

Commit Details

File/Blob

```

Support:

```text

UC-009 through UC-014

```

---

## 99. PAT Screens

```text

PAT List

Create PAT

PAT One-Time Secret

PAT Revocation

```

Support:

```text

UC-020

UC-022

```

---

# Deployment Traceability

## 100. Laravel Runtime

Supports:

```text

All application use cases

Session auth

HTTP API

Git Smart HTTP handlers

```

---

## 101. PostgreSQL

Supports:

```text

Persistent application metadata

Ownership

Authentication

PAT metadata

```

---

## 102. Repository Storage

Supports:

```text

Persistent Git-native user data

Clone/fetch/push

Repository browser

```

---

## 103. Git Executable

Supports:

```text

git init --bare

upload-pack

receive-pack

Repository browser Git operations

```

---

## 104. HTTPS Ingress

Supports:

```text

Secure session authentication

Secure PAT transport

Git Smart HTTP

```

---

# Observability Traceability

## 105. Authentication Events

Support diagnosis of:

```text

UC-002

UC-021

```

---

## 106. Authorization Events

Support diagnosis of:

```text

UC-023

UC-024

```

---

## 107. Repository Events

Support diagnosis of:

```text

UC-004

UC-007

```

---

## 108. Git Process Events

Support diagnosis of:

```text

UC-015

UC-016

UC-017

UC-018

UC-019

UC-025

```

---

## 109. Storage Events

Support diagnosis of:

```text

Repository missing

Repository inconsistent

Unsafe path

Storage unavailable

```

---

# Security Traceability

## 110. Authentication Security Controls

Mapped controls:

```text

Password hashing

Session regeneration

Secure cookies

Rate limiting

HTTPS

```

---

## 111. Authorization Security Controls

Mapped controls:

```text

Central Repository Policy

Owner identity from database

Fail closed

Private concealment

Authorization before storage/Git

```

---

## 112. Data Security Controls

Mapped controls:

```text

Parameterized queries

Database constraints

PAT hash-only storage

Deployment secret isolation

```

---

## 113. Repository Storage Controls

Mapped controls:

```text

Internal ID-based mapping

Root containment

No arbitrary path input

No silent recreation

No orphan adoption

```

---

## 114. Git Execution Controls

Mapped controls:

```text

Configured Git binary

Operation allowlist

No shell

Structured arguments

Timeout

Controlled environment

```

---

## 115. Output Security Controls

Mapped controls:

```text

HTML output encoding

Binary-safe behavior

No repository code execution

Safe error mapping

Sensitive logging exclusion

```

---

# Verification Traceability

## 116. Verification Categories

Each major requirement maps to one or more verification approaches:

```text

Unit Test

Feature Test

Integration Test

Security Test

Git Protocol Test

Database Test

Filesystem/Storage Test

Deployment Smoke Test

Manual UI Verification

Backup/Restore Verification

```

---

## 117. Authentication Verification

```text

Unit/Feature:

Password validation

Login

Logout

Session behavior

Security:

Enumeration resistance

CSRF

Rate limiting

Secret logging

```

---

## 118. Repository Verification

```text

Feature:

Create/list/view/change visibility

Database:

Namespace uniqueness

Visibility constraint

Storage:

Bare repo created

Missing storage handled

Compensation behavior

```

---

## 119. Authorization Verification

```text

Unit:

Policy matrix

Feature:

Public/private web access

Git Integration:

Public/private upload-pack

Owner/non-owner receive-pack

```

---

## 120. Repository Browser Verification

```text

Integration:

Branches

History

Commit detail

Tree

Blob

Empty repository

Security:

Output encoding

Unsafe refs/paths

Large/binary blob behavior

```

---

## 121. PAT Verification

```text

Feature:

Create/list/revoke

Security:

Plaintext one-time only

Hash not exposed

Revoked token rejected

Other-user revoke denied

```

---

## 122. Git Smart HTTP Verification

```text

Protocol Integration:

Discovery

Clone

Fetch

Push

Security:

PAT auth

Owner-only push

No shell

No option injection

Timeouts

```

---

## 123. Deployment Verification

```text

Smoke:

Home

Authentication

Repository creation

Public/private reads

Clone/fetch/push

Browser

Operational:

DB health

Storage health

Git binary health

TLS

```

---

# Major Traceability Matrix

## 124. Matrix

| Capability | Requirement/Use Case | Primary Components | Data | Interface | Security | Verification |

|---|---|---|---|---|---|---|

| Register | UC-001 | Authentication Service | users | `/register` | Password hashing, CSRF | Feature + Security |

| Login | UC-002 | Authentication Service | users | `/login` | Session regeneration, rate limit | Feature + Security |

| Logout | UC-003 | Authentication Service | Session | `/logout` | CSRF/session invalidation | Feature |

| Create repository | UC-004 | Repository Service, Storage, Git Executor | repositories + Git repo | `POST /repositories` | Validation, safe path | Feature + Storage |

| List repositories | UC-005 | Repository Service | repositories | `GET /repositories` | Own-user filtering | Feature |

| View repository | UC-006 | Repository Service, Authorization | repositories | `GET /{owner}/{repo}` | Public/private policy | Feature + Authz |

| Change visibility | UC-007 | Repository Service, Authorization | repositories | PATCH visibility | Owner-only | Feature + Authz |

| Browse | UC-009 | Browser Service | Git repo | Browser endpoints | AUTH-READ | Integration |

| Branches | UC-010 | Browser Service, Git Executor | Git refs | branches endpoint | Safe ref handling | Integration |

| Commit history | UC-011 | Browser Service, Git Executor | Git commits | commits endpoint | Bounded reads | Integration |

| Commit details | UC-012 | Browser Service, Git Executor | Git commit | commit endpoint | Safe object handling | Integration |

| Tree | UC-013 | Browser Service, Git Executor | Git trees | tree endpoint | Safe Git path | Integration |

| File | UC-014 | Browser Service, Git Executor | Git blob | blob endpoint | XSS/binary safety | Integration + Security |

| Read discovery | UC-015 | Git Read Service | Git refs | info/refs | AUTH-READ | Git protocol |

| Clone | UC-016 | Git Read Service, Executor | Git objects | upload-pack | PAT for private | Git protocol |

| Fetch | UC-017 | Git Read Service, Executor | Git objects | upload-pack | PAT for private | Git protocol |

| Write discovery | UC-018 | Git Write Service | Git refs | info/refs | PAT + owner | Git protocol |

| Push | UC-019 | Git Write Service, Executor | Git objects/refs | receive-pack | PAT + owner | Git protocol |

| Create PAT | UC-020 | PAT Service | PAT table | token endpoint | One-time plaintext | Feature + Security |

| Git PAT auth | UC-021 | PAT Auth | PAT table | HTTP auth | Hash verification | Security + Protocol |

| Revoke PAT | UC-022 | PAT Service | revoked_at | revoke endpoint | Token owner only | Feature + Security |

| Authorize read | UC-023 | Authorization | owner/visibility | Shared policy | Fail closed | Unit + Integration |

| Authorize write | UC-024 | Authorization | owner | Shared policy | Owner only | Unit + Integration |

| Diagnose failure | UC-025 | Observability | Logs/health | Internal diagnostics | Secret exclusion | Failure tests |

---

# Deferred Scope Traceability

## 125. Repository Deletion

Status:

```text

Deferred

```

Design confirmation:

```text

No deletion API

No deletion UI

No normal permanent storage deletion flow

```

---

## 126. Repository Rename

Status:

```text

Deferred

```

Design confirmation:

```text

No rename API

No rename UI

Physical ID mapping remains rename-independent

```

---

## 127. Repository Transfer

Status:

```text

Deferred

```

Design confirmation:

```text

No ownership transfer API/UI

```

---

## 128. Collaborators

Status:

```text

Excluded

```

Design confirmation:

```text

No collaborator table

No ACL table

No collaborator role

No collaborator UI

```

---

## 129. Organizations and Teams

Status:

```text

Excluded

```

Design confirmation:

```text

No organization/team domain model

No authorization roles

No database structures

No UI

```

---

## 130. Pull Requests / Issues / CI/CD

Status:

```text

Excluded

```

Design confirmation:

```text

No components

No APIs

No database structures

No UI

No process execution

```

---

## 131. PAT Scopes and Expiration

Status:

```text

Deferred

```

Design confirmation:

```text

No scope field/table

No expiration field

No scope evaluation

No expiration UI

```

---

## 132. SSH Git

Status:

```text

Excluded

```

Design confirmation:

```text

Git Smart HTTP only

No SSH daemon

No SSH key management

```

---

# Reverse Traceability

## 133. Purpose

Reverse traceability ensures that major design elements are justified by approved requirements or constraints.

---

## 134. PostgreSQL

Justified by:

```text

User metadata

Repository metadata

PAT metadata

Integrity constraints

```

---

## 135. Bare Git Storage

Justified by:

```text

Clone

Fetch

Push

Git-native repository browsing

```

---

## 136. Repository Storage Component

Justified by:

```text

Safe physical mapping

Path containment

Creation lifecycle

Consistency handling

```

---

## 137. Git Process Executor

Justified by:

```text

Bare init

upload-pack

receive-pack

Git-native browser operations

Command-injection prevention

```

---

## 138. Authorization Policy

Justified by:

```text

Public/private repository visibility

Owner-only private read

Owner-only write

Owner-only metadata management

```

---

## 139. Observability

Justified by:

```text

Failure diagnosis

Operational health

Security-relevant events

Git/storage troubleshooting

```

---

## 140. HTTPS

Justified by:

```text

Session credential confidentiality

PAT transport confidentiality

Production security

```

---

# Traceability Gap Analysis

## 141. Missing Requirement Coverage

Blocking major requirements with no design mapping:

```text

0

```

---

## 142. Missing Use-Case Coverage

Active V1.0.0 use cases without design mapping:

```text

0

```

---

## 143. Deferred Use Cases Accidentally Implemented in Design

Detected:

```text

0

```

---

## 144. Unjustified Major Design Components

Detected:

```text

0

```

---

## 145. Authorization Gaps

Detected:

```text

0

```

---

## 146. Data Authority Gaps

Detected:

```text

0

```

---

## 147. Git Protocol Gaps

Detected:

```text

0

```

---

## 148. Repository Storage Gaps

Detected:

```text

0

```

---

## 149. Security Gaps

Blocking security-design traceability gaps:

```text

0

```

---

## 150. Verification Gaps

Major capabilities without an identified verification approach:

```text

0

```

---

# Traceability Validation

## 151. Requirements-to-Design Validation

Major approved requirements map to one or more M3 design artifacts.

Result:

```text

PASS

```

---

## 152. Use-Case-to-Design Validation

Active V1.0.0 use cases:

```text

24

```

Mapped:

```text

24

```

Deferred:

```text

UC-008

```

Result:

```text

PASS

```

---

## 153. Architecture-to-Requirement Validation

Major architecture decisions are justified by approved requirements, analysis conclusions, or explicit V1.0.0 constraints.

Result:

```text

PASS

```

---

## 154. Component-to-Use-Case Validation

Every major application component supports one or more approved use cases or cross-cutting requirements.

Result:

```text

PASS

```

---

## 155. Database-to-Requirement Validation

Every primary V1.0.0 persistent table maps to approved application data requirements.

Result:

```text

PASS

```

---

## 156. API-to-Use-Case Validation

Every required application and Git Smart HTTP interface maps to approved use cases.

Result:

```text

PASS

```

---

## 157. Authorization-to-Requirement Validation

Public/private read and owner-only write/manage rules remain consistent with Requirements Engineering and System Analysis.

Result:

```text

PASS

```

---

## 158. Storage-to-Requirement Validation

Repository physical storage behavior supports Git persistence while preserving path safety and metadata/Git authority separation.

Result:

```text

PASS

```

---

## 159. Git-Execution-to-Requirement Validation

Every Git execution category supports an approved hosting/browser capability.

Result:

```text

PASS

```

---

## 160. UI-to-Use-Case Validation

Every required browser-facing use case has corresponding information architecture and wireframe coverage.

Result:

```text

PASS

```

---

## 161. Deployment-to-NFR Validation

Deployment design supports persistence, security, availability, maintainability, and recoverability requirements.

Result:

```text

PASS

```

---

## 162. Observability-to-NFR Validation

Observability design supports diagnosis, health verification, security-event visibility, and operational maintenance.

Result:

```text

PASS

```

---

## 163. Security-to-Requirement Validation

Security controls trace to approved authentication, authorization, confidentiality, integrity, and execution-safety requirements.

Result:

```text

PASS

```

---

## 164. Scope Validation

Unapproved V1.0.0 product scope introduced by M3 design:

```text

0

```

Result:

```text

PASS

```

---

# Design Coverage Summary

## 165. Coverage Summary

```text

Project Definition                  → Covered

Requirements Engineering            → Covered

System Analysis                     → Covered

Architecture Decisions              → Covered

Container Architecture              → Covered

Component Design                    → Covered

Database Schema                     → Covered

API Specification                   → Covered

Authorization                       → Covered

Repository Storage                  → Covered

Git Process Execution               → Covered

UI Information Architecture         → Covered

Wireframes                          → Covered

Deployment                          → Covered

Observability                       → Covered

Security                            → Covered

Verification Approach               → Covered

```

---

## 166. Blocking Traceability Gaps

```text

0

```

---

## 167. Unapproved Scope Additions

```text

0

```

---

## 168. Deferred Scope Violations

```text

0

```

---

# Implementation Handoff

## 169. Implementation Inputs

Implementation can rely on the following approved design chain:

```text

Architecture Decisions

       ↓

Containers

       ↓

Components

       ↓

Database Schema

       ↓

API Contracts

       ↓

Authorization Policy

       ↓

Repository Storage

       ↓

Git Process Execution

       ↓

UI Structure

       ↓

Deployment

       ↓

Observability

       ↓

Security

```

---

## 170. Implementation Work Packages

The design supports implementation work packages such as:

```text

Authentication

Repository persistence

Repository management

Authorization policy

Repository storage

Git process execution

Repository browser

Git Smart HTTP read

Git Smart HTTP write

PAT lifecycle

Web UI

Observability

Security hardening

Deployment configuration

```

---

## 171. Verification Handoff

Implementation and Testing should preserve traceability by linking tests to:

```text

Requirement IDs

Use Cases

Design Components

Failure/Security Controls

```

where practical.

---

# Traceability Invariants

## 172. DTI-001

```text

Every active V1.0.0 use case

has design coverage

```

---

## 173. DTI-002

```text

Every major design component

has requirement justification

```

---

## 174. DTI-003

```text

Deferred scope

does not receive active implementation design

```

---

## 175. DTI-004

```text

Authorization requirements

map to explicit policy controls

```

---

## 176. DTI-005

```text

Persistent data requirements

map to authoritative storage

```

---

## 177. DTI-006

```text

Git protocol requirements

map to Git Smart HTTP and controlled Git execution

```

---

## 178. DTI-007

```text

Security requirements

map to explicit controls and tests

```

---

## 179. DTI-008

```text

Major requirements

have identified verification approaches

```

---

# Completion

## 180. Completion Criteria

This design traceability document is complete when:

- Requirements Engineering sources are identified;

- System Analysis sources are identified;

- System Design sources are identified;

- traceability dimensions are defined;

- actor requirements are mapped;

- registration is mapped;

- authentication is mapped;

- logout is mapped;

- repository creation is mapped;

- repository listing is mapped;

- repository viewing is mapped;

- visibility management is mapped;

- repository browser capabilities are mapped;

- Git Smart HTTP read capabilities are mapped;

- Git Smart HTTP write capabilities are mapped;

- PAT lifecycle is mapped;

- authorization use cases are mapped;

- failure diagnosis is mapped;

- functional requirement families are covered;

- non-functional requirement families are covered;

- data requirements are mapped;

- interface requirements are mapped;

- business rules are mapped;

- authorization requirements are mapped;

- major ADR groups are traceable;

- major components are traceable;

- primary database structures are traceable;

- API routes are traceable;

- UI structures are traceable;

- deployment elements are traceable;

- observability controls are traceable;

- security controls are traceable;

- verification approaches are identified;

- deferred/excluded scope is explicitly preserved;

- reverse traceability is validated;

- no active use case lacks design coverage;

- no major design element lacks justification;

- no blocking traceability gap remains.

---

## 181. Design Traceability Result

Requirements-to-design traceability:

```text

COMPLETE

```

Active V1.0.0 use cases mapped:

```text

24 / 24

```

Deferred use cases:

```text

UC-008

```

Blocking traceability gaps:

```text

0

```

Unapproved scope additions:

```text

0

```

Readiness for System Design validation:

```text

READY

```

Next design artifact:

```text

16-system-design-validation.md

```

---

## 182. Document Status

This document is currently:

```text

Approved

```

It will be changed to:

```text

Approved

```

after requirements-to-design coverage, use-case coverage, architecture justification, component coverage, database/API/authorization/storage/Git/UI/deployment/observability/security traceability, verification mapping, reverse traceability, and scope preservation have been reviewed.
