# GitForge V1.0.0 System Design Plan

## 1. Document Information

| Field | Value |

|---|---|

| Project | GitForge - Web Git Hosting Platform |

| Target release | V1.0.0 |

| Document | System Design Plan |

| SDLC phase | System Design |

| Milestone | M3 - System Design |

| Project owner | Farhad Valizadegan |

| Status | Approved |

| Last updated | 2026-08-17 |

---

## 2. Purpose

This document defines the plan, scope, principles, deliverables, validation rules, and exit criteria for GitForge V1.0.0 System Design.

The System Design phase converts the approved logical System Analysis into a concrete, implementable technical design.

It establishes:

- architecture decisions;

- system containers;

- components and responsibilities;

- database structures;

- API contracts;

- authorization design;

- repository-storage design;

- Git process-execution design;

- UI information architecture;

- wireframes;

- deployment design;

- observability design;

- security design;

- design traceability;

- design validation.

The goal is to produce a complete implementation-ready design without prematurely implementing production code.

---

## 3. SDLC Position

Current SDLC position:

```text

1. Project Definition            ✅ Complete

2. Requirements Engineering     ✅ Complete

3. System Analysis              ✅ Complete

4. System Design                🟡 In Progress

5. Implementation

6. Testing

7. Deployment

8. Maintenance

```

Current milestone:

```text

M3 — System Design

```

---

## 4. Upstream Inputs

System Design is based on approved artifacts from:

```text

docs/Project-Definition/

docs/SDLC/02-requirements-engineering/

docs/SDLC/03-system-analysis/

```

System Analysis is considered complete and authoritative for logical system behavior.

System Design must not silently redefine approved requirements or M2 analysis conclusions.

---

## 5. Primary Design Objective

M3 must answer:

> How will GitForge V1.0.0 be structured and implemented technically while preserving all approved requirements, system-analysis invariants, security rules, failure semantics, and scope boundaries?

The design must be sufficiently concrete for implementation to begin without major architectural re-analysis.

---

## 6. System Design Scope

M3 includes:

```text

Architecture decisions

Container architecture

Component design

Database schema design

API specification

Authorization design

Repository-storage design

Git process-execution design

UI information architecture

Wireframes

Deployment design

Observability design

Security design

Design traceability

System Design validation

Milestone review

```

---

## 7. Out-of-Scope Activities

M3 does not include:

```text

Production implementation

Feature coding

Database migration execution

Production deployment

Test execution

Production infrastructure provisioning

Operational monitoring execution

Maintenance activities

```

These belong to later SDLC phases.

---

## 8. Design Principles

System Design follows these principles:

```text

Requirements remain authoritative

System Analysis remains authoritative for logical behavior

Prefer simple architecture suitable for V1.0.0

Avoid unnecessary enterprise complexity

Preserve clear responsibility boundaries

Authentication and authorization remain separate

Application metadata and Git-native data remain separate

External input remains untrusted

Authorization precedes protected repository access

Physical repository paths are internally controlled

Git process execution is constrained

Security is cross-cutting

Failures must remain fail-closed

Design decisions must be traceable

Implementation detail must be sufficient but not speculative

```

---

## 9. Pragmatic V1.0.0 Design Principle

GitForge V1.0.0 is a portfolio-scale Git hosting platform.

Therefore M3 should prefer:

```text

Clear

Maintainable

Testable

Secure

Locally deployable

Understandable

Portfolio-demonstrable

```

over:

```text

Premature microservices

Distributed systems

Multi-region infrastructure

Enterprise identity federation

Complex orchestration

Unnecessary abstraction layers

```

---

## 10. Technology Context

Known technical context includes:

```text

Backend:

Laravel 12

Language/runtime:

PHP 8.4

Database:

PostgreSQL 18

Git:

System Git executable

Platform:

Web application

Local development:

Windows + XAMPP environment

Repository protocol:

Git Smart HTTP

```

System Design may define how these technologies are used, but must remain aligned with approved V1.0.0 scope.

---

## 11. Architecture Design Boundary

The architecture will define concrete technical structure such as:

```text

Web application boundary

Backend application responsibilities

Database access

Repository-storage access

Git process execution

Git Smart HTTP handling

Browser/API interfaces

Operational interfaces

```

Architecture must not introduce functionality excluded from V1.0.0.

---

## 12. Planned M3 Deliverables

The planned M3 deliverables are:

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

15-design-traceability.md

16-system-design-validation.md

17-milestone-review.md

```

Total planned deliverables:

```text

