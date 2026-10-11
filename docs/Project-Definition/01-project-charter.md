# GitForge V1.0.0 Project Charter

## 1. Document Information

| Field | Value |
|---|---|
| Project | GitForge - Web Git Hosting Platform |
| Target release | V1.0.0 |
| Document | Project Charter |
| SDLC phase | Project Definition |
| Milestone | M0 - Project Definition |
| Project owner | Farhad Valizadegan |
| Status | Approved |
| Last updated | 2026-08-17 |

---

## 2. Project Background

Software teams require controlled storage, collaboration, and access to Git
repositories. Public hosting services provide these capabilities but may not
satisfy requirements for self-hosting, infrastructure ownership, privacy,
customization, or learning how Git hosting systems operate internally.

GitForge is being developed as a self-hosted web Git hosting platform. The
project combines a Laravel backend, a React and TypeScript frontend,
PostgreSQL persistence, managed bare Git repositories, and Git Smart HTTP
transport.

The project is also intended to demonstrate complete software-engineering
practice through a classic eight-phase Software Development Life Cycle.

---

## 3. Project Purpose

The purpose of GitForge V1.0.0 is to provide a secure and deployable
self-hosted platform through which authenticated users can:

- Create and manage Git repositories.
- Control public and private repository visibility.
- Clone, fetch, and push repositories over Git Smart HTTP.
- Generate scoped personal access tokens for Git operations.
- Browse repository branches, commits, directories, and text files.
- Archive or schedule owned repositories for deletion.

---

## 4. Problem Statement

Developers who require control over their source-code infrastructure need a
practical way to host Git repositories without depending entirely on an
external Git hosting provider.

Creating such a platform requires more than storing files. It requires secure
identity management, repository authorization, managed Git storage, safe Git
process execution, HTTP transport compatibility, credential lifecycle
management, repository metadata synchronization, and a usable web interface.

GitForge addresses this problem by delivering the essential capabilities of a
self-hosted Git repository platform within a controlled V1.0.0 scope.

---

## 5. Product Vision

GitForge will be a focused, secure, and understandable self-hosted Git
platform that gives developers ownership of their repositories and hosting
infrastructure.

V1.0.0 will prioritize reliable repository hosting and essential management
workflows rather than attempting to reproduce every feature of large
commercial Git hosting platforms.

---

## 6. Project Objectives

GitForge V1.0.0 has the following objectives:

1. Provide secure user registration, authentication, and session management.
2. Provide repository creation, discovery, updating, archiving, and deletion.
3. Store repositories as managed bare Git repositories.
4. Support authenticated Git clone, fetch, and push operations.
5. Enforce repository ownership, visibility, status, and token permissions.
6. Provide scoped, expiring, and revocable repository access tokens.
7. Synchronize repository metadata after successful Git pushes.
8. Provide a web interface for repository and credential management.
9. Provide secure repository branch, commit, directory, and file browsing.
10. Provide automated backend and frontend verification.
11. Prepare a reproducible production deployment for V1.0.0.
12. Document the project through the complete classic SDLC.

---

## 7. V1.0.0 Scope

### 7.1 In scope

- User registration
- User login and logout
- Authenticated user profile retrieval
- Public and private repositories
- Repository ownership and authorization
- Repository metadata management
- Managed bare repository creation
- Repository archive and unarchive operations
- Asynchronous repository deletion
- Git Smart HTTP discovery
- Git upload-pack and receive-pack operations
- Scoped repository access tokens
- Token expiration and revocation
- Read-only and read-write token abilities
- Default-branch metadata synchronization
- Repository branch browsing
- Repository commit browsing
- Repository directory browsing
- Supported text-file viewing
- React-based management interface
- REST API
- PostgreSQL application persistence
- Automated backend and frontend tests
- Continuous integration
- Production deployment documentation
- V1.0.0 release documentation

### 7.2 Out of scope

The following capabilities are excluded from V1.0.0:

- Organizations and teams
- Repository collaborators
- Fine-grained per-repository member roles
- Pull requests and code review
- Issues and project boards
- Fork networks
- Web-based source-code editing
- Git SSH transport
- Git LFS
- Releases and package registries
- Webhooks
- CI/CD execution for hosted repositories
- Social features
- Billing and subscription management
- Multi-node repository replication
- High-availability deployment
- Mobile applications
- V2.0.0 capabilities

Excluded capabilities may be evaluated only after V1.0.0 satisfies its
Definition of Done.

---

## 8. Major Deliverables

The project will produce:

- Laravel REST API backend
- React and TypeScript frontend
- PostgreSQL database schema
- Managed Git repository storage
- Git Smart HTTP transport
- Personal access token management
- Repository browser
- Automated backend test suite
- Automated frontend test suite
- GitHub Actions workflows
- SDLC documentation
- PlantUML analysis and design diagrams
- Deployment and operations documentation
- V1.0.0 release package

---

## 9. Stakeholders

| Stakeholder | Role | Primary interest |
|---|---|---|
| Farhad Valizadegan | Project owner and developer | Product delivery, engineering quality, portfolio value |
| Repository owner | Authenticated user | Secure repository management and Git access |
| Authorized platform user | Authenticated user | Access to visible repositories |
| Platform administrator | System operator | Deployment, storage, security, and maintenance |
| Portfolio reviewer | Technical evaluator | Architecture, code quality, testing, and documentation |
| Future contributor | Developer | Understandable architecture and maintainable code |

---

## 10. Assumptions

- Git is installed on the hosting environment.
- The hosting environment permits controlled Git subprocess execution.
- PostgreSQL is available for application persistence.
- Managed repository storage is writable by the application runtime.
- HTTPS will protect production HTTP traffic.
- Queue workers will operate in the production environment.
- V1.0.0 is initially intended for a controlled self-hosted deployment.
- Development continues through feature branches and pull requests.
- GitHub Actions remains available for continuous integration.

---

## 11. Constraints

- Development is primarily performed by one developer.
- Time, infrastructure, and financial resources are limited.
- Git subprocesses must never be constructed through unsafe shell strings.
- Repository paths must remain inside the configured managed storage root.
- Private repository access must require valid authorization.
- Plain-text access tokens must never be stored or displayed after creation.
- V1.0.0 scope must remain smaller than commercial Git hosting platforms.
- V2.0.0 development cannot begin before V1.0.0 completion.
- The system must remain understandable as a portfolio engineering project.

---

## 12. Initial Risks

| Risk | Impact | Initial response |
|---|---|---|
| Unsafe Git command execution | Critical | Use validated argument arrays and reject unsafe input |
| Repository path traversal | Critical | Validate paths and enforce the managed storage root |
| Unauthorized private access | Critical | Apply authorization policies and scoped tokens |
| Token disclosure | High | Store only token hashes and display plaintext once |
| Oversized Git or blob requests | High | Enforce configurable request and blob limits |
| Repository storage loss | High | Define backup and restoration procedures |
| Failed asynchronous deletion | Medium | Record recoverable failure state and support retries |
| Scope expansion | High | Enforce the approved V1.0.0 scope |
| Single-developer dependency | Medium | Maintain clear code and engineering documentation |
| Production configuration errors | High | Create deployment checklists and environment references |

The complete risk register will be maintained as a separate M0 artifact.

---

## 13. Success Criteria

GitForge V1.0.0 will be considered successful when:

- All approved V1.0.0 requirements are implemented.
- Required backend, frontend, and Git transport tests pass.
- Public and private authorization rules behave correctly.
- Scoped access tokens correctly control clone and push operations.
- Repository storage remains isolated and manageable.
- Core repository workflows operate through the web interface.
- A production-like deployment is completed successfully.
- Backup, restoration, monitoring, and rollback procedures are documented.
- No unresolved critical security defect remains.
- SDLC and milestone documentation is complete.
- The approved release is tagged as `v1.0.0`.

---

## 14. Development and Governance Approach

The project follows these governance rules:

- Use the classic eight-phase SDLC.
- Track measurable progress through separate milestones.
- Define deliverables and exit criteria for every milestone.
- Develop changes on focused feature or documentation branches.
- Review changes through pull requests.
- Require relevant automated checks before merging.
- Keep `main` in a clean and releasable state.
- Record architectural decisions that materially affect the system.
- Prevent unapproved V2.0.0 scope from entering V1.0.0.

---

## 15. Approval and Exit Criteria

This charter is ready for approval when:

- The project purpose and problem are clear.
- The product vision is accepted.
- V1.0.0 objectives are measurable.
- In-scope and out-of-scope capabilities are explicit.
- Stakeholders, assumptions, constraints, and initial risks are recorded.
- Success criteria are defined.
- The project owner approves the document.

Approval of this charter satisfies only the Project Charter deliverable. M0
remains in progress until every M0 deliverable and exit criterion is complete.

---

## 16. Approval

| Role | Name | Decision | Date |
|---|---|---|---|
| Project owner | Farhad Valizadegan | Approved | 2026-08-17 |
