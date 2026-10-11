# Testing Plan

| Field | Value |
|---|---|
| Project | GitForge — Web Git Hosting Platform |
| Version | V1.0.0 |
| SDLC phase | 6 — Testing |
| Milestone | M5 — Testing |
| Document | Testing Plan |
| Status | Approved |
| Date | 2026-08-21 |
| Branch | `testing/m5` |

---

## 1. Purpose

This document defines the formal testing plan for GitForge V1.0.0.

The purpose of the Testing phase is to verify that the implemented system satisfies the approved requirements, behaves correctly under expected and exceptional conditions, preserves security and authorization boundaries, and is suitable to proceed to the Deployment phase.

Testing in M5 evaluates the completed implementation produced during M4.

The Testing phase does not introduce new product scope.

Any implementation changes required to correct confirmed defects must remain traceable to the corresponding defect, requirement, design element, and verification result.

---

## 2. Testing Objective

The primary objective of M5 is to establish sufficient evidence that GitForge V1.0.0:

- satisfies approved functional requirements;
- satisfies applicable non-functional requirements;
- implements approved use cases correctly;
- preserves authorization and repository visibility rules;
- protects repository storage boundaries;
- executes Git processes safely;
- supports required Git Smart HTTP read and write operations;
- supports Personal Access Token authentication correctly;
- provides the required web UI behavior;
- handles expected failures correctly;
- provides required observability behavior;
- satisfies defined security controls;
- behaves correctly across integrated frontend, backend, database, filesystem, and Git boundaries;
- contains no unresolved release-blocking defects;
- is ready to proceed to SDLC Phase 7 — Deployment.

---

## 3. Testing Scope

M5 covers verification and validation of the complete GitForge V1.0.0 implementation.

Testing includes:

1. functional testing;
2. API testing;
3. integration testing;
4. system testing;
5. authorization testing;
6. authentication testing;
7. repository lifecycle testing;
8. repository browser testing;
9. Git Smart HTTP read testing;
10. Git Smart HTTP write testing;
11. Personal Access Token testing;
12. repository storage testing;
13. Git process execution testing;
14. frontend testing;
15. database integration testing;
16. security testing;
17. observability testing;
18. failure and exception testing;
19. regression testing;
20. acceptance testing;
21. requirements traceability verification.

---

## 4. Out of Scope

The following activities are outside the scope of M5 unless required to resolve a release-blocking defect:

- new V1.0.0 features;
- functionality assigned to later releases;
- production deployment;
- production infrastructure provisioning;
- long-term maintenance activities;
- large-scale production performance engineering;
- unsupported Git protocols;
- SSH Git transport unless explicitly included in V1.0.0 requirements;
- distributed repository storage;
- high-availability architecture;
- multi-region deployment;
- functionality explicitly deferred during approved project decisions.

---

## 5. Test Basis

Testing must be derived from approved project artifacts.

Primary test-basis sources are:

### 5.1 Project Definition

`docs/Project-Definition/`

Relevant artifacts include:

- project charter;
- problem statement;
- product vision;
- project goals;
- stakeholder definition.

### 5.2 Requirements Engineering

`docs/SDLC/Requirements-Engineering/`

Primary sources include:

- functional requirements;
- non-functional requirements;
- data requirements;
- interface requirements;
- business rules;
- use-case model;
- detailed use-case specifications;
- user stories and acceptance criteria;
- authorization requirements matrix;
- requirements traceability matrix;
- requirements validation;
- M1 milestone review.

### 5.3 System Analysis

`docs/SDLC/System-Analysis/`

Relevant sources include:

- domain analysis;
- logical system decomposition;
- authentication analysis;
- authorization analysis;
- repository lifecycle analysis;
- repository browser analysis;
- Git Smart HTTP read analysis;
- Git Smart HTTP write analysis;
- Personal Access Token analysis;
- repository storage analysis;
- data-flow analysis;
- state analysis;
- trust-boundary analysis;
- failure and exception analysis;
- sequence models;
- activity models;
- analysis traceability;
- M2 milestone review.