17

```

---

## 13. Deliverable 01 — System Design Plan

Purpose:

```text

Define M3 scope, process, principles, deliverables, validation, and exit criteria.

```

Output:

```text

01-system-design-plan.md

```

---

## 14. Deliverable 02 — Architecture Decisions

Purpose:

```text

Document major architectural decisions and their rationale.

```

Expected areas include:

```text

Application architecture style

Backend/web boundary

Database authority

Git storage authority

Git Smart HTTP handling

Process execution model

Repository storage model

Authentication approach

PAT handling approach

Deployment assumptions

```

Output:

```text

02-architecture-decisions.md

```

---

## 15. Deliverable 03 — Container Architecture

Purpose:

```text

Define the major runtime/deployment containers and their relationships.

```

Expected containers may include:

```text

Web client/browser

Laravel application

PostgreSQL

Repository storage

Git executable/process boundary

Reverse proxy/web server where applicable

```

The final container model must be justified by architecture decisions.

Output:

```text

03-container-architecture.md

```

---

## 16. Deliverable 04 — Component Design

Purpose:

```text

Translate logical M2 responsibilities into concrete application components.

```

Expected component areas include:

```text

Authentication

Repository management

Repository authorization

Repository browsing

PAT management

Git Smart HTTP

Repository storage

Git process execution

Persistence

Failure handling

Diagnostics

```

Output:

```text

04-component-design.md

```

---

## 17. Deliverable 05 — Database Schema Design

Purpose:

```text

Define PostgreSQL structures required for authoritative application metadata.

```

Expected data areas include:

```text

Users

Repositories

Repository ownership

Visibility

Personal Access Tokens

PAT revocation state

Timestamps

Constraints

Indexes

Relationships

```

Git-native objects must not be unnecessarily duplicated into PostgreSQL.

Output:

```text

05-database-schema-design.md

```

---

## 18. Deliverable 06 — API Specification

Purpose:

```text

Define application-facing interfaces and contracts.

```

Expected capabilities include:

```text

Registration

Authentication

Logout

Repository creation

Repository listing

Repository viewing

Visibility update

Repository browser operations

PAT creation

PAT listing where required

PAT revocation

```

The API specification should define:

```text

Routes/endpoints

Methods

Authentication requirements

Authorization requirements

Request structures

Response structures

Validation expectations

Failure semantics

```

Output:

```text

06-api-specification.md

```

---

## 19. Deliverable 07 — Authorization Design Matrix

Purpose:

```text

Convert approved authorization requirements into implementation-ready authorization rules.

```

Core policy remains:

```text

Public read:

Visitor                  Allow

Authenticated Non-Owner  Allow

Owner                    Allow

Private read:

Visitor                  Deny

Authenticated Non-Owner  Deny

Owner                    Allow

Write:

Anonymous                 Deny

Authenticated Non-Owner   Deny

Owner                     Allow

```

Output:

```text

07-authorization-design-matrix.md

```

---

## 20. Deliverable 08 — Repository Storage Design

Purpose:

```text

Define concrete physical repository storage behavior.

```

Expected areas include:

```text

Repository root

Directory naming/mapping

Logical-to-physical mapping

Path containment

Repository initialization

Repository existence checking

Empty repository behavior

Missing repository behavior

Orphan detection

Storage failure handling

Filesystem permissions assumptions

```

Output:

```text

08-repository-storage-design.md

```

---

## 21. Deliverable 09 — Git Process Execution Design

Purpose:

```text

Define safe execution of Git-native operations.

```

Expected areas include:

```text

Approved Git executable

Approved subcommands/services

Argument construction

No arbitrary shell execution

Environment handling

Repository target handling

stdin/stdout/stderr handling

Timeouts

Process termination

Exit-code handling

git-upload-pack

git-receive-pack

Read-only inspection commands

Failure propagation

```

Output:

```text

09-git-process-execution-design.md

```

---

## 22. Deliverable 10 — UI Information Architecture

Purpose:

```text

Define the structure and navigation of the V1.0.0 web interface.

```

Expected areas include:

```text

Public pages

Authentication pages

Repository dashboard

Repository detail

Repository browser

PAT management

Navigation hierarchy

Page relationships

```

Output:

```text

10-ui-information-architecture.md

```

---

## 23. Deliverable 11 — Wireframes

Purpose:

```text

Provide implementation-oriented low-fidelity interface layouts.

```

Expected wireframes may include:

```text

Login

Registration

Repository list

Create repository

Repository overview

