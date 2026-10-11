# Test Environment Validation

| Field | Value |
|---|---|
| Project | GitForge — Web Git Hosting Platform |
| Version | V1.0.0 |
| SDLC phase | 6 — Testing |
| Milestone | M5 — Testing |
| Work package | WP-03 — Test Environment Validation |
| Document | Test Environment Validation |
| Status | Approved |
| Date | 2026-08-21 |
| Branch | `testing/m5` |

---

## 1. Purpose

This document validates that the GitForge V1.0.0 test environment is correctly configured, operational, reproducible, and suitable for executing the formal M5 test cases.

WP-03 establishes confidence that test results produced during the remaining M5 work packages are attributable to the system under test rather than to an incorrectly configured development or test environment.

The validation covers:

- application runtime prerequisites;
- backend runtime;
- frontend runtime;
- PostgreSQL connectivity;
- database schema state;
- backend dependency availability;
- frontend dependency availability;
- automated test infrastructure;
- static-analysis and quality tooling;
- HTTP liveness and readiness;
- filesystem prerequisites;
- Git executable availability;
- repository-storage prerequisites;
- environment isolation;
- configuration safety;
- test evidence readiness.

---

## 2. Objectives

The objectives of WP-03 are to verify that:

- the expected source branch is active;
- the working tree is controlled before test execution;
- required software versions are available;
- PHP and required PHP extensions are available;
- Composer dependencies are installed;
- Laravel can bootstrap successfully;
- PostgreSQL is available;
- the application database is reachable;
- all expected database migrations are applied;
- Node.js and npm are available;
- frontend dependencies are installed;
- TypeScript validation can execute;
- frontend linting can execute;
- frontend unit tests can execute;
- frontend production build can execute;
- backend automated tests can execute;
- Laravel Pint can execute;
- Git is installed and callable by the application environment;
- application storage directories exist and are writable;
- repository-storage configuration is usable;
- liveness and readiness endpoints can respond correctly;
- frontend runtime can be started;
- backend runtime can be started;
- the environment is ready for formal M5 functional and non-functional testing.

---

## 3. Validation Scope

WP-03 validates the local Windows-based GitForge V1.0.0 testing environment used for M5.

The environment includes:

### Backend

- Laravel 12;
- PHP 8.4;
- Composer dependencies;
- PostgreSQL;
- Laravel automated tests;
- Laravel Pint;
- application configuration;
- repository-storage integration;
- Git process execution.

### Frontend

- React;
- TypeScript;
- Vite;
- npm dependencies;
- Vitest;
- Testing Library;
- ESLint;
- TypeScript compiler;
- production build pipeline.

### Runtime Integration

- Laravel backend runtime;
- React/Vite frontend runtime;
- frontend-to-backend API communication;
- database connectivity;
- liveness endpoint;
- readiness endpoint.

---

## 4. Out of Scope

WP-03 does not perform the complete functional verification of GitForge features.

The following are handled by later M5 work packages:

- detailed authentication behavior;
- repository metadata behavior;
- authorization-rule validation;
- repository-storage functional behavior;
- detailed Git process-execution behavior;
- repository-browser behavior;
- Git Smart HTTP clone and fetch scenarios;
- personal access token behavior;
- Git Smart HTTP push scenarios;
- complete web UI behavior;
- dedicated security testing;
- observability and failure testing;
- complete end-to-end workflows;
- regression testing;
- acceptance testing.

WP-03 validates only that the environment required to perform those tests is ready.

---

## 5. Test Environment

The M5 test environment is the local GitForge development and validation environment.

Expected project root:

```text
C:\xampp\htdocs\projects\web\Web-Git-Hosting-Platform
```

Expected Git branch:

```text
testing/m5
```

Expected backend runtime:

```text
Laravel 12.66.0
PHP 8.4.1
```

Expected database platform:

```text
PostgreSQL 18.6
```

