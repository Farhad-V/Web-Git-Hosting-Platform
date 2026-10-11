# Repository Metadata Testing



| Field | Value |
|---|---|
| Project | GitForge — Web Git Hosting Platform |
| Version | V1.0.0 |
| SDLC phase | 6 — Testing |
| Milestone | M5 — Testing |
| Work package | WP-05 — Repository Metadata Testing |
| Document | Repository Metadata Testing |
| Status | Approved |
| Date | 2026-08-22 |
| Branch | `testing/m5` |



---
## 1. Purpose



This document records the formal execution and results of repository metadata testing for GitForge V1.0.0.



WP-05 verifies repository creation, metadata persistence, repository-name validation, ownership, visibility, metadata retrieval, private-repository concealment, repository updates, and repository-management authentication boundaries.



The work package executes the repository metadata test family defined in:



```text

docs/SDLC/Testing/02-test-case-specification.md

```



The formal repository metadata test identifiers are:



```text

TC-REPO-001

TC-REPO-002

TC-REPO-003

TC-REPO-004

TC-REPO-005

TC-REPO-006

TC-REPO-007

TC-REPO-008

TC-REPO-009

TC-REPO-010

```



---
## 2. Objectives



WP-05 verifies that:



- an authenticated user can create a valid public repository;

- an authenticated user can create a valid private repository;

- an unauthenticated user cannot create repository metadata or repository storage;

- unsafe repository names are rejected;

- repository names are unique within an owner namespace;

- repository names may be reused by different owners;

- repository names are normalized according to the approved implementation;

- public repository metadata can be retrieved;

- repository owners can retrieve their private repositories;

- unauthorized users cannot retrieve private repository metadata;

- repository owners can update supported metadata;

- non-owners cannot update repository metadata;

- repository creation initializes corresponding Git repository storage;

- failed storage initialization does not leave inconsistent repository metadata;

- unsupported deferred metadata is not persisted in V1.0.0;

- repository metadata behavior remains consistent with authentication and authorization boundaries.



---
## 3. Test Basis



Repository metadata testing is based on the approved project artifacts.



Primary requirement basis:



```text

FR-013 – FR-028

FR-061 – FR-066

FR-075 – FR-078

UC-004 – UC-006

```



Primary formal test-case basis:



```text

TC-REPO-001 – TC-REPO-010

```



Primary implementation evidence:



```text

backend/tests/Feature/Api/V1/RepositoryTest.php

```



Supporting implementation areas include:



```text

App\\Models\\Repository

App\\Models\\User

App\\Enums\\RepositoryVisibility

App\\Services\\Git\\RepositoryStorageService

```



Authorization-specific scenarios will receive additional dedicated coverage in WP-06.



Repository-storage internals will receive additional dedicated coverage in WP-07.



---
## 4. Scope



WP-05 covers:



- repository creation;

- public repository creation;

- private repository creation;

- repository ownership;

- repository visibility;

- repository-name normalization;

- repository-name validation;

- owner/name uniqueness;

- repository metadata persistence;

- repository storage initialization during creation;

- rollback when storage initialization fails;

- repository listing for authenticated owners;

- public metadata retrieval;

- owned private metadata retrieval;

- private metadata concealment;

- repository visibility update;

- owner-only repository update;

- unauthenticated repository-management rejection.



---
## 5. Out of Scope



The following areas are tested in later M5 work packages:



- comprehensive repository authorization matrix testing;

- repository-storage service internals;

- Git process execution;

- repository tree and file browser behavior;

- Git Smart HTTP clone/fetch behavior;

- personal access token behavior;

- Git Smart HTTP push behavior;

- complete web UI repository workflows;

- dedicated security testing;

- observability and failure-management testing;

- system-wide end-to-end testing;

- acceptance testing.



Repository metadata observations discovered in those work packages may reference WP-05 results.



---
## 6. Entry Criteria



WP-05 may execute when:



- M4 Implementation is complete;

- WP-01 Testing Plan is approved;

- WP-02 Test Case Specification is approved;

- WP-03 Test Environment Validation is approved;

- WP-04 Authentication Testing is approved;