Repository tree

File view

Commit history

Commit details

PAT management

```

PlantUML may be used where appropriate.

Output:

```text

11-wireframes.md

```

---

## 24. Deliverable 12 — Deployment Design

Purpose:

```text

Define how GitForge V1.0.0 is deployed and operated.

```

Expected areas include:

```text

Application runtime

Web server/reverse proxy

PHP runtime

PostgreSQL

Filesystem repository storage

Git executable

Environment variables

Secrets

Writable directories

Network boundaries

Backup-relevant assets

```

Output:

```text

12-deployment-design.md

```

---

## 25. Deliverable 13 — Observability Design

Purpose:

```text

Define operational visibility required for V1.0.0.

```

Expected areas include:

```text

Application logging

Security-relevant logging

Git process diagnostics

Repository storage failures

Database failures

Request correlation

Health checks

Operational signals

Sensitive-data suppression

```

Output:

```text

13-observability-design.md

```

---

## 26. Deliverable 14 — Security Design

Purpose:

```text

Convert M2 trust-boundary and security analysis into concrete controls.

```

Expected areas include:

```text

Password handling

Session authentication

PAT generation

PAT verification storage

PAT revocation

Authorization enforcement

Input validation

Path safety

Git process safety

Repository content safety

Output encoding

Secret handling

CSRF

XSS

SQL injection protection

Diagnostic sanitization

Transport security assumptions

Filesystem access control

```

Output:

```text

14-security-design.md

```

---

## 27. Deliverable 15 — Design Traceability

Purpose:

```text

Map approved requirements and System Analysis conclusions to concrete System Design elements.

```

Traceability should connect:

```text

Requirement

       ↓

Analysis

       ↓

Architecture / Component / Data / API / Security Design

```

Output:

```text

15-design-traceability.md

```

---

## 28. Deliverable 16 — System Design Validation

Purpose:

```text

Validate completeness, consistency, traceability, security, and implementation readiness of M3.

```

Validation should verify:

```text

Architecture consistency

Component consistency

Database consistency

API consistency

Authorization consistency

Storage consistency

Git process consistency

UI consistency

Deployment consistency

Security consistency

Requirements traceability

Scope preservation

Implementation readiness

```

Output:

```text

16-system-design-validation.md

```

---

## 29. Deliverable 17 — Milestone Review

Purpose:

```text

Perform formal M3 closure review.

```

The review determines whether the project may proceed to:

```text

SDLC Phase 5 — Implementation

```

Output:

```text

17-milestone-review.md

```

---

# Design Workflow

## 30. M3 Work Sequence

Recommended sequence:

```text

System Design Plan

       ↓

Architecture Decisions

       ↓

Container Architecture

       ↓

Component Design

       ↓

Database Schema

       ↓

API Specification

       ↓

Authorization Design

       ↓

Repository Storage Design

       ↓

Git Process Execution Design

       ↓

UI Information Architecture

       ↓

Wireframes

       ↓

Deployment Design

       ↓

Observability Design

       ↓

Security Design

       ↓

Design Traceability

       ↓

System Design Validation

       ↓

Milestone Review

```

---

## 31. Design Dependency Principle

Later design artifacts may depend on earlier ones.

Examples:

```text

Component Design

depends on

Architecture Decisions

```

```text

API Specification

depends on

Component and Domain Design

```

```text

Repository Storage Design

depends on

Architecture + Analysis Storage Semantics

```

```text

Security Design

depends on

Architecture + Components + API + Storage + Git Execution

```

---

## 32. Controlled Design Decision Rule

Every major design decision should:

```text

Identify the problem

List relevant constraints

State the decision

Provide rationale

Identify consequences

Identify affected requirements

```

Major decisions should not be implicit.

---

## 33. Requirement Conflict Rule

If System Design reveals a contradiction in approved requirements:

```text

Do not silently modify the requirement.

```

Instead:

```text

Identify the conflict

Document impact

Raise controlled requirement change

Update affected upstream artifacts if approved

Resume design afterward

```

---

## 34. Analysis Conflict Rule

If System Design appears to require behavior that contradicts M2:

```text

Verify approved requirements first.

```

If M2 is incorrect:

```text

Update M2 through controlled analysis correction.

```

If requirements are incorrect:

```text

Use controlled requirements change.

```

---

## 35. Scope Change Rule

M3 must not introduce:

```text

Organizations

Teams

Collaborators

Forks

Pull requests

Issues

Boards

CI/CD

Wiki

Package registry

Container registry