Expected database endpoint:

```text
127.0.0.1:5432
```

Expected application database:

```text
git_hosting_platform
```

Expected application database role:

```text
git_hosting_app
```

Expected frontend development endpoint:

```text
http://127.0.0.1:5173
```

Expected backend development endpoint:

```text
http://127.0.0.1:8000
```

Expected backend liveness endpoint:

```text
http://127.0.0.1:8000/up
```

Expected readiness endpoint:

```text
http://127.0.0.1:8000/api/v1/health/readiness
```

---

## 6. Validation Result States

Each validation check uses one of the following states.

| State | Meaning |
|---|---|
| PASS | Validation criterion was satisfied |
| FAIL | Validation criterion was not satisfied |
| BLOCKED | Validation could not be executed because of an external dependency or environment limitation |
| NOT EXECUTED | Validation has not yet been executed |

A WP-03 approval requires all blocking environment checks to reach `PASS`.

A documented non-blocking environment limitation may remain if:

- it does not invalidate the planned M5 test execution;
- its scope is explicitly documented;
- an alternative verification approach exists where necessary;
- it is not a release-blocking defect.

---

## 7. Validation Priorities

Environment checks are classified as:

| Priority | Meaning |
|---|---|
| P0 | Mandatory prerequisite for M5 execution |
| P1 | Important validation required before relevant test families execute |
| P2 | Supporting validation or diagnostic check |

Any failed P0 validation blocks WP-03 approval.

---

## 8. Environment Validation Inventory

The following validation cases constitute WP-03.

| ID | Validation area | Priority | Initial status |
|---|---|---|---|
| ENV-001 | Project directory | P0 | PASS |
| ENV-002 | Git branch | P0 | PASS |
| ENV-003 | Git working-tree state | P1 | PASS |
| ENV-004 | Git executable | P0 | PASS |
| ENV-005 | PHP runtime | P0 | PASS |
| ENV-006 | Required PHP extensions | P0 | PASS |
| ENV-007 | Composer availability | P0 | PASS |
| ENV-008 | Laravel bootstrap | P0 | PASS |
| ENV-009 | Laravel framework version | P1 | PASS |
| ENV-010 | Backend dependencies | P0 | PASS |
| ENV-011 | PostgreSQL client | P0 | PASS |
| ENV-012 | Database connectivity | P0 | PASS |
| ENV-013 | Database migration state | P0 | PASS |
| ENV-014 | Application database configuration | P0 | PASS |
| ENV-015 | Node.js runtime | P0 | PASS |
| ENV-016 | npm availability | P0 | PASS |
| ENV-017 | Frontend dependencies | P0 | PASS |
| ENV-018 | TypeScript validation | P1 | PASS |
| ENV-019 | Frontend lint validation | P1 | PASS |
| ENV-020 | Frontend automated-test infrastructure | P0 | PASS |
| ENV-021 | Frontend production build | P1 | PASS |
| ENV-022 | Backend automated-test infrastructure | P0 | PASS |
| ENV-023 | Laravel Pint | P1 | PASS |
| ENV-024 | Laravel storage writability | P0 | PASS |
| ENV-025 | Repository-storage prerequisite | P0 | PASS |
| ENV-026 | Backend runtime | P0 | PASS |
| ENV-027 | Liveness endpoint | P0 | PASS |
| ENV-028 | Readiness endpoint | P0 | PASS |
| ENV-029 | Frontend runtime | P0 | PASS |
| ENV-030 | Frontend/backend integration reachability | P0 | PASS |
| ENV-031 | Configuration safety | P0 | PASS |
| ENV-032 | Test evidence readiness | P1 | PASS |

Total validation checks:

```text
32
```

---

## 9. ENV-001 — Project Directory

### Objective

Verify that test execution is being performed from the expected GitForge project repository.

### Command

```powershell
Get-Location
```

### Expected Result

The current path is:

