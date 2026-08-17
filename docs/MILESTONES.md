# GitForge V1.0.0 Milestones

## 1. Purpose

This document tracks measurable progress toward GitForge V1.0.0.

The SDLC lifecycle is defined separately in `SDLC.md`. Each milestone may
contain activities from multiple SDLC phases.

A milestone is complete only when:

- All deliverables are present.
- All exit criteria are satisfied.
- Verification evidence is available.
- Changes are merged into `main`.

---

## 2. Status Definitions

| Status | Meaning |
|---|---|
| Not started | Work has not begun |
| In progress | Some deliverables exist, but exit criteria are incomplete |
| Implemented | Functionality exists, but documentation or verification remains |
| Complete | Deliverables and exit criteria are fully satisfied |

---

## M0 - Project Definition

**SDLC phase:** Project Definition
**Status:** In progress

### Deliverables

- Project charter
- Problem statement
- Product vision
- V1.0.0 goals
- Stakeholder list
- Project scope
- Constraints and assumptions
- Feasibility assessment
- Initial risk register
- Success metrics
- Development strategy
- Release strategy

### Exit criteria

- The business and engineering problem is clearly documented.
- V1.0.0 scope and exclusions are explicit.
- Stakeholders and constraints are identified.
- Major risks and success metrics are documented.
- The project charter is reviewed.

### Current evidence

- Product direction has been established.
- V1.0.0 is defined as the current release target.
- Laravel, React, PostgreSQL, and managed Git storage were selected.
- A feature-branch and pull-request workflow is operational.

### Remaining work

- Create and review the formal project-definition artifacts.

---

## M1 - Requirements Engineering

**SDLC phase:** Requirements Engineering
**Status:** In progress

### Deliverables

- Functional requirements
- Non-functional requirements
- User roles
- User stories
- Use cases
- Acceptance criteria
- Authorization requirements
- Security requirements
- Requirements traceability matrix
- V1.0.0 scope specification

### Exit criteria

- Every V1.0.0 feature has an identified requirement.
- Every requirement has acceptance criteria.
- Security and operational requirements are included.
- Requirements are uniquely identified.
- Requirements are traceable to implementation and tests.

### Current evidence

Implemented behavior provides evidence for requirements covering:

- Authentication
- Repository management
- Repository visibility
- Repository lifecycle operations
- Git Smart HTTP
- Personal access tokens
- Repository browsing

### Remaining work

- Convert implemented behavior into formal requirements.
- Add identifiers and traceability.
- Confirm the remaining V1.0.0 scope.

---

## M2 - System Analysis

**SDLC phase:** System Analysis
**Status:** In progress

### Deliverables

- System context analysis
- Actor analysis
- Domain model
- Repository lifecycle analysis
- Authentication workflow analysis
- Authorization analysis
- Git Smart HTTP workflow analysis
- Access token lifecycle analysis
- Repository browser analysis
- Failure-state analysis
- Security threat analysis
- PlantUML analysis diagrams

### Exit criteria

- Actors and system boundaries are defined.
- Primary and alternate workflows are documented.
- Domain entities and relationships are analyzed.
- Trust boundaries and threats are identified.
- Failure and recovery behavior is documented.
- Analysis artifacts map to requirements.

### Current evidence

The implemented backend, frontend, tests, policies, services, and database
models provide source material for the analysis.

### Remaining work

- Reconstruct and review the formal analysis artifacts.
- Add the necessary PlantUML diagrams.

---

## M3 - System Design

**SDLC phase:** System Design
**Status:** In progress

### Deliverables

- Architecture decisions
- System architecture
- Container architecture
- Component design
- Database design
- API specification
- Authorization matrix
- Repository-storage design
- Git process-execution design
- Git Smart HTTP design
- Access token design
- Repository browser design
- Frontend information architecture
- Wireframes
- Deployment architecture
- Observability design
- Security design
- PlantUML design diagrams

### Exit criteria

- Every major requirement maps to a component.
- API contracts and database structures are documented.
- Authorization rules are explicit.
- Git execution and storage boundaries are documented.
- Security and deployment designs are complete.
- Major design decisions are recorded.
- Verification approaches are identified.

### Current evidence

The implemented system contains working architectural decisions for:

- Laravel REST APIs
- React and TypeScript frontend
- PostgreSQL persistence
- Laravel Sanctum authentication
- Managed bare Git repositories
- Git subprocess execution
- Git Smart HTTP transport
- Asynchronous repository deletion
- Scoped personal access tokens
- Repository browsing

### Remaining work

- Document the implemented architecture.
- Create design diagrams.
- Record important architectural decisions.

---

## M4 - Application Foundation and Authentication

**SDLC phases:** Implementation and Testing
**Status:** Complete

### Deliverables

- Laravel backend foundation
- React and TypeScript frontend foundation
- PostgreSQL configuration
- User registration
- User login
- Authenticated profile retrieval
- Logout
- Frontend authentication flow
- Protected and guest routes
- Authentication tests
- Continuous integration checks

### Exit criteria

- Users can register, sign in, retrieve their profile, and sign out.
- Protected routes reject unauthenticated users.
- Authentication data is securely stored and transmitted.
- Backend and frontend authentication tests pass.
- The implementation is merged into `main`.

### Evidence

- Backend authentication feature tests passed.
- Frontend authentication tests passed.
- ESLint and production builds passed.
- Authentication implementation was merged into `main`.

---

## M5 - Repository Domain and Managed Storage

**SDLC phases:** Implementation and Testing
**Status:** Complete

### Deliverables