### 5.4 System Design

`docs/SDLC/System-Design/`

Relevant sources include:

- architecture decisions;
- container architecture;
- component design;
- database schema design;
- API specification;
- authorization design matrix;
- repository storage design;
- Git process execution design;
- UI information architecture;
- wireframes;
- deployment design;
- observability design;
- security design;
- design traceability;
- M3 milestone review.

### 5.5 Implementation

`docs/SDLC/Implementation/`

Relevant sources include:

- implementation plan;
- design-to-implementation amendments;
- implementation traceability;
- implementation validation;
- M4 milestone review.

---

## 6. System Under Test

The System Under Test is GitForge V1.0.0 as merged into the project `main` branch at completion of M4.

The system consists primarily of:

### Backend

- Laravel 12 application;
- PHP 8.4 runtime;
- REST API;
- authentication services;
- authorization policies;
- Personal Access Token services;
- repository metadata services;
- repository storage services;
- Git process execution services;
- Git Smart HTTP endpoints;
- repository browser services;
- observability services;
- PostgreSQL persistence.

### Frontend

- React application;
- TypeScript;
- React Router;
- TanStack Query;
- Axios;
- React Hook Form;
- Zod validation;
- Tailwind CSS;
- repository management UI;
- repository browser UI;
- authentication UI;
- Personal Access Token UI.

### Infrastructure Boundaries

- PostgreSQL;
- local filesystem repository storage;
- Git executable;
- HTTP interface;
- frontend/backend integration.

---

## 7. Testing Principles

M5 follows these principles:

1. Tests must be traceable to approved requirements or risk areas.
2. Passing implementation-phase tests alone is not sufficient to close M5.
3. Testing must include both positive and negative cases.
4. Authorization boundaries must be tested explicitly.
5. Private repository information must not leak through error behavior.
6. Repository filesystem containment must be tested.
7. Git command execution must be tested against injection and unsafe input.
8. External interfaces must be tested at their actual boundaries where practical.
9. Defects must be reproducible before classification.
10. Corrected defects must be retested.
11. Relevant regression tests must run after defect correction.
12. Release-blocking defects must be zero before M5 closure.
13. Test evidence must be recorded.
14. Deferred observations must not be misclassified as passed requirements.
15. Test results must distinguish product defects from environment limitations.

---

## 8. Test Levels

### 8.1 Unit Testing

Purpose:

Verify isolated application services, domain logic, validation rules, Git command abstractions, repository storage logic, and supporting components.

Primary targets include:

- authentication services;
- PAT services;
- Git command runner;
- repository storage service;
- repository browser service;
- Git Smart HTTP service;
- health and observability services;
- frontend service functions;
- frontend component logic where appropriate.

---

### 8.2 Integration Testing

Purpose:

Verify collaboration between multiple implementation components.

Integration areas include:

- Laravel and PostgreSQL;
- controllers and services;
- models and database persistence;
- authorization policies and controllers;
- repository metadata and filesystem storage;
- Laravel and Git executable;
- Smart HTTP controller and Git services;
- PAT authentication and Git operations;
- frontend and REST API;
- repository browser and Git repository contents.

---

### 8.3 System Testing

Purpose:

Verify GitForge as an integrated system from externally observable interfaces.

System tests must exercise complete flows such as:

- user registration;
- login;
- repository creation;
- repository update;
- public repository access;
- private repository access;
- repository browsing;
- commit browsing;
- PAT creation;
- authenticated Git clone;
- Git fetch;
- Git push;
- unauthorized access attempts;
- invalid Git operations;
- health/readiness behavior.

---

### 8.4 Acceptance Testing

Purpose:

Verify that V1.0.0 satisfies approved user stories, acceptance criteria, use cases, and milestone objectives.

Acceptance testing provides the final behavioral evidence required before M5 closure.

---

## 9. Functional Test Areas

Functional testing must cover at least the following capability groups.

### 9.1 Authentication