```text
C:\xampp\htdocs\projects\web\Web-Git-Hosting-Platform
```

### Result

```text
PASS
```

---

## 10. ENV-002 — Git Branch

### Objective

Verify that M5 validation is executed on the correct branch.

### Command

```powershell
git branch --show-current
```

### Expected Result

```text
testing/m5
```

### Result

```text
PASS
```

---

## 11. ENV-003 — Git Working-Tree State

### Objective

Identify any intentional or uncommitted changes before formal environment validation.

### Command

```powershell
git status --short
```

### Expected Result

Only changes intentionally associated with WP-03 may be present.

Unexpected modifications must be investigated before formal test execution.

### Result

```text
PASS
```

---

## 12. ENV-004 — Git Executable

### Objective

Verify that Git is installed and callable from the test environment.

### Command

```powershell
git --version
```

### Expected Result

A valid Git version is returned and the process exits successfully.

### Result

```text
PASS
```

---

## 13. ENV-005 — PHP Runtime

### Objective

Verify that the PHP runtime required by the backend is available.

### Command

```powershell
php --version
```

### Expected Result

PHP 8.4.x is available.

Expected environment version:

```text
PHP 8.4.1
```

### Result

```text
PASS
```

---

## 14. ENV-006 — Required PHP Extensions

### Objective

Verify that PHP extensions required by the Laravel/PostgreSQL environment are enabled.

### Command

```powershell
php -m
```

### Required Extensions

At minimum:

```text
PDO
pdo_pgsql
pgsql
openssl
mbstring
tokenizer
xml
ctype
json
fileinfo
```

Extensions implicitly built into the PHP runtime may not appear identically across PHP distributions.

### Pass Criterion

The runtime provides the functionality required by Laravel, PostgreSQL connectivity, cryptographic operations, string processing, and application execution.

### Result

```text
PASS
```

---

## 15. ENV-007 — Composer Availability

### Objective

Verify that Composer is available.

### Command

```powershell
composer --version
```

### Expected Result

Composer returns a valid version and exits successfully.

### Result

```text
PASS
```

---

## 16. ENV-008 — Laravel Bootstrap

### Objective

Verify that the Laravel application can bootstrap without a fatal configuration or dependency error.

### Command

```powershell
php artisan about
```

### Expected Result

Laravel successfully boots and displays application information.

### Result

```text
PASS
```

---

## 17. ENV-009 — Laravel Framework Version

### Objective

Verify the backend framework version.

### Command

```powershell
php artisan --version
```

### Expected Result

```text
Laravel Framework 12.66.0
```

A later compatible patch version is acceptable only if the version change is intentional and documented.

### Result

```text
PASS
```

---

## 18. ENV-010 — Backend Dependencies

### Objective

Verify that Composer dependencies are installed and internally consistent.

### Command

```powershell
composer validate
```

### Additional Check

```powershell
composer check-platform-reqs
```

### Expected Result

Required PHP platform dependencies are satisfied.

A non-blocking Composer metadata warning may be documented separately if it does not affect application execution or dependency integrity.

### Result

```text
PASS
```

---

## 19. ENV-011 — PostgreSQL Client

### Objective

Verify that the PostgreSQL command-line client is available.

### Command

```powershell
& "C:\Program Files\PostgreSQL\18\bin\psql.exe" --version
```

### Expected Result

PostgreSQL client version 18.x is returned.

Expected environment version:

```text
psql (PostgreSQL) 18.6
```

### Result

```text
PASS
```

---

## 20. ENV-012 — Database Connectivity

### Objective

Verify that Laravel can establish a connection to the configured PostgreSQL database.

### Command

```powershell
php artisan db:show
```

### Expected Result

Laravel successfully connects to PostgreSQL and reports database information.

The expected database is:

```text
git_hosting_platform
```

### Result

```text
PASS
```

---

## 21. ENV-013 — Database Migration State