Git LFS

SSH Git transport

Billing

Enterprise ACLs

Federation

Multi-region design

Analytics

Social features

```

unless V1.0.0 scope is formally changed.

---

## 36. Deferred Feature Rule

The following remain deferred:

```text

Repository deletion

Repository rename

Repository transfer

Repository description

Account deletion

PAT scopes

PAT expiration

```

Design should not create mandatory dependencies on these features.

---

# Design Modeling

## 37. Diagram Strategy

PlantUML should be used where diagrams improve clarity.

Expected diagrams include:

```text

Architecture diagrams

Container diagrams

Component diagrams

ER/data diagrams

Authorization flows

Repository storage diagrams

Git process-execution diagrams

Deployment diagrams

Security/trust diagrams

UI/navigation diagrams

```

---

## 38. Diagram Purpose Rule

Diagrams must answer a concrete design question.

Do not add diagrams only for documentation volume.

---

## 39. Architecture Diagram Level

Architecture diagrams should distinguish:

```text

External actors

Runtime containers

Internal components

Persistence

Filesystem storage

Git execution

External protocol boundaries

```

---

## 40. Database Diagram Level

Database diagrams should define concrete entities and relationships.

Expected model:

```text

users

repositories

personal_access_tokens

```

with concrete attributes, keys, constraints, and indexes established in the database design artifact.

---

# Security Design Planning

## 41. Security as Cross-Cutting Design

Security must be addressed throughout:

```text

Architecture

API

Authorization

Database

Repository storage

Git execution

UI

Deployment

Observability

```

Security is not isolated to `14-security-design.md`.

---

## 42. Security-Critical Areas

The following require explicit design treatment:

```text

Password verification

Session authentication

PAT generation

PAT storage

PAT authentication

PAT revocation

Repository authorization

Path containment

Git process execution

Repository content rendering

Diagnostics

Secrets

Database access

Filesystem permissions

```

---

## 43. Authentication/Authorization Separation

Concrete implementation must preserve:

```text

Authentication

       ↓

Identity

```

and:

```text

Identity + Resource + Operation

       ↓

Authorization

```

These must not collapse into a single implicit check.

---

## 44. Repository Path Safety

Design must guarantee:

```text

External logical repository identity

       ↓

Validated internal mapping

       ↓

Configured repository root containment

       ↓

Physical repository target

```

Raw client filesystem paths are prohibited.

---

## 45. Git Process Safety

Design must guarantee:

```text

No arbitrary shell execution

Approved Git executable only

Approved operations only

Controlled arguments

Controlled environment

Safe physical target

Controlled input/output

Timeout and termination behavior

```

---

## 46. Repository Content Safety

Git repository content must remain untrusted.

Design must account for safe handling of:

```text

Commit messages

Branch/tag names

File names

Paths

Blob content

Text/binary files

Potentially malicious content

```

---

# Database Design Planning

## 47. Database Authority

PostgreSQL remains authoritative for:

```text

Users

Repository metadata

Ownership

Visibility

PAT metadata

PAT revocation state

```

---

## 48. Git-Native Authority

Physical Git repositories remain authoritative for:

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

System Design must preserve this separation.

---

## 49. Database Integrity Planning

Database design must define:

```text

Primary keys

Foreign keys

Unique constraints

Nullability

Visibility constraints

Ownership constraints

PAT ownership

Indexes

Timestamps

Deletion behavior for supported entities

```

---

# API Design Planning

## 50. API Design Principles

API design should be:

```text

Consistent

Predictable

REST-oriented where appropriate

Authorization-aware

Validation-aware

Failure-aware

Implementation-ready

```

---

## 51. API Security

Every protected endpoint must specify:

```text

Authentication requirement

Authorization requirement

Input validation

Failure behavior

Sensitive response constraints

```

---

# UI Design Planning

## 52. UI Scope

V1.0.0 UI should support only approved functionality.

Primary areas:

```text

Authentication

Repository management

Repository browsing

PAT management

```

---

## 53. UI Simplicity

UI design should prioritize:

```text

Clear navigation

Low cognitive complexity

Direct task completion

Portfolio-quality presentation

Responsive structure

Accessible semantic layout

```

---

# Deployment Planning

## 54. Deployment Design Principle

Deployment design should be sufficient for a realistic V1.0.0 installation without unnecessary production-scale complexity.

The deployment model should clarify:

```text

Application runtime

Database

Repository filesystem

Git executable

Web ingress

Secrets

Persistent data

Operational logs