- Repository database model
- Repository ownership
- Public and private visibility
- Repository creation API
- Repository listing and detail APIs
- Repository update API
- Archive and unarchive operations
- Coordinated deletion lifecycle
- Managed bare Git storage
- Storage cleanup job
- Backend tests
- Frontend repository management

### Exit criteria

- Authenticated users can manage their repositories.
- Repository names are unique within an owner namespace.
- Private repositories enforce access control.
- Bare repositories are safely initialized and deleted.
- Repository deletion is recoverable and asynchronous.
- Backend and frontend tests pass.
- The implementation is merged into `main`.

### Evidence

- Repository API and storage tests passed.
- Repository lifecycle jobs were tested.
- Frontend repository management passed tests, lint, and build.
- Repository management was merged into `main`.

---

## M6 - Git Transport and Repository Credentials

**SDLC phases:** Implementation and Testing
**Status:** Complete

### Deliverables

- Git Smart HTTP discovery
- Upload-pack support
- Receive-pack support
- Private repository authentication
- Push authorization
- Archived repository write protection
- Personal access token API
- Read and write token abilities
- Token expiration
- Token revocation
- Frontend token management
- Default-branch synchronization

### Exit criteria

- Public repositories can be cloned according to visibility rules.
- Private repositories require a valid scoped token.
- Read-only tokens cannot push.
- Write-enabled tokens can push to active repositories.
- Expired and revoked tokens are rejected.
- Successful pushes synchronize repository metadata.
- Automated and manual Git transport tests pass.
- The implementation is merged into `main`.

### Evidence

- Git Smart HTTP tests passed.
- Token ability tests passed.
- Manual private clone and push tests passed.
- Default branch synchronized to `main`.
- Access token functionality was merged into `main`.

---

## M7 - Repository Browser

**SDLC phases:** Implementation and Testing
**Status:** Complete

### Deliverables

- Branch-listing API
- Commit-history API
- Repository tree API
- Text blob API
- Branch and path validation
- Blob-size limit
- Binary blob rejection
- Repository browser service tests
- Repository browser API tests
- Frontend branch selector
- Directory navigation
- Breadcrumb navigation
- Text file viewer
- Recent commit display
- Frontend service tests

### Exit criteria

- Authorized users can browse repository branches.
- Authorized users can inspect recent commits.
- Directory trees can be navigated securely.
- Supported text files preserve their content.
- Invalid branches and paths are rejected.
- Oversized and unsupported blobs are controlled.
- Automated backend and frontend tests pass.
- GitHub Actions checks pass.
- The implementation is merged into `main`.

### Evidence

- Backend repository browser tests passed.
- Frontend repository browser tests passed.
- Laravel Pint and ESLint passed.
- Production frontend build passed.
- Manual repository browser verification passed.
- Repository browser implementation was merged into `main`.

---

## M8 - V1.0.0 Quality and Production Readiness

**SDLC phases:** Testing and Deployment
**Status:** Not started

### Deliverables

- Confirmed V1.0.0 requirements
- Requirements traceability matrix
- Complete backend test suite
- Complete frontend test suite
- End-to-end critical-path tests
- Security review
- Performance review
- Route-level frontend code splitting
- Production environment specification
- Deployment configuration
- Queue-worker configuration
- Web-server and TLS configuration
- Backup and restoration procedure
- Logging and monitoring
- Deployment checklist
- Rollback procedure
- Release runbook

### Exit criteria

- Every approved V1.0.0 requirement is implemented.
- All automated and end-to-end tests pass.
- No unresolved critical security issue remains.
- The production frontend has no avoidable bundle warning.
- A production-like deployment is successful.
- Backup and restoration are verified.
- Monitoring and rollback procedures are tested.
- A release candidate is approved.

---

## M9 - V1.0.0 Release and Maintenance Readiness

**SDLC phases:** Deployment and Maintenance
**Status:** Not started

### Deliverables

- Final release notes
- V1.0.0 changelog
- Installation documentation
- Administrator documentation
- User documentation
- API documentation
- Security guidance
- Maintenance policy
- Incident procedure
- Dependency-update procedure
- Versioning policy
- Git tag `v1.0.0`
- GitHub V1.0.0 release

### Exit criteria

- The V1.0.0 Definition of Done is satisfied.
- Final documentation is reviewed.
- Production deployment is verified.
- Release notes and changelog are complete.
- The `v1.0.0` tag is created from the approved commit.
- The GitHub release is published.
- Maintenance responsibilities are documented.
- V2.0.0 work remains separate from V1.0.0.

---

## 3. Milestone Summary

| Milestone | Status |
|---|---|
| M0 - Project Definition | In progress |
| M1 - Requirements Engineering | In progress |
| M2 - System Analysis | In progress |
| M3 - System Design | In progress |
| M4 - Application Foundation and Authentication | Complete |
| M5 - Repository Domain and Managed Storage | Complete |
| M6 - Git Transport and Repository Credentials | Complete |
| M7 - Repository Browser | Complete |
| M8 - V1.0.0 Quality and Production Readiness | Not started |
| M9 - V1.0.0 Release and Maintenance Readiness | Not started |

---

## 4. Current Position

The application has progressed through M7 from an implementation perspective.

However, M0 through M3 remain in progress because their formal SDLC artifacts
have not yet been committed. These milestones must be completed
retrospectively from verified implementation evidence before V1.0.0 can enter
production readiness.

The next active milestone is:

> M0 - Project Definition

After completing M0, documentation should proceed sequentially through M1,
M2, and M3. Feature development should resume only after the remaining
V1.0.0 requirements have been confirmed.