### Objective

Verify that the database schema is synchronized with the application migrations.

### Command

```powershell
php artisan migrate:status
```

### Expected Result

Every migration required by GitForge V1.0.0 reports:

```text
Ran
```

No required migration may remain pending before formal application testing.

### Result

```text
PASS
```

---

## 22. ENV-014 — Application Database Configuration

### Objective

Verify that Laravel resolves the expected database driver and connection without exposing sensitive credentials.

### Command

```powershell
php artisan tinker --execute="dump(['connection' => config('database.default'), 'host' => config('database.connections.pgsql.host'), 'port' => config('database.connections.pgsql.port'), 'database' => config('database.connections.pgsql.database'), 'username' => config('database.connections.pgsql.username')]);"
```

### Expected Result

The active connection resolves to PostgreSQL.

Expected non-secret parameters include:

```text
DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=git_hosting_platform
DB_USERNAME=git_hosting_app
```

### Security Rule

Database passwords or other secrets must not be copied into this document or committed to Git.

### Result

```text
PASS
```

---

## 23. ENV-015 — Node.js Runtime

### Objective

Verify that Node.js is installed and callable.

### Command

```powershell
node --version
```

### Expected Result

A supported Node.js version is returned successfully.

### Result

```text
PASS
```

---

## 24. ENV-016 — npm Availability

### Objective

Verify that npm is installed and callable.

### Command

```powershell
npm.cmd --version
```

### Expected Result

A valid npm version is returned.

### Result

```text
PASS
```

---

## 25. ENV-017 — Frontend Dependencies

### Objective

Verify that the frontend dependency tree is installed and resolvable.

### Command

```powershell
npm.cmd ls --depth=0
```

### Expected Result

The frontend's required top-level dependencies resolve successfully.

Unexpected missing or invalid packages must be corrected before frontend test execution.

### Result

```text
PASS
```

---

## 26. ENV-018 — TypeScript Validation

### Objective

Verify that the TypeScript compiler can analyze the frontend project successfully.

### Command

```powershell
npx.cmd tsc --noEmit
```

### Expected Result

The command exits successfully with no TypeScript errors.

### Result

```text
PASS
```

---

## 27. ENV-019 — Frontend Lint Validation

### Objective

Verify that the frontend linting infrastructure is operational and the current source satisfies configured lint rules.

### Command

```powershell
npm.cmd run lint
```

### Expected Result

The lint command completes successfully without blocking errors.

### Result

```text
PASS
```

---

## 28. ENV-020 — Frontend Automated-Test Infrastructure

### Objective

Verify that the frontend unit/component test infrastructure is operational.

### Command

```powershell
npm.cmd test -- --run
```

### Alternative

If the project's configured npm test script already runs Vitest non-interactively:

```powershell
npm.cmd test
```

### Expected Result

Vitest executes successfully.

All currently maintained frontend automated tests pass.

### Result

```text
PASS
```

---

## 29. ENV-021 — Frontend Production Build

### Objective

Verify that the frontend can produce a production build.

### Command

```powershell
npm.cmd run build
```

### Expected Result

Vite completes the production build successfully.

A bundle-size advisory warning is non-blocking unless it represents a defined requirement violation or causes an actual runtime failure.

### Result

```text
PASS
```

---

## 30. ENV-022 — Backend Automated-Test Infrastructure

### Objective

Verify that Laravel's automated-test infrastructure is operational before the specialized M5 functional test work packages begin.

### Command

```powershell
php artisan test
```

### Expected Result

The Laravel automated test suite executes successfully.

Existing passing tests must remain passing.

Any environment-specific skipped test must be explicitly identified and evaluated.

### Result

```text
PASS
```

---

## 31. ENV-023 — Laravel Pint

### Objective

Verify that the backend source satisfies the configured PHP formatting rules.

### Command

```powershell
vendor\bin\pint --test
```

### Expected Result

