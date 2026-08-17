# Software Development Life Cycle

## GitForge - Web Git Hosting Platform

**Target release:** V1.0.0

**Development approach:** Classic eight-phase SDLC

**Milestone tracking:** Maintained separately in `MILESTONES.md`

---

## 1. Purpose

This document defines the Software Development Life Cycle used to develop GitForge.

GitForge is a self-hosted Git hosting platform that provides repository management, Git Smart HTTP transport, authentication, scoped access tokens, and repository browsing through a web application.

The SDLC describes the engineering lifecycle of the project. Implementation progress is tracked separately through milestones containing deliverables and exit criteria.

---

## 2. SDLC Phases

GitForge follows these eight phases:

1. Project Definition
2. Requirements Engineering
3. System Analysis
4. System Design
5. Implementation
6. Testing
7. Deployment
8. Maintenance

The phases provide logical engineering boundaries. Necessary feedback and
controlled revision between phases are permitted.

---

## 3. Phase 1 ÃƒÆ’Ã‚Â¢ÃƒÂ¢Ã¢â‚¬Å¡Ã‚Â¬ÃƒÂ¢Ã¢â€šÂ¬Ã‚Â Project Definition

### Purpose

Establish why GitForge should exist, what problem it solves, and what the V1.0.0 release must accomplish.

### Required artifacts

- Project charter
- Problem statement
- Product vision
- Project goals
- Stakeholder identification
- Project scope
- Constraints and assumptions
- Feasibility assessment
- Initial risk register
- Success metrics
- Development and release strategy

### Current status

**Engineering work performed; formal documentation required.**

The product direction and technical scope have been established during development, but the corresponding project-definition documents must be reconstructed and reviewed.

### Exit criteria

- The problem and product vision are documented.
- V1.0.0 goals and exclusions are explicit.
- Stakeholders and constraints are identified.
- Major risks and success measurements are recorded.
- The project charter is approved.

---

## 4. Phase 2 ÃƒÆ’Ã‚Â¢ÃƒÂ¢Ã¢â‚¬Å¡Ã‚Â¬ÃƒÂ¢Ã¢â€šÂ¬Ã‚Â Requirements Engineering

### Purpose

Define the functional and non-functional behavior required from GitForge V1.0.0.

### Required artifacts

- Stakeholder requirements
- Functional requirements
- Non-functional requirements
- User roles and permissions
- User stories
- Use cases
- Acceptance criteria
- Requirements traceability matrix
- V1.0.0 scope specification

### Implemented requirement areas

- Account registration and authentication
- Authenticated session management
- Repository creation and metadata management
- Public and private repository visibility
- Repository archive and deletion lifecycle
- Managed bare Git repository storage
- Git clone, fetch, and push over Smart HTTP
- Scoped personal access tokens
- Repository branch discovery
- Commit history browsing
- Directory and file browsing
- Text blob viewing

### Current status

**Core requirements implemented; formal specification and traceability required.**

### Exit criteria

- Every V1.0.0 feature has a documented requirement.
- Every requirement has acceptance criteria.
- Security and operational requirements are included.
- Requirements are uniquely identified and traceable.
- Out-of-scope capabilities are explicitly listed.

---

## 5. Phase 3 ÃƒÆ’Ã‚Â¢ÃƒÂ¢Ã¢â‚¬Å¡Ã‚Â¬ÃƒÂ¢Ã¢â€šÂ¬Ã‚Â System Analysis

### Purpose

Analyze the system domain, workflows, data, actors, security boundaries, and operational behavior before documenting the final design.

### Required artifacts

- System context analysis
- Actor analysis
- Domain model
- Repository lifecycle analysis
- Authentication and authorization analysis
- Git Smart HTTP workflow analysis
- Personal access token lifecycle analysis
- Repository browsing analysis
- Data-flow analysis
- Failure and exception analysis
- Security threat analysis
- PlantUML analysis diagrams

### Current status

**Behavior implemented and tested; formal analysis artifacts required.**

### Exit criteria

- Actors and system boundaries are defined.
- Major workflows and alternate flows are documented.
- Domain entities and relationships are analyzed.
- Trust boundaries and security threats are identified.
- Failure states and recovery behavior are documented.
- Analysis results map to requirements.

---

## 6. Phase 4 ÃƒÆ’Ã‚Â¢ÃƒÂ¢Ã¢â‚¬Å¡Ã‚Â¬ÃƒÂ¢Ã¢â€šÂ¬Ã‚Â System Design

### Purpose

Define how the analyzed requirements are realized through architecture, components, APIs, storage, security controls, and user interfaces.

### Required artifacts

- Architecture decisions
- System architecture
- Container architecture
- Component design
- Database design
- REST API design
- Authorization matrix
- Git repository-storage design
- Git process-execution design
- Git Smart HTTP transport design
- Personal access token design
- Repository browser design
- Frontend information architecture
- UI wireframes
- Deployment architecture
- Observability design
- Security design
- PlantUML design diagrams

### Implemented architecture

- Laravel 12 REST API backend
- React and TypeScript frontend
- PostgreSQL application database
- Laravel Sanctum authentication
- Managed bare Git repositories
- Git subprocess execution through validated argument arrays
- Git Smart HTTP clone, fetch, and push transport
- Database-backed asynchronous repository deletion
- Scoped and expiring repository access tokens
- Repository metadata synchronization after successful pushes
- Repository tree, commit, branch, and blob inspection services

### Current status

**Architecture implemented; formal design documentation requires reconstruction and verification.**

### Exit criteria

- Every major requirement maps to a component.
- APIs and database structures are documented.
- Authorization rules are explicit.
- Storage and Git execution boundaries are documented.
- Security controls and deployment topology are defined.
- Design decisions are supported by PlantUML diagrams.