Verify:

- user registration;
- valid login;
- invalid login;
- logout;
- authenticated session behavior;
- unauthenticated access restrictions;
- CSRF protection where applicable;
- authentication state consistency between frontend and backend.

---

### 9.2 Repository Metadata

Verify:

- repository creation;
- repository retrieval;
- repository listing;
- repository update;
- repository ownership;
- visibility values;
- validation behavior;
- invalid repository identifiers;
- duplicate repository constraints;
- owner/repository resolution.

---

### 9.3 Authorization

Verify:

- owner privileges;
- authenticated non-owner behavior;
- unauthenticated behavior;
- public repository access;
- private repository concealment;
- unauthorized modification rejection;
- unauthorized Git read rejection;
- unauthorized Git write rejection;
- PAT ability enforcement.

---

### 9.4 Repository Storage

Verify:

- repository storage initialization;
- canonical repository path generation;
- storage-root containment;
- repository existence detection;
- invalid path rejection;
- traversal protection;
- symlink-related protections where supported by the environment;
- safe interaction with bare Git repositories;
- orphan repository handling.

---

### 9.5 Git Process Execution

Verify:

- permitted Git executables;
- permitted subcommands;
- argument handling;
- timeout behavior;
- process exit-code handling;
- stdout handling;
- stderr handling;
- invalid command rejection;
- shell-injection resistance;
- repository path containment.

---

### 9.6 Repository Browser

Verify:

- branch/ref resolution;
- tree listing;
- nested directory browsing;
- file content retrieval;
- commit listing;
- commit detail retrieval;
- invalid ref behavior;
- invalid path behavior;
- object-type validation;
- private repository restrictions;
- public repository browsing.

---

### 9.7 Git Smart HTTP Read

Verify:

- Git `info/refs`;
- `git-upload-pack`;
- public clone;
- authenticated private clone;
- fetch;
- invalid credentials;
- missing credentials;
- insufficient token permissions;
- repository-not-found behavior;
- private repository concealment.

---

### 9.8 Personal Access Tokens

Verify:

- token creation;
- raw token returned only when appropriate;
- token persistence;
- token hashing/security properties;
- token listing;
- token revocation;
- token abilities;
- invalid token rejection;
- revoked token rejection;
- token authentication for Git transport.

---

### 9.9 Git Smart HTTP Write

Verify:

- authenticated push;
- `git-receive-pack`;
- permitted token abilities;
- insufficient permissions;
- invalid credentials;
- private repository access;
- repository ownership restrictions;
- Git failure propagation;
- successful repository update through push.

---

### 9.10 Web User Interface

Verify:

- application routing;
- authentication pages;
- dashboard;
- repository creation interface;
- repository cards/list;
- repository detail page;
- repository browser;
- repository settings;
- PAT management interface;
- loading states;
- empty states;
- error states;
- validation feedback;
- unauthorized-navigation behavior.

---

## 10. API Testing

API testing must validate:

- endpoint availability;
- HTTP methods;
- request validation;
- response status codes;
- response payload structure;
- authorization behavior;
- authentication behavior;
- not-found behavior;
- private resource concealment;
- invalid identifier handling;
- malformed input handling;
- state-changing requests;
- error consistency.

Tests must be aligned with the approved API specification and implementation amendments.

---

## 11. Database Testing

Database-related verification must include:

- migration status;
- required tables;
- required columns;
- indexes;
- unique constraints;
- foreign keys;
- ownership relationships;
- visibility values;
- Personal Access Token persistence;
- expected cascade or restriction behavior;
- data consistency across repository operations.

No pending required migration may remain at M5 closure.

---

## 12. Security Testing

Security testing is mandatory.

At minimum, verify:

### 12.1 Authentication Security

- invalid credential rejection;
- session protection;
- CSRF behavior;
- token protection;
- authentication boundary enforcement.

### 12.2 Authorization Security

- direct-object access attempts;
- owner/non-owner boundaries;
- private repository concealment;
- unauthorized update attempts;
- unauthorized Git read/write attempts.