Pint reports no formatting violations.

### Result

```text
PASS
```

---

## 32. ENV-024 — Laravel Storage Writability

### Objective

Verify that Laravel runtime storage directories exist and are writable.

### Required Paths

```text
storage
storage\app
storage\framework
storage\framework\cache
storage\framework\sessions
storage\framework\views
storage\logs
bootstrap\cache
```

### Command

```powershell
$paths = @(
    'storage',
    'storage\app',
    'storage\framework',
    'storage\framework\cache',
    'storage\framework\sessions',
    'storage\framework\views',
    'storage\logs',
    'bootstrap\cache'
)

$paths | ForEach-Object {
    [PSCustomObject]@{
        Path   = $_
        Exists = Test-Path $_
    }
}
```

### Expected Result

All required runtime paths exist.

Laravel runtime execution must be capable of writing required cache, session, view, and log data.

### Result

```text
PASS
```

---

## 33. ENV-025 — Repository-Storage Prerequisite

### Objective

Verify that the repository-storage environment required by GitForge is configured and reachable by the application.

### Validation Requirements

The configured repository root must:

- resolve to the intended filesystem location;
- exist or be creatable by the application;
- be writable by the GitForge process;
- not unintentionally point to application source directories;
- support creation of Git bare repositories;
- support repository lookup;
- provide sufficient isolation for formal test fixtures.

### Security Requirements

The repository root must not:

- expose unrelated system directories;
- permit arbitrary path traversal outside the configured root;
- require privileged operating-system access for ordinary GitForge operations.

### Evidence

The exact storage path may be recorded if it is non-sensitive.

Secrets, credentials, or unrelated personal filesystem information must not be committed.

### Result

```text
PASS
```

---

## 34. ENV-026 — Backend Runtime

### Objective

Verify that the Laravel HTTP runtime can start successfully.

### Command

Run in a dedicated PowerShell terminal:

```powershell
php artisan serve --host=127.0.0.1 --port=8000
```

### Expected Result

Laravel starts successfully and listens on:

```text
http://127.0.0.1:8000
```

### Result

```text
PASS
```

---

## 35. ENV-027 — Liveness Endpoint

### Objective

Verify that the backend liveness endpoint responds successfully.

### Precondition

ENV-026 backend runtime is active.

### Command

```powershell
Invoke-WebRequest http://127.0.0.1:8000/up -UseBasicParsing
```

### Expected Result

HTTP status:

```text
200
```

The endpoint confirms that the Laravel application process is alive and capable of serving HTTP requests.

### Result

```text
PASS
```

---

## 36. ENV-028 — Readiness Endpoint

### Objective

Verify that the GitForge readiness endpoint reports the application ready for service.

### Precondition

ENV-026 backend runtime is active.

### Command

```powershell
Invoke-WebRequest http://127.0.0.1:8000/api/v1/health/readiness -UseBasicParsing
```

### Expected Result

HTTP status:

```text
200
```

The readiness response must indicate that required application dependencies are available according to the implemented readiness contract.

### Result

```text
PASS
```

---

## 37. ENV-029 — Frontend Runtime

### Objective

Verify that the Vite frontend development runtime can start successfully.

### Command

Run in a dedicated PowerShell terminal:

```powershell
npm.cmd run dev -- --host 127.0.0.1
```

### Expected Result

The frontend becomes reachable at:

```text
http://127.0.0.1:5173
```

### Result

```text
PASS
```

---

## 38. ENV-030 — Frontend/Backend Integration Reachability

### Objective

Verify that the frontend runtime and backend API runtime can coexist using the configured Vite API proxy.

### Preconditions

The following runtimes are active:

```text
Frontend: http://127.0.0.1:5173
Backend:  http://127.0.0.1:8000
```

### Validation

The frontend must be able to issue configured `/api` requests through the Vite proxy without an infrastructure-level connection failure.