---

## 7. Phase 5 ÃƒÆ’Ã‚Â¢ÃƒÂ¢Ã¢â‚¬Å¡Ã‚Â¬ÃƒÂ¢Ã¢â€šÂ¬Ã‚Â Implementation

### Purpose

Construct the backend, frontend, Git transport, persistence, security, and supporting infrastructure required for V1.0.0.

### Completed implementation areas

- Laravel project foundation
- PostgreSQL integration
- User authentication
- Repository domain and API
- Managed Git repository storage
- Git Smart HTTP transport
- React application foundation
- Frontend authentication flow
- Frontend repository management
- Repository lifecycle controls
- Scoped personal access token management
- Git token permission enforcement
- Default-branch metadata synchronization
- Backend repository browser
- Frontend repository browser

### Current status

**In progress.**

The core vertical product flow is operational. Remaining V1.0.0 scope must be determined through milestone reconciliation and requirements traceability.

### Exit criteria

- Every approved V1.0.0 requirement is implemented.
- No unfinished critical user workflow remains.
- Configuration examples are complete.
- Security-sensitive operations are validated.
- Implementation is traceable to requirements and tests.
- The release candidate can be produced from a clean checkout.

---

## 8. Phase 6 ÃƒÆ’Ã‚Â¢ÃƒÂ¢Ã¢â‚¬Å¡Ã‚Â¬ÃƒÂ¢Ã¢â€šÂ¬Ã‚Â Testing

### Purpose

Verify functional correctness, integration behavior, security controls, Git transport behavior, and frontend quality.

### Existing verification practices

- Laravel unit tests
- Laravel feature tests
- Git storage tests
- Git Smart HTTP integration tests
- Authentication and authorization tests
- Personal access token tests
- Repository browser tests
- Vitest frontend tests
- React Testing Library tests
- ESLint validation
- TypeScript compilation
- Production frontend builds
- Laravel Pint formatting validation
- GitHub Actions continuous integration
- Manual browser verification
- Manual Git clone and push smoke tests

### Current status

**Continuous testing active; formal V1.0.0 test completion pending.**

### Exit criteria

- All automated tests pass.
- Backend and frontend CI checks pass.
- Critical user journeys pass end-to-end verification.
- Authorization and token boundaries are tested.
- Git clone, fetch, and push workflows are verified.
- Security testing produces no unresolved critical findings.
- Release acceptance testing is complete.

---

## 9. Phase 7 ÃƒÆ’Ã‚Â¢ÃƒÂ¢Ã¢â‚¬Å¡Ã‚Â¬ÃƒÂ¢Ã¢â€šÂ¬Ã‚Â Deployment

### Purpose

Prepare, deploy, verify, and document a reproducible GitForge V1.0.0 environment.

### Required artifacts

- Production environment specification
- Deployment architecture
- Environment-variable reference
- Database migration procedure
- Git storage provisioning procedure
- Queue-worker configuration
- Web-server configuration
- TLS and domain configuration
- Backup and restoration procedure
- Deployment checklist
- Rollback procedure
- Release runbook

### Current status

**Pending.**

Local development and continuous integration are operational, but a complete production deployment has not yet been verified.

### Exit criteria

- A production-like environment is deployed.
- HTTPS and production configuration are active.
- Database, queue, and Git storage services are operational.
- Backup and restoration are verified.
- Monitoring and logging are available.
- Rollback instructions are tested.
- The V1.0.0 release is tagged and published.

---

## 10. Phase 8 ÃƒÆ’Ã‚Â¢ÃƒÂ¢Ã¢â‚¬Å¡Ã‚Â¬ÃƒÂ¢Ã¢â€šÂ¬Ã‚Â Maintenance

### Purpose

Operate GitForge after release and manage defects, security updates, performance, and future changes.

### Required artifacts

- Maintenance policy
- Incident-management procedure
- Defect triage process
- Security-update policy
- Dependency-update procedure
- Backup schedule
- Monitoring and alerting procedure
- Release and versioning policy
- V2.0.0 change-request process

### Current status

**Not started.**

Maintenance begins after the V1.0.0 production release.

### Exit criteria

Maintenance does not have a final completion point. It is considered operational when:

- Ownership and support procedures are documented.
- Incidents and defects can be tracked.
- Security and dependency updates are scheduled.
- Backup and recovery checks are recurring.
- Future scope changes follow a controlled process.

---

## 11. Current SDLC Position

| Phase | Status |
|---|---|
| 1. Project Definition | Documentation required |
| 2. Requirements Engineering | Core behavior established; documentation required |
| 3. System Analysis | Implementation evidence exists; documentation required |
| 4. System Design | Architecture implemented; documentation required |
| 5. Implementation | In progress |
| 6. Testing | In progress continuously |
| 7. Deployment | Pending |
| 8. Maintenance | Not started |

---

## 12. Documentation Rule

SDLC phases and milestones serve different purposes:

- SDLC phases organize the complete software-engineering lifecycle.
- Milestones organize measurable project progress.
- Every milestone must define deliverables and exit criteria.
- A milestone may include work from more than one SDLC phase.
- Milestone completion must be supported by verifiable evidence.
- V2.0.0 work must not begin before V1.0.0 satisfies its Definition of Done.

---

## 13. Document Status

**Status:** Draft

**Target release:** V1.0.0

**Next action:** Create `docs/MILESTONES.md` and reconcile completed work

against milestones M0ÃƒÆ’Ã‚Â¢ÃƒÂ¢Ã¢â‚¬Å¡Ã‚Â¬ÃƒÂ¢Ã¢â€šÂ¬Ã…â€œM9.