```

---

## 55. Persistent Assets

Persistent assets include at least:

```text

PostgreSQL data

Physical Git repositories

Application secrets/configuration

Operationally relevant logs where applicable

```

---

# Observability Planning

## 56. Required Operational Visibility

M3 must define how operators can understand failures involving:

```text

Authentication

Authorization

Database

Repository storage

Git process execution

Git Smart HTTP

Application failures

Security-sensitive events

```

---

## 57. Sensitive Logging Rule

Observability must not intentionally record:

```text

Plaintext PAT

Plaintext password

Database password

Application secret

Raw Authorization header

Unnecessary repository file content

```

---

# Traceability Planning

## 58. Design Traceability Direction

Traceability must support:

```text

Requirement

    ↓

Analysis

    ↓

Design

    ↓

Implementation

    ↓

Testing

```

M3 establishes the Design portion of this chain.

---

## 59. Design Gap Classification

Potential M3 gaps include:

```text

DG-01 Missing architecture decision

DG-02 Missing component responsibility

DG-03 Missing database representation

DG-04 Missing API contract

DG-05 Missing authorization rule

DG-06 Missing storage design

DG-07 Missing Git execution design

DG-08 Missing UI path

DG-09 Missing deployment design

DG-10 Missing observability design

DG-11 Missing security control

DG-12 Missing requirement traceability

DG-13 Contradictory design

DG-14 Unapproved scope addition

```

---

## 60. Blocking Design Gap Rule

Unresolved gaps affecting:

```text

Authentication

Authorization

Repository creation

Repository browsing

PAT lifecycle

Git read

Git write

Repository storage

Database integrity

Git process safety

Security boundary

```

block Implementation entry.

---

# M3 Exit Criteria

## 61. Exit Criteria

M3 exit criteria are:

```text

EC-01 Architecture decisions documented

EC-02 System containers defined

EC-03 Components and responsibilities defined

EC-04 Database schema defined

EC-05 API contract defined

EC-06 Authorization design defined

EC-07 Repository-storage design defined

EC-08 Git process-execution design defined

EC-09 UI information architecture defined

EC-10 Required wireframes defined

EC-11 Deployment design defined

EC-12 Observability design defined

EC-13 Security design defined

EC-14 Major requirements traceable to design

EC-15 Design internally consistent

EC-16 No blocking design defect remains

EC-17 Design sufficient for Implementation

```

---

## 62. EC-01 — Architecture Decisions Documented

Pass condition:

```text

All architecture-shaping decisions required for V1.0.0 are explicit and justified.

```

---

## 63. EC-02 — System Containers Defined

Pass condition:

```text

Major runtime/deployment containers and their relationships are defined.

```

---

## 64. EC-03 — Components and Responsibilities Defined

Pass condition:

```text

Logical M2 responsibilities are mapped to concrete implementation components.

```

---

## 65. EC-04 — Database Schema Defined

Pass condition:

```text

Authoritative application metadata has an implementation-ready PostgreSQL schema.

```

---

## 66. EC-05 — API Contract Defined

Pass condition:

```text

Required application interfaces are implementation-ready.

```

---

## 67. EC-06 — Authorization Design Defined

Pass condition:

```text

Read, write, and metadata mutation rules have explicit enforcement points.

```

---

## 68. EC-07 — Repository-Storage Design Defined

Pass condition:

```text

Physical storage mapping, containment, initialization, and failure handling are implementation-ready.

```

---

## 69. EC-08 — Git Process-Execution Design Defined

Pass condition:

```text

Git invocation, arguments, process lifecycle, input/output, and failure behavior are implementation-ready.

```

---

## 70. EC-09 — UI Information Architecture Defined

Pass condition:

```text

Primary pages and navigation relationships are defined.

```

---

## 71. EC-10 — Required Wireframes Defined

Pass condition:

```text

Critical V1.0.0 user workflows have usable wireframes.

```

---

## 72. EC-11 — Deployment Design Defined

Pass condition:

```text

Application, database, Git repository storage, Git executable, and ingress deployment relationships are defined.

```

---

## 73. EC-12 — Observability Design Defined

Pass condition:

```text

Application and operational failures can be diagnosed safely.

```

---

## 74. EC-13 — Security Design Defined

Pass condition:

```text

Security-critical analysis findings are mapped to concrete controls.

```

---

## 75. EC-14 — Requirements Traceable to Design

Pass condition:

```text

Major V1.0.0 requirements and analysis conclusions map to design elements.