### 12.3 Injection Protection

- SQL injection attempts;
- command injection attempts;
- Git argument injection attempts;
- malicious path input;
- repository-name manipulation.

### 12.4 Storage Security

- path traversal;
- storage-root escape;
- unsafe repository paths;
- symlink escape where environment support permits testing.

### 12.5 Deployment Security Verification

Verify relevant configuration expectations including:

- debug configuration;
- environment-secret handling;
- production environment examples;
- web-server routing;
- PHP-FPM configuration;
- sensitive-file exposure prevention.

---

## 13. Observability Testing

Verify:

- application health endpoint;
- readiness endpoint;
- database readiness behavior;
- repository storage readiness behavior;
- Git executable readiness behavior;
- request correlation or request identifiers where implemented;
- structured application logging;
- Git execution logging;
- authorization-related observability;
- failure logging without inappropriate secret exposure.

Observability tests must not require logging sensitive credentials or raw access tokens.

---

## 14. Failure and Exception Testing

Testing must include expected failure paths.

Examples include:

- invalid login;
- duplicate user registration;
- invalid repository name;
- missing repository;
- unauthorized repository access;
- inaccessible repository path;
- invalid Git object identifier;
- unsupported Git object;
- Git process failure;
- Git timeout;
- database unavailability where practical;
- repository storage unavailability where practical;
- invalid PAT;
- revoked PAT;
- insufficient PAT ability;
- malformed API requests.

The expected response must be compared against the approved analysis and design behavior.

---

## 15. Regression Testing

Regression testing must occur:

- after defect correction;
- after security-related changes;
- after authentication changes;
- after authorization changes;
- after repository storage changes;
- after Git process execution changes;
- after Smart HTTP changes;
- before final M5 approval.

The final regression run must include the complete automated backend and frontend test suites.

---

## 16. Manual Testing

Manual testing is required where automated tests alone do not provide sufficient evidence.

Manual tests may include:

- browser-based end-to-end flows;
- visual UI verification;
- repository creation through UI;
- repository browsing;
- PAT creation and use;
- real Git clone;
- real Git fetch;
- real Git push;
- invalid credential behavior;
- user-visible error states;
- deployment configuration inspection.

Manual test procedures and results must be recorded.

---

## 17. Automated Testing

Existing automated tests from M4 remain part of the M5 test evidence.

Expected automated verification includes:

### Backend

```text
php artisan test
```

### Backend Code Style

```text
vendor\bin\pint --test
```

### Frontend Type Checking

```text
npx.cmd tsc --noEmit
```

### Frontend Tests

```text
npm.cmd test -- --run
```

### Frontend Linting

```text
npm.cmd run lint
```

### Frontend Production Build

```text
npm.cmd run build
```

Additional test commands may be introduced during M5 when required by the approved test cases.

---

## 18. Test Environment

Primary M5 development/test environment:

| Component | Environment |
|---|---|
| Operating system | Windows development environment |
| Backend framework | Laravel 12 |
| PHP | PHP 8.4 |
| Database | PostgreSQL 18 |
| Frontend | React + TypeScript |
| Build tool | Vite |
| Git | Local Git executable |
| Backend host | `127.0.0.1` |
| Frontend development host | `127.0.0.1` |
| Repository storage | Local filesystem |
| Browser | Modern supported desktop browser |

Environment-specific limitations must be recorded separately from product defects.

---

## 19. Test Data

Test data must:

- use non-production identities;
- avoid real credentials;
- avoid real secrets;
- avoid committing generated PAT secrets;
- be reproducible where practical;
- include public and private repositories;
- include repository owners and non-owners;
- include valid and invalid tokens;
- include valid and invalid repository refs;
- include repository trees, files, and commits;
- support both positive and negative authorization scenarios.

Temporary Git repositories may be created during tests and removed afterward.

---

## 20. Defect Classification

Defects found during M5 are classified by severity.

### Severity 1 — Critical

Examples:

- authentication bypass;
- authorization bypass;
- arbitrary command execution;
- arbitrary filesystem access;
- destructive data corruption;
- private repository disclosure;
- core system unusable.

Release status:

**Blocking**

---

### Severity 2 — High

Examples:

- major required feature does not work;
- Git clone/push failure for valid supported scenarios;
- repository lifecycle failure;
- security control materially ineffective;
- serious data-consistency problem.

Release status:

**Blocking unless formally resolved**

---

### Severity 3 — Medium

Examples:

- partial functional defect;
- recoverable UI defect;
- incorrect non-critical error behavior;
- limited interoperability issue.

Release status:

Evaluated individually.

---

### Severity 4 — Low

Examples:

- minor presentation issue;
- non-blocking usability issue;
- cosmetic problem;
- optimization opportunity.

Release status:

Normally non-blocking.

---

## 21. Defect Lifecycle

Each confirmed defect should follow:

```text
Detected
  |
  v
Reproduced
  |
  v
Classified
  |
  v
Assigned
  |
  v
Corrected
  |
  v
Retested
  |
  v
Regression Tested
  |
  v
Closed
```

A defect must not be marked closed solely because code was changed.

Retest evidence is required.

---

## 22. Test Evidence

M5 evidence may include:

- automated test output;
- command output;
- API responses;
- Git CLI output;
- database inspection results;
- browser verification;
- test-case result tables;
- screenshots where useful;
- defect records;
- regression results;
- acceptance-test results.

Evidence should remain reproducible and traceable.

---

## 23. Requirements Traceability

Every V1.0.0 requirement relevant to implemented scope must ultimately be associated with a testing outcome.

The M5 traceability chain is:

```text
Requirement
    |
    v
Use Case / Acceptance Criterion
    |
    v
Analysis
    |
    v
Design
    |
    v
Implementation
    |
    v
Test Case
    |
    v
Test Result
```

No requirement may be considered verified solely because implementation exists.

---

## 24. M5 Work Packages

M5 is divided into the following work packages.

| Work Package | Name | Purpose |
|---|---|---|
| WP-01 | Testing Plan | Define M5 strategy, scope, levels, criteria, evidence, and workflow |
| WP-02 | Test Case Specification | Define formal test cases derived from approved requirements |
| WP-03 | Test Environment Validation | Verify database, runtime, Git, filesystem, frontend, and backend test environment |
| WP-04 | Authentication Testing | Verify registration, login, logout, sessions, and authentication boundaries |
| WP-05 | Repository Metadata Testing | Verify repository metadata lifecycle and validation |
| WP-06 | Authorization Testing | Verify owner, non-owner, public, private, and unauthenticated boundaries |
| WP-07 | Repository Storage Testing | Verify storage correctness and containment |
| WP-08 | Git Process Execution Testing | Verify controlled and secure Git process behavior |
| WP-09 | Repository Browser Testing | Verify refs, trees, blobs, commits, and browsing behavior |
| WP-10 | Git Smart HTTP Read Testing | Verify clone and fetch flows |
| WP-11 | Personal Access Token Testing | Verify PAT lifecycle, abilities, and authentication |
| WP-12 | Git Smart HTTP Write Testing | Verify push flows and write authorization |
| WP-13 | Web UI Testing | Verify required frontend behavior and user flows |
| WP-14 | Security Testing | Verify security controls and abuse cases |
| WP-15 | Observability and Failure Testing | Verify health, readiness, logging, failures, and exception behavior |
| WP-16 | System and End-to-End Testing | Verify complete integrated user and Git workflows |
| WP-17 | Regression Testing | Execute final complete automated and targeted regression suites |
| WP-18 | Acceptance Testing | Validate V1.0.0 against approved acceptance criteria |
| WP-19 | Test Traceability | Map requirements to formal test cases and results |
| WP-20 | Testing Validation | Validate completeness and quality of M5 evidence |
| WP-21 | M5 Milestone Review | Determine formal Testing milestone completion |

---