This validation confirms connectivity only.

Detailed API behavior is tested by later M5 work packages.

### Expected Result

No proxy configuration, connection-refused, host-resolution, or backend-reachability failure prevents frontend/API communication.

### Result

```text
PASS
```

---

## 39. ENV-031 — Configuration Safety

### Objective

Verify that test execution does not depend on accidentally committed secrets or unsafe production configuration.

### Checks

Execute:

```powershell
git status --short
```

and inspect tracked configuration intentionally.

The following rules apply:

- `.env` must not be committed if excluded by project policy;
- database passwords must not be added to M5 documentation;
- PAT values must not be committed;
- session secrets must not be committed;
- generated private repository test data must not contain real credentials;
- production secrets must not be required for local M5 execution;
- test credentials must be synthetic;
- M5 evidence must redact sensitive values.

### Result

```text
PASS
```

---

## 40. ENV-032 — Test Evidence Readiness

### Objective

Verify that the M5 process can preserve sufficient evidence for later validation and traceability.

### Required Evidence Types

Depending on the test case, acceptable evidence may include:

- PowerShell command output;
- PHPUnit/Laravel test output;
- Vitest output;
- TypeScript compiler output;
- ESLint output;
- Pint output;
- HTTP status and response evidence;
- database query evidence;
- Git command output;
- filesystem verification;
- screenshots where UI behavior must be demonstrated;
- defect records;
- requirement-to-test mappings;
- final test result tables.

### Evidence Rule

Evidence must:

- identify the tested behavior;
- be reproducible where practical;
- avoid disclosure of secrets;
- preserve relevant failure output;
- distinguish expected from actual results;
- support later WP-19 Test Traceability and WP-20 Testing Validation.

### Result

```text
PASS
```

---

## 41. Environment Validation Execution Order

WP-03 should be executed in the following order:

```text
1. Repository and branch validation
2. Git validation
3. PHP and Composer validation
4. Laravel bootstrap validation
5. PostgreSQL validation
6. Database connectivity validation
7. Migration validation
8. Node.js and npm validation
9. Frontend dependency validation
10. TypeScript validation
11. Frontend lint validation
12. Frontend automated-test validation
13. Frontend production-build validation
14. Backend automated-test validation
15. Pint validation
16. Laravel filesystem validation
17. Repository-storage prerequisite validation
18. Backend runtime validation
19. Liveness validation
20. Readiness validation
21. Frontend runtime validation
22. Frontend/backend reachability validation
23. Configuration-safety validation
24. Test-evidence readiness validation
```

A failed P0 prerequisite must be corrected before dependent validation continues.

---

## 42. Known Environment Characteristics

The following characteristics are known from the completed M4 implementation validation and provide historical context for WP-03.

They are not automatically treated as fresh WP-03 evidence.

### Backend Automated Tests

At the end of M4:

```text
169 passed
581 assertions
1 skipped
0 failed
```

The skipped test was associated with a Windows/PHP environment limitation affecting creation of a directory symbolic link outside the repository root.

This limitation was previously assessed as non-blocking for M4.

WP-03 must determine whether it remains non-blocking for M5.

### Frontend Automated Tests

At the end of M4:

```text
6 test files passed
23 tests passed
```

### TypeScript

At the end of M4:

```text
npx.cmd tsc --noEmit
PASS
```

### Frontend Lint

At the end of M4:

```text
npm.cmd run lint
PASS
```

### Frontend Build

At the end of M4:

```text
npm.cmd run build
PASS
```

A Vite bundle-size advisory was observed for the primary JavaScript bundle.

The advisory was non-blocking.

### Laravel Pint

At the end of M4:

```text
vendor\bin\pint --test
PASS
```

### Runtime Health

At the end of M4:

```text
Liveness: HTTP 200
Readiness: HTTP 200
Frontend: HTTP 200
```

These historical results establish a reference point only.