```

---

## 76. EC-15 — Design Internally Consistent

Pass condition:

```text

Architecture, components, database, API, authorization, storage, Git execution, UI, deployment, observability, and security do not contradict each other.

```

---

## 77. EC-16 — No Blocking Design Defect Remains

Pass condition:

```text

Critical defects = 0

Unresolved Major defects = 0

Blocking design gaps = 0

```

---

## 78. EC-17 — Design Sufficient for Implementation

Pass condition:

```text

Implementation can begin without requiring major unresolved architecture or behavior decisions.

```

---

# Validation Strategy

## 79. Per-Document Validation

Each design artifact should be checked for:

```text

Correct document metadata

Approved requirements alignment

M2 analysis alignment

No accidental scope expansion

Internal consistency

Valid diagrams

No malformed Markdown

No unresolved Draft marker after approval

```

---

## 80. M3 Cross-Artifact Validation

Before milestone closure, validate:

```text

Architecture ↔ Components

Components ↔ Database

Components ↔ API

API ↔ Authorization

Authorization ↔ Security

Repository Storage ↔ Git Execution

Git Execution ↔ Deployment

UI ↔ API

Observability ↔ Failure Handling

Security ↔ All Design Areas

Traceability ↔ Requirements

```

---

## 81. Approval Workflow

Each deliverable follows:

```text

Create Draft

       ↓

Validate

       ↓

Review

       ↓

Approve

```

The milestone is committed only after the full M3 set is validated.

---

## 82. Commit Strategy

Preferred M3 workflow:

```text

Complete all M3 deliverables

       ↓

Validate entire M3 directory

       ↓

Approve milestone

       ↓

Stage all M3 artifacts

       ↓

Commit as one coherent milestone

```

Recommended final commit message:

```text

docs: complete M3 system design

```

---

# Risks

## 83. Design Risk — Premature Complexity

Risk:

```text

Design becomes unnecessarily enterprise-scale.

```

Mitigation:

```text

Keep V1.0.0 architecture pragmatic and directly tied to approved requirements.

```

---

## 84. Design Risk — Framework-Driven Design

Risk:

```text

Laravel conventions replace actual system design reasoning.

```

Mitigation:

```text

Define system responsibilities first, then map them to Laravel constructs.

```

---

## 85. Design Risk — Security Drift

Risk:

```text

Implementation convenience weakens M2 security invariants.

```

Mitigation:

```text

Explicit security design and design traceability.

```

---

## 86. Design Risk — Git Complexity

Risk:

```text

Git Smart HTTP or process execution is underspecified.

```

Mitigation:

```text

Dedicated Git process-execution and repository-storage design artifacts.

```

---

## 87. Design Risk — Metadata/Git-State Duplication

Risk:

```text

Application database begins duplicating Git-native authority.

```

Mitigation:

```text

Maintain strict authority separation defined by M2.

```

---

## 88. Design Risk — Incomplete Failure Design

Risk:

```text

Happy-path design ignores operational failures.

```

Mitigation:

```text

Every major design artifact incorporates failure behavior from M2.

```

---

# M3 Completion Definition

## 89. M3 Is Complete When

M3 is complete only when:

- all 17 planned deliverables exist;

- all 17 deliverables are Approved;

- all 17 exit criteria pass;

- architecture decisions are explicit;

- container architecture is defined;

- concrete components are defined;

- PostgreSQL schema is implementation-ready;

- application API contract is implementation-ready;

- authorization enforcement design is explicit;

- repository storage is implementation-ready;

- Git execution is implementation-ready;

- UI information architecture is defined;

- required wireframes are defined;

- deployment design is complete;

- observability design is complete;

- security design is complete;

- major requirements are traceable to design;

- design validation passes;

- Critical defects equal zero;

- unresolved Major defects equal zero;

- blocking design gaps equal zero;

- Implementation readiness is confirmed.

---

## 90. Planned M3 Result

Target milestone result:

```text

M3 — SYSTEM DESIGN COMPLETE

```

Authorized next SDLC phase after successful closure:

```text

5. Implementation

```

---

## 91. Current Milestone Status

Current status:

```text

M3 — System Design

STARTED

```

Current deliverable:

```text

01-system-design-plan.md

```

Progress before approval:

```text

0 / 17 Approved

```

---

## 92. Document Status

This document is currently:

```text

Approved

```

It will be changed to:

```text

Approved

```

after scope, design principles, deliverables, workflow, validation strategy, risks, exit criteria, and Implementation-readiness conditions have been reviewed.