## 25. Work-Package Execution Order

The planned sequence is:

```text
WP-01 Testing Plan
        |
        v
WP-02 Test Case Specification
        |
        v
WP-03 Test Environment Validation
        |
        +-------------------------------+
        |                               |
        v                               v
WP-04 Authentication              WP-05 Repository Metadata
        |                               |
        +---------------+---------------+
                        |
                        v
                 WP-06 Authorization
                        |
                        v
                 WP-07 Repository Storage
                        |
                        v
              WP-08 Git Process Execution
                        |
                        v
              WP-09 Repository Browser
                        |
                        v
            WP-10 Git Smart HTTP Read
                        |
                        v
               WP-11 PAT Testing
                        |
                        v
            WP-12 Git Smart HTTP Write
                        |
                        v
                 WP-13 Web UI
                        |
                        v
                WP-14 Security
                        |
                        v
         WP-15 Observability / Failure
                        |
                        v
          WP-16 System / End-to-End
                        |
                        v
               WP-17 Regression
                        |
                        v
               WP-18 Acceptance
                        |
                        v
              WP-19 Traceability
                        |
                        v
               WP-20 Validation
                        |
                        v
          WP-21 M5 Milestone Review
```

Some test execution may overlap where independence permits, but closure dependencies must remain satisfied.

---

## 26. Entry Criteria

M5 may begin only when:

- M4 implementation is complete;
- M4 milestone review is approved;
- M4 branch has passed required CI;
- M4 has been merged into `main`;
- the testing branch has been created from the completed M4 state;
- required implementation artifacts are available;
- required test environment dependencies are available.

Current entry status:

| Entry Criterion | Status |
|---|---|
| M4 complete | PASS |
| M4 review approved | PASS |
| M4 CI passed | PASS |
| M4 merged to `main` | PASS |
| Testing branch created | PASS |
| Implementation artifacts available | PASS |
| Test environment available | TO BE VERIFIED IN WP-03 |

M5 entry is therefore permitted.

---

## 27. Suspension Criteria

Testing may be suspended for a work package when:

- required test infrastructure is unavailable;
- a critical defect prevents meaningful further execution;
- test data cannot be established safely;
- implementation state becomes unstable;
- prerequisite testing has not completed;
- results cannot be trusted due to environment failure.

Suspension must be recorded together with the reason.

---

## 28. Resumption Criteria

Testing may resume when:

- the blocking environment issue is corrected;
- the blocking defect has been addressed sufficiently to continue;
- prerequisite testing is complete;
- required test data is available;
- test integrity can be restored.

---

## 29. Pass Criteria

A test case passes when:

- all preconditions are satisfied;
- the defined procedure is executed;
- actual behavior matches expected behavior;
- no unintended security or data effect occurs;
- required evidence is captured.

---

## 30. Fail Criteria

A test case fails when:

- expected behavior is not achieved;
- unauthorized behavior succeeds;
- required behavior is missing;
- system state becomes inconsistent;
- data integrity is violated;
- required security controls fail;
- unexpected errors invalidate the result.

---

## 31. Blocked Test Criteria

A test is blocked when it cannot be executed because of an external prerequisite or environment limitation that prevents a valid result.

Blocked tests must not be counted as passed.

---

## 32. M5 Exit Criteria

M5 is complete only when all of the following are satisfied:

1. the Testing Plan is approved;
2. formal test cases exist for required V1.0.0 capabilities;
3. the test environment is validated;
4. authentication testing passes;
5. repository metadata testing passes;
6. authorization testing passes;
7. repository storage testing passes;
8. Git process execution testing passes;
9. repository browser testing passes;
10. Git Smart HTTP read testing passes;
11. Personal Access Token testing passes;
12. Git Smart HTTP write testing passes;
13. required web UI testing passes;
14. security testing passes;
15. observability and failure testing passes;
16. system/end-to-end testing passes;
17. final regression testing passes;
18. acceptance testing passes;
19. requirements-to-test traceability is complete;
20. all critical defects are closed;
21. all high-severity release-blocking defects are closed;
22. blocking defect count is zero;
23. unresolved medium/low observations are documented and dispositioned;
24. testing validation passes;
25. M5 milestone review is approved.