- Laravel automated-test infrastructure is operational;

- PostgreSQL test persistence is operational;

- Git executable integration required for repository creation is operational;

- temporary repository storage can be created;

- branch `testing/m5` is active.



Current entry assessment:



```text

M4 complete: YES

WP-01 approved: YES

WP-02 approved: YES

WP-03 approved: YES

WP-04 approved: YES

Test environment ready: YES

WP-05 execution: COMPLETE

```



---
## 7. Result States



Each formal test case uses one of the following states.



| State | Meaning |
|---|---|
| PASS | Actual behavior satisfies the expected result |
| FAIL | Actual behavior differs from the expected result |
| BLOCKED | Execution cannot proceed because of an external prerequisite |
| NOT EXECUTED | Test has not yet been executed |



WP-05 cannot be approved while any P1 repository metadata test remains `FAIL`, `BLOCKED`, or `NOT EXECUTED`.



---
## 8. Repository Metadata Test Inventory



| Test ID | Description | Priority | Result |
|---|---|---|---|
| TC-REPO-001 | Create valid public repository | P2 | PASS |
| TC-REPO-002 | Create valid private repository | P2 | PASS |
| TC-REPO-003 | Reject repository creation without authentication | P1 | PASS |
| TC-REPO-004 | Reject invalid repository name | P2 | PASS |
| TC-REPO-005 | Reject duplicate owner/repository name | P2 | PASS |
| TC-REPO-006 | Retrieve public repository metadata | P2 | PASS |
| TC-REPO-007 | Retrieve owned private repository | P1 | PASS |
| TC-REPO-008 | Conceal private repository from unauthorized user | P1 | PASS |
| TC-REPO-009 | Update repository as owner | P2 | PASS |
| TC-REPO-010 | Reject repository update by non-owner | P1 | PASS |



Total formal repository metadata test cases:



```text

10

```



---
## 9. Test Environment



Repository metadata tests execute against the approved WP-03 environment.



Backend:



```text

Laravel Framework 12.66.0

PHP 8.4.1

PostgreSQL 18.6

```



Application database driver:



```text

pgsql

```



Git executable:



```text

git

```



Project branch:



```text

testing/m5

```



Primary test class:



```text

Tests\\Feature\\Api\\V1\\RepositoryTest

```



Primary source file:



```text

backend/tests/Feature/Api/V1/RepositoryTest.php

```



Repository tests isolate physical Git repositories under a temporary storage root created for each test execution context.



---
## 10. Test Data



Repository metadata tests use synthetic users and repositories.



Representative values include:



```text

username: public-owner

repository: public-project

visibility: public

```



and:



```text

repository: git-hosting-platform

visibility: private

```



Additional users and repositories are generated through Laravel factories.



No production repository metadata or production Git repository storage is used.



---
## 11. Repository Storage Isolation



The repository feature suite configures a temporary storage root:



```text

git.storage\_root = temporary test directory

git.repositories\_directory = repositories

git.initial\_branch = main

```



Each test execution therefore operates independently of the application repository-storage root used outside the test suite.



Temporary test storage is removed during test teardown.



This protects:



- local development repositories;

- persistent application repository data;

- unrelated test executions.



---
## 12. Primary Test Command



From the backend directory:



```powershell

php artisan test --filter=RepositoryTest

```



Authoritative fresh execution result:



```text

PASS



Tests: 19 passed

Assertions: 63

Failures: 0

Duration: 4.39s

```



---
## 13. Code-Style Verification



The modified repository feature test file was verified with Laravel Pint.



Command:



```powershell

vendor\\bin\\pint --test tests\\Feature\\Api\\V1\\RepositoryTest.php

```



Result:



```text

PASS

1 file

0 style issues

```



---
## 14. TC-REPO-001 — Create Valid Public Repository



### Priority



```text

P2

```



### Requirement Basis



```text

FR-013 – FR-020

```



### Preconditions



- user is authenticated;

- requested repository name is valid;

- requested owner/name combination does not already exist.



### Action



Create a repository with:



```text

visibility = public

```



### Expected Result



- repository request succeeds;

- metadata record is created;

- authenticated user becomes repository owner;