Formal WP-03 execution requires fresh validation.

---

## 43. Environment Limitation — Windows Directory Symlink Support

A previously observed environment-specific limitation affects a repository-storage test involving a directory symbolic link outside the configured repository root.

The Windows/PHP environment did not provide the required directory symlink capability for that specific automated test.

The environment limitation may be accepted during M5 only if all of the following remain true:

- the limitation is caused by the local operating-system/runtime environment rather than by GitForge application behavior;
- the corresponding security control is validated through another reliable test path where possible;
- the limitation does not prevent normal repository operations;
- the limitation does not prevent Git Smart HTTP testing;
- the limitation does not invalidate path-containment verification;
- the limitation is recorded in M5 evidence;
- no requirement requiring the unsupported operating-system feature is falsely marked as verified.

If these conditions are not met, the relevant test must be marked `BLOCKED`.

---

## 44. Test Data Isolation

Formal M5 execution must use controlled test data.

Test data should use synthetic entities such as:

```text
test-owner
test-member
test-outsider
public-repository
private-repository
test-token
test-branch
test-commit
```

Real user credentials must not be required.

Tests that create repositories, tokens, users, sessions, or temporary files must clean them up where practical.

Test fixtures must not modify unrelated repositories or unrelated development data.

---

## 45. Database Safety

Before destructive or state-mutating test execution:

- confirm the active database;
- confirm the environment is appropriate for testing;
- avoid running destructive test operations against unrelated databases;
- use automated test isolation where implemented;
- use transactions or refresh mechanisms where appropriate.

The configured development database is:

```text
git_hosting_platform
```

Automated tests may use environment-specific test database behavior as defined by the project's test configuration.

No test should assume that destructive operations are safe solely because the application is running locally.

---

## 46. Repository Storage Safety

Repository-storage tests must use paths under the configured GitForge repository root or dedicated controlled fixtures.

Tests must not intentionally mutate:

- the GitForge source repository;
- unrelated local Git repositories;
- user home directories;
- operating-system directories;
- directories outside controlled test fixtures.

Any path-containment test that intentionally references an external path must perform only the minimum safe operation required to verify rejection.

---

## 47. Runtime Process Management

Backend and frontend runtime processes used during WP-03 must be started in dedicated terminals where practical.

Backend:

```powershell
php artisan serve --host=127.0.0.1 --port=8000
```

Frontend:

```powershell
npm.cmd run dev -- --host 127.0.0.1
```

After runtime validation is complete, unnecessary development servers should be stopped to avoid:

- port conflicts;
- stale application state;
- accidental testing against an old process;
- ambiguity regarding which code revision is executing.

---

## 48. Port Expectations

The normal local M5 ports are:

| Service | Host | Port |
|---|---|---:|
| PostgreSQL | `127.0.0.1` | 5432 |
| Laravel backend | `127.0.0.1` | 8000 |
| Vite frontend | `127.0.0.1` | 5173 |

A changed port is acceptable only if:

- the change is intentional;
- dependent configuration is updated consistently;
- the actual test endpoint is documented;
- no validation result is attributed to a different stale process.

---

## 49. Failure Handling

If an environment validation fails:

1. record the command;
2. preserve the relevant failure output;
3. identify whether the failure is:
   - application-related;
   - dependency-related;
   - operating-system-related;
   - configuration-related;
   - database-related;
   - network/port-related;
   - tooling-related;
4. determine whether the failure blocks M5;
5. correct the environment when appropriate;
6. repeat the affected validation;
7. preserve the final result.

Environment repair must not silently change an approved system requirement or design decision.

---

## 50. Defect Classification During WP-03

An observed problem is classified as an application defect when the environment satisfies its documented prerequisites but GitForge still behaves incorrectly.

An observed problem is classified as an environment issue when GitForge cannot be fairly evaluated because a prerequisite is unavailable or incorrectly configured.

Examples of environment issues include:

- PostgreSQL service not running;
- missing PHP extension;
- missing npm dependencies;
- occupied application port;
- unsupported local filesystem operation;
- incorrect local `.env` value;
- stale development server.

Environment issues must not be falsely reported as application defects.

---

## 51. Freshness Requirement

M4 validation results are historical evidence.

WP-03 must produce fresh environment evidence before the dependent M5 test work packages are considered formally executed.

Fresh validation is necessary because:

- source code may have changed;
- dependency state may have changed;
- database state may have changed;
- runtime configuration may have changed;
- operating-system state may have changed;
- ports or services may have changed;
- repository-storage fixtures may have changed.

---

## 52. Traceability

WP-03 supports the execution of the test cases specified in:

```text
docs/SDLC/06-testing/02-test-case-specification.md
```

Environment validation itself is primarily a test-process prerequisite rather than a substitute for requirement verification.

Formal requirement-to-test traceability will be consolidated in:

```text
WP-19 — Test Traceability
```

---

## 53. WP-03 Entry Criteria

WP-03 may execute when:

- M4 Implementation is complete;
- M5 has formally started;
- WP-01 Testing Plan is approved;
- WP-02 Test Case Specification is approved;
- branch `testing/m5` exists;
- GitForge source is available locally;
- required local services can be started.

Current entry assessment:

```text
M4 complete: YES
M5 started: YES
WP-01 approved: YES
WP-02 approved: YES
testing/m5 branch available: YES
WP-03 execution: READY
```

---

## 54. WP-03 Exit Criteria

WP-03 is complete only when:

- all 32 environment validation checks have been addressed;
- all P0 checks pass;
- required P1 checks pass or have a documented non-blocking justification;
- database connectivity is confirmed;
- migrations are confirmed current;
- backend automated-test infrastructure executes;
- frontend automated-test infrastructure executes;
- TypeScript validation executes;
- lint validation executes;
- backend formatting validation executes;
- frontend build executes;
- backend runtime is reachable;
- frontend runtime is reachable;
- liveness is confirmed;
- readiness is confirmed;
- repository-storage prerequisites are usable;
- Git is callable;
- configuration safety is confirmed;
- known environment limitations are documented;
- no blocking environment defect remains;
- evidence is sufficient for later M5 validation;
- this document is updated with actual results;
- WP-03 is formally approved.

---

## 55. WP-03 Approval Rule

WP-03 must not be approved merely because the same environment passed during M4.

Approval requires fresh execution of the WP-03 validation checks.

The final decision must be based on current evidence.

Possible decisions:

```text
APPROVED
BLOCKED
REQUIRES CORRECTION
```

---

## 56. Current WP-03 Status

Current state:

```text
WP-03 document created
Validation specification defined
32 environment checks defined
Fresh environment execution not yet performed
Status: APPROVED
```

Next:

- all 32 WP-03 environment checks executed and passed;
- execution evidence recorded;
- no blocking environment issue remains;
- WP-03 exit criteria verified;
- WP-03 approved;
- begin WP-04 Authentication Testing.

---

## 57. Conclusion

WP-03 defines the formal validation procedure for the GitForge V1.0.0 M5 test environment.

The environment must demonstrate that the following foundations are operational before detailed functional testing proceeds:

- Git repository context;
- PHP runtime;
- Laravel runtime;
- Composer dependency environment;
- PostgreSQL connectivity;
- current database migrations;
- Node.js and npm runtime;
- frontend dependencies;
- TypeScript;
- frontend linting;
- frontend testing;
- frontend build;
- backend automated testing;
- Laravel Pint;
- runtime storage;
- repository storage;
- Git process availability;
- backend HTTP runtime;
- frontend HTTP runtime;
- liveness;
- readiness;
- frontend/backend integration reachability;
- configuration safety;
- test evidence capability.

Current WP-03 decision:

```text
APPROVED
```