---

## 33. Testing Completion Definition

Testing is considered complete when:

- required V1.0.0 behavior has been verified;
- required security boundaries have been verified;
- required Git transport behavior has been verified;
- required frontend behavior has been verified;
- required integrations have been verified;
- regression testing is green;
- acceptance criteria are satisfied;
- traceability is complete;
- no release-blocking defect remains;
- M5 review approves progression to Deployment.

---

## 34. Testing Deliverables

Expected M5 documentation deliverables are:

```text
docs/SDLC/Testing/
├── 01-testing-plan.md
├── 02-test-case-specification.md
├── 03-test-environment-validation.md
├── 04-authentication-testing.md
├── 05-repository-metadata-testing.md
├── 06-authorization-testing.md
├── 07-repository-storage-testing.md
├── 08-git-process-execution-testing.md
├── 09-repository-browser-testing.md
├── 10-git-smart-http-read-testing.md
├── 11-personal-access-token-testing.md
├── 12-git-smart-http-write-testing.md
├── 13-web-ui-testing.md
├── 14-security-testing.md
├── 15-observability-and-failure-testing.md
├── 16-system-and-end-to-end-testing.md
├── 17-regression-testing.md
├── 18-acceptance-testing.md
├── 19-test-traceability.md
├── 20-testing-validation.md
└── 21-m5-milestone-review.md
```

Additional evidence files may be introduced when justified.

---

## 35. Change Control During Testing

M5 may reveal implementation defects requiring source-code changes.

Such changes must:

1. be tied to a documented test failure or defect;
2. remain within approved V1.0.0 scope;
3. preserve requirements and design intent unless a formal amendment is required;
4. include appropriate automated or manual verification;
5. be retested;
6. receive regression testing;
7. be committed with a clear defect-related commit message.

Testing must not become an uncontrolled second implementation phase.

---

## 36. Branch Strategy

Primary M5 branch:

```text
testing/m5
```

Testing documentation and defect corrections required for M5 may be developed on this branch.

If substantial defect corrections require isolation, focused branches may be introduced when justified.

Final M5 integration into `main` occurs only after:

- M5 validation passes;
- M5 milestone review is approved;
- required CI is green;
- the final M5 pull request is reviewed and mergeable.

---

## 37. CI Role During M5

Continuous Integration provides important regression evidence but does not replace M5 testing.

CI should verify at minimum:

- backend automated tests;
- frontend automated tests;
- code quality checks;
- build integrity.

M5 additionally provides:

- formal requirements-based testing;
- cross-component verification;
- security testing;
- end-to-end testing;
- acceptance testing;
- documented evidence;
- milestone-level validation.

---

## 38. Risk-Based Testing Priorities

Highest testing priority is assigned to:

### Priority 1

- authentication;
- authorization;
- private repository protection;
- repository storage containment;
- Git process injection protection;
- PAT authentication;
- Git push authorization;
- command execution boundaries.

### Priority 2

- repository creation and metadata;
- repository browser;
- clone/fetch behavior;
- Git push behavior;
- API validation;
- database integrity.

### Priority 3

- frontend usability;
- error presentation;
- observability;
- deployment configuration verification.

Priority does not remove any required test from scope.

---

## 39. Test Reporting

Each testing work package should record:

- purpose;
- test basis;
- environment;
- prerequisites;
- test cases executed;
- expected results;
- actual results;
- pass/fail/blocked status;
- defects discovered;
- corrections performed;
- retest result;
- regression impact;
- remaining observations;
- work-package conclusion.

---

## 40. Testing Metrics

M5 should track at least:

- total planned test cases;
- total executed;
- passed;
- failed;
- blocked;
- not executed;
- defects by severity;
- defects corrected;
- defects retested;
- open blocking defects;
- requirements covered;
- acceptance criteria covered.