- repository visibility is public;

- normalized repository name is persisted;

- full repository name identifies owner and repository;

- corresponding bare Git repository storage is initialized.



### Automated Evidence



```text

authenticated user can create public repository

```



The automated test verifies:



- HTTP creation success;

- response metadata;

- owner assignment;

- public visibility;

- database persistence;

- expected repository storage path;

- physical Git repository existence.



### Result



```text

PASS

```



---
## 15. TC-REPO-002 — Create Valid Private Repository



### Priority



```text

P2

```



### Requirement Basis



```text

FR-013 – FR-020

```



### Preconditions



- user is authenticated;

- repository name is valid;

- owner/name combination is available.



### Action



Create repository with:



```text

visibility = private

```



### Expected Result



- repository is created;

- metadata is persisted;

- authenticated user becomes owner;

- visibility is private;

- repository storage is initialized.



### Automated Evidence



```text

authenticated user can create repository

```



The test verifies:



- successful creation;

- normalized repository metadata;

- owner metadata;

- private visibility;

- database persistence;

- expected Git storage path;

- physical repository existence.



### Result



```text

PASS

```



---
## 16. TC-REPO-003 — Reject Repository Creation Without Authentication



### Priority



```text

P1

```



### Requirement Basis



```text

FR-013 – FR-020

AR-010 – AR-012

```



### Preconditions



User is unauthenticated.



### Action



Submit a repository creation request.



### Expected Result



- request is rejected;

- repository metadata is not created;

- Git repository storage is not created;

- no unauthorized side effect remains.



### Automated Evidence



```text

unauthenticated user cannot create repository or storage

```



The dedicated test verifies:



- HTTP unauthorized response;

- zero repository metadata records;

- no repository directories created;

- no repository-storage files created.



Supporting evidence:



```text

unauthenticated user cannot manage repositories

```



### Result



```text

PASS

```



---
## 17. TC-REPO-004 — Reject Invalid Repository Name



### Priority



```text

P2

```



### Requirement Basis



```text

FR-013 – FR-020

FR-075 – FR-078

```



### Preconditions



User is authenticated.



### Action



Submit a repository name violating repository validation rules.



Representative unsafe input:



```text

../project

```



### Expected Result



- request is rejected;

- validation error identifies repository name;

- no repository metadata is created.



### Automated Evidence



```text

repository name rejects unsafe values

```



### Result



```text

PASS

```



---
## 18. TC-REPO-005 — Reject Duplicate Owner/Repository Name



### Priority



```text

P2

```



### Requirement Basis



```text

FR-013 – FR-020

```



### Preconditions



The owner already has a repository named:



```text

project

```



### Action



Attempt creation using the same repository name within the same owner namespace.



### Expected Result



- duplicate creation is rejected;

- existing repository remains unchanged;

- duplicate metadata record is not created.



### Automated Evidence



```text

repository name must be unique within owner namespace

```



Supporting evidence:



```text

different owners can use same repository name

```



The supporting test confirms uniqueness is scoped to owner namespace rather than globally across all users.



### Result



```text

PASS

```



---
## 19. TC-REPO-006 — Retrieve Public Repository Metadata



### Priority



```text

P2

```



### Requirement Basis



```text

FR-021 – FR-028

```



### Preconditions



A public repository exists.



### Action



Request repository metadata.



### Expected Result



Public repository metadata is returned according to the approved API behavior.



### Automated Evidence



Primary:



```text

anonymous visitor can view public repository

```



Supporting:



```text

authenticated user can view public repository

```



The primary test verifies:



- successful HTTP response;

- repository identifier;

- full owner/repository name;

- public visibility.



### Result



```text

PASS

```



---
## 20. TC-REPO-007 — Retrieve Owned Private Repository



### Priority



```text

P1

```



### Requirement Basis



```text

FR-021 – FR-028

```



### Preconditions



- private repository exists;

- requester is repository owner.



### Action



Request private repository metadata.



### Expected Result



Repository metadata is returned to the owner.



### Automated Evidence



```text

owner can view private repository

```



### Result



```text

PASS

```



---
## 21. TC-REPO-008 — Conceal Private Repository from Unauthorized User



### Priority



```text

P1

```



### Requirement Basis



```text

FR-025 – FR-028

FR-061 – FR-066

```



### Preconditions



- private repository exists;

- requester is not repository owner.



### Action



Request private repository metadata.



### Expected Result



- private repository is not disclosed;

- concealment behavior is used;

- unauthorized metadata is not returned.



### Automated Evidence



Primary:



```text

non owner cannot view private repository

```



The response is verified as:



```text

404 Not Found

```



Supporting evidence:



```text

anonymous visitor cannot view private repository

```



The system therefore conceals private repository existence from both authenticated non-owners and anonymous users.



### Result



```text

PASS

```



---
## 22. TC-REPO-009 — Update Repository as Owner



### Priority



```text

P2

```



### Requirement Basis



```text

Repository lifecycle and metadata requirements

```



### Preconditions



- repository exists;

- requester is owner.



### Action



Submit a supported repository metadata change.



Current V1.0.0 supported mutation tested:



```text

visibility: private -> public

```



### Expected Result



- update succeeds;

- metadata is persisted;

- resulting repository state is consistent.



### Automated Evidence



```text

owner can update repository visibility

```



Supporting evidence:



```text

repository update does not allow rename

```



The supporting test confirms repository-name mutation is not implicitly introduced through the visibility-update endpoint.



### Result



```text

PASS

```



---
## 23. TC-REPO-010 — Reject Repository Update by Non-Owner



### Priority



```text

P1

```



### Requirement Basis



```text

FR-061 – FR-066

```



### Preconditions



- repository exists;

- requester is authenticated;

- requester is not owner.



### Action



Attempt to update repository metadata.



### Expected Result



- mutation is rejected;

- repository metadata remains unchanged.



### Automated Evidence



```text

non owner cannot update repository

```



The test verifies:



- forbidden response;

- repository visibility remains private.



### Result



```text

PASS

```



---
## 24. Repository Name Normalization Verification



Repository names are normalized to lowercase according to the implemented V1.0.0 behavior.



Automated evidence:



```text

repository name is normalized to lowercase

```



Representative request:



```text

My.Project\_Name

```



Expected persisted value:



```text

my.project\_name

```



Result:



```text

PASS

```



---
## 25. Repository Namespace Verification



Repository-name uniqueness applies within the owner namespace.



Automated evidence:



```text

repository name must be unique within owner namespace

different owners can use same repository name

```



This verifies both sides of the namespace rule:



- duplicate owner/name is rejected;

- identical repository names for different owners are allowed.



Result:



```text

PASS

```



---
## 26. Repository Metadata Shape Verification



The V1.0.0 repository implementation intentionally does not persist deferred metadata fields that are outside the current approved schema.



Automated evidence:



```text

repository creation does not persist deferred metadata

```



The test confirms the absence of deferred attributes including:



```text

slug

description

status

storage\_uuid

default\_branch

archived\_at

deleted\_at

```



Result:



```text

PASS

```



---
## 27. Repository Creation Atomicity Verification



Repository creation coordinates metadata persistence with Git repository initialization.



If Git repository storage creation fails, repository metadata must not remain persisted in an inconsistent state.



Automated evidence:



```text

failed storage creation rolls back repository metadata

```



The test deliberately configures an invalid Git initial branch and verifies:



- repository creation fails;

- zero repository metadata records remain;

- repository storage directory does not contain a created repository.



Result:



```text

PASS

```



---
## 28. Repository Listing Verification



Authenticated repository listing is restricted to repositories owned by the requesting user.



Automated evidence:



```text

repository index lists only owned repositories

```



The test includes:



- owned private repository;

- owned public repository;

- another user's public repository.



Expected behavior:



- both owned repositories are returned;

- the other user's repository is absent from the owner-management listing.



Result:



```text

PASS

```



---
## 29. Public Repository Read Verification



Public repository metadata is intentionally readable without an authenticated browser session.



Automated evidence:



```text

anonymous visitor can view public repository

authenticated user can view public repository

```



This behavior is distinct from repository-management endpoints, which require authentication.



Result:



```text

PASS

```



---
## 30. Private Repository Concealment Verification



Private repository metadata is concealed from unauthorized requesters.



Automated evidence:



```text

non owner cannot view private repository

anonymous visitor cannot view private repository

```



Observed behavior:



```text

404 Not Found

```



This prevents unauthorized callers from distinguishing an inaccessible private repository through the repository metadata endpoint.



Result:



```text

PASS

```



---
## 31. Repository Update Boundary Verification



The repository-update endpoint supports approved metadata mutations while preserving unsupported immutable metadata.



Automated evidence:



```text

owner can update repository visibility

repository update does not allow rename

non owner cannot update repository

```



Verified behavior:



- owner may change visibility;

- repository name remains unchanged when supplied to the update request;

- non-owner update is rejected.



Result:



```text

PASS

```



---
## 32. Authentication Boundary Verification



Repository-management operations require authenticated user context.



Automated evidence:



```text

unauthenticated user cannot manage repositories

unauthenticated user cannot create repository or storage

```



The suite verifies unauthenticated rejection for:



- repository index;

- repository creation;

- repository update.



Public repository metadata read remains intentionally accessible.



Result:



```text

PASS

```



---
## 33. Repository Storage Initialization Verification



Successful repository creation initializes corresponding repository storage through:



```text

RepositoryStorageService

```



The expected storage path is derived from:



```text

temporary storage root

repositories

<repository-id>.git

```



Direct evidence is provided by:



```text

authenticated user can create repository

authenticated user can create public repository

```



Both tests verify that the storage service reports the resulting repository as existing.



Result:



```text

PASS

```



---
## 34. Expected Automated Test Set



The executed `RepositoryTest` suite contains tests equivalent to:



```text

authenticated user can create repository

authenticated user can create public repository

unauthenticated user cannot create repository or storage

repository creation does not persist deferred metadata

failed storage creation rolls back repository metadata

repository name is normalized to lowercase

repository name rejects unsafe values

repository name must be unique within owner namespace

different owners can use same repository name

repository index lists only owned repositories

anonymous visitor can view public repository

authenticated user can view public repository

owner can view private repository

non owner cannot view private repository

anonymous visitor cannot view private repository

owner can update repository visibility

repository update does not allow rename

non owner cannot update repository

unauthenticated user cannot manage repositories

```



Total:



```text

19 automated tests

```



The exact PHPUnit output generated during WP-05 execution is the authoritative fresh execution evidence.



---
## 35. Defect Handling



If a repository metadata test fails:



1\. preserve the failed test identifier;

2\. preserve relevant automated-test output;

3\. identify expected behavior;

4\. identify actual behavior;

5\. identify affected requirement identifiers;

6\. determine whether metadata, storage, authentication, or authorization is affected;

7\. record the defect;

8\. correct the implementation or test where justified;

9\. rerun the affected test;

10\. rerun relevant regression coverage before approval.



---
## 36. Severity Guidance



| Severity | Repository Metadata Example |
|---|---|
| Critical | Unauthorized repository creation or private repository disclosure |
| High | Non-owner repository mutation, inconsistent storage/metadata state |
| Medium | Valid repository operation fails or metadata is persisted incorrectly |
| Low | Non-security response or validation-detail discrepancy |



Any security-sensitive failure affecting repository ownership or private metadata blocks WP-05 approval.



---
## 37. Security Escalation Rule



The following findings must be escalated to later dedicated authorization or security testing even if first discovered during WP-05:



- private repository disclosure;

- owner identity bypass;

- unauthorized mutation;

- repository path manipulation;

- unsafe repository-name acceptance;

- metadata/storage desynchronization that could expose another repository;

- authentication bypass;

- concealment failure.



No such blocking defect remains from the approved WP-05 execution.



---
## 38. Evidence Requirements



WP-05 approval evidence consists of:



- approved formal test-case definitions;

- primary `RepositoryTest` source;

- fresh Laravel test execution;

- test result count;

- assertion count;

- zero failed tests;

- Pint style verification;

- formal mapping from TC-REPO identifiers to automated evidence;

- repository metadata and storage assertions contained in the automated suite.



---
## 39. Execution Record



### Repository Feature Test Execution



Command:



```powershell

php artisan test --filter=RepositoryTest

```



Result:



```text

PASS



Tests: 19 passed

Assertions: 63

Failures: 0

Duration: 4.39s

```



### Repository Test Style Verification



Command:



```powershell

vendor\\bin\\pint --test tests\\Feature\\Api\\V1\\RepositoryTest.php

```



Result:



```text

PASS

1 file

```



---
## 40. Formal Test Result Summary



| Test ID | Priority | Result |
|---|---|---|
| TC-REPO-001 | P2 | PASS |
| TC-REPO-002 | P2 | PASS |
| TC-REPO-003 | P1 | PASS |
| TC-REPO-004 | P2 | PASS |
| TC-REPO-005 | P2 | PASS |
| TC-REPO-006 | P2 | PASS |
| TC-REPO-007 | P1 | PASS |
| TC-REPO-008 | P1 | PASS |
| TC-REPO-009 | P2 | PASS |
| TC-REPO-010 | P1 | PASS |



Summary:



```text

PASS: 10

FAIL: 0

BLOCKED: 0

NOT EXECUTED: 0

```



Formal execution rate:



```text

10 / 10 = 100%

```



Formal pass rate:



```text

10 / 10 = 100%

```



---
## 41. Supporting Automated Test Result Summary



| Test Suite | Result |
|---|---|
| Repository feature tests | PASS — 19 tests, 63 assertions |
| RepositoryTest Pint verification | PASS — 1 file |



Additional supporting scenarios passed for:



- metadata schema boundaries;

- storage initialization;

- failed-storage rollback;

- repository-name normalization;

- cross-owner namespace behavior;

- owner repository listing;

- anonymous public repository access;

- anonymous private repository concealment;

- immutable repository naming during metadata update;

- unauthenticated repository-management boundaries.



---
## 42. Requirement Coverage



WP-05 provides repository metadata verification for the following requirement families:



```text

FR-013 – FR-020

Repository creation and initial metadata



FR-021 – FR-028

Repository metadata retrieval and visibility



FR-061 – FR-066

Repository ownership and authorization boundaries



FR-075 – FR-078

Repository input-validation behavior



UC-004 – UC-006

Repository lifecycle use cases

```



Detailed authorization behavior will continue in WP-06.



Detailed repository-storage behavior will continue in WP-07.



WP-05 does not claim to replace those later dedicated work packages.



---
## 43. WP-05 Exit Criteria and Approval



WP-05 exit criteria are satisfied when:



- all ten formal `TC-REPO` cases are executed;

- all P1 repository metadata cases pass;

- valid public repository creation is verified;

- valid private repository creation is verified;

- unauthenticated repository creation is rejected without metadata or storage side effects;

- invalid repository names are rejected;

- owner namespace uniqueness is verified;

- public metadata retrieval is verified;

- private metadata retrieval by the owner is verified;

- unauthorized private metadata access is concealed;

- owner repository update is verified;

- non-owner repository update is rejected;

- repository creation/storage consistency is verified;

- repository feature regression tests pass;

- test file style verification passes;

- no blocking defect remains.



Current assessment:



```text

Formal TC-REPO cases executed: 10 / 10

Formal TC-REPO cases passed: 10 / 10

P1 failures: 0

Repository feature tests: 19 passed

Repository assertions: 63 passed

Pint verification: PASS

Blocking defects: 0

WP-05 exit criteria: SATISFIED

Status: APPROVED

```



WP-05 is approved.



Next work package:



```text

WP-06 — Authorization Testing

```



---
## 44. Conclusion



Repository metadata testing for GitForge V1.0.0 completed successfully.



All ten formal repository metadata test cases passed.



Fresh automated execution produced:



```text

19 tests passed

63 assertions

0 failures

```



Repository creation, ownership, visibility, namespace uniqueness, metadata retrieval, private-repository concealment, supported metadata update, authentication boundaries, and repository-storage initialization behaved according to the approved V1.0.0 expectations exercised by WP-05.



Final WP-05 decision:



```text

APPROVED

```



M5 testing may proceed to:



```text

WP-06 — Authorization Testing

```