Final milestone review must not rely only on a single pass percentage.

Critical requirement and security coverage must be complete.

---

## 41. Test Result States

Allowed formal test-result states:

```text
NOT EXECUTED
PASS
FAIL
BLOCKED
```

Defect states are tracked separately.

---

## 42. Requirement Verification States

Requirement testing status may be represented as:

```text
NOT TESTED
PARTIALLY VERIFIED
VERIFIED
FAILED
BLOCKED
```

A requirement becomes `VERIFIED` only when all required associated test evidence passes.

---

## 43. Testing Quality Gates

The following quality gates apply.

### Gate 1 — Test Definition

Pass when:

- test cases are derived from approved sources;
- test expectations are explicit;
- traceability exists.

### Gate 2 — Component and Integration Verification

Pass when:

- core functional areas pass;
- integration boundaries pass;
- blocking defects are addressed.

### Gate 3 — Security Verification

Pass when:

- required security controls pass;
- no critical security defect remains;
- no unauthorized repository access path remains.

### Gate 4 — System Verification

Pass when:

- complete workflows succeed;
- Git operations succeed;
- UI/API integration succeeds.

### Gate 5 — Regression Verification

Pass when:

- complete automated suites are green;
- corrected defects remain corrected;
- no regression blocker exists.

### Gate 6 — Acceptance Verification

Pass when:

- approved acceptance criteria are satisfied;
- requirements traceability is complete.

### Gate 7 — Milestone Closure

Pass when:

- testing validation passes;
- M5 review is approved;
- blocking defects equal zero.

---

## 44. Blocking Conditions

M5 cannot close while any of the following remains unresolved:

- critical defect;
- high-severity release-blocking defect;
- authentication bypass;
- authorization bypass;
- private repository disclosure;
- repository root escape;
- unsafe Git command execution;
- inability to clone/fetch required repositories;
- inability to push in required supported scenarios;
- required feature not testable;
- required acceptance criterion failed;
- incomplete required traceability;
- unreliable final regression suite.

---

## 45. Non-Blocking Observations

A non-blocking observation may remain when:

- it does not violate an approved requirement;
- it does not introduce unacceptable security risk;
- it does not prevent required V1.0.0 usage;
- it does not invalidate acceptance criteria;
- it is documented;
- its disposition is explicit.

Examples may include:

- performance optimization opportunities;
- UI refinement opportunities;
- non-critical developer-experience improvements;
- environment-specific test limitations.

---

## 46. M5 Completion Authority

The M5 Milestone Review determines whether Testing is formally complete.

Possible decisions are:

```text
PASS
PASS WITH NON-BLOCKING OBSERVATIONS
FAIL
```

Only `PASS` or an explicitly justified `PASS WITH NON-BLOCKING OBSERVATIONS` may authorize progression to Deployment.

---

## 47. Transition to Deployment

After M5 is complete and formally merged into `main`, the project may begin:

```text
SDLC Phase 7 — Deployment
```

Testing artifacts become the formal verification basis for deployment readiness.

Deployment must not be used to compensate for unresolved M5 release-blocking defects.

---

## 48. Current M5 Status

```text
M5 — Testing
Status: IN PROGRESS

Completed:
- M4 implementation completed
- M4 approved
- M4 merged into main
- testing/m5 branch created
- testing documentation directory created
- WP-01 Testing Plan approved

Next:
- begin WP-02 Test Case Specification
```

---

## 49. WP-01 Exit Condition

WP-01 — Testing Plan is complete when:

- testing scope is defined;
- test levels are defined;
- testing areas are defined;
- test basis is identified;
- test environment expectations are defined;
- defect classification is defined;
- evidence requirements are defined;
- test-result states are defined;
- M5 work packages are defined;
- M5 entry criteria are satisfied;
- M5 exit criteria are defined;
- quality gates are defined;
- transition rules to Deployment are defined;
- this document has been reviewed and approved.

Current WP-01 status:

```text
APPROVED
```