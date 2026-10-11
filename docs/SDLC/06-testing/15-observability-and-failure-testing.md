# Observability and Failure Testing

| Field | Value |
|---|---|
| Project | GitForge — Web Git Hosting Platform |
| Release | V1.0.0 |
| Milestone | M5 — Testing |
| SDLC phase | Testing |
| Work package | WP-15 — Observability and Failure Testing |
| Status | Approved |
| Last updated | 2026-08-22 |

---

## 1. Purpose

This document records the observability, readiness, failure-handling, and exception-behavior verification results for GitForge V1.0.0.

The objective of WP-15 is to verify that the application:

- exposes usable health and readiness signals;
- produces consistent request correlation information;
- records operational events without leaking sensitive data;
- detects dependency failures;
- fails safely when Git, storage, or database operations fail;
- returns controlled error behavior rather than false success;
- preserves repository and database consistency during supported failure conditions.

---

## 2. Scope

WP-15 covers the formal test cases:

- `TC-OBS-001` through `TC-OBS-006`
- `TC-FAIL-001` through `TC-FAIL-007`

The work package covers:

- liveness;
- readiness;
- database dependency failure;
- Git dependency failure;
- request correlation;
- sensitive log handling;
- missing repository behavior;
- invalid Git object and commit handling;
- Git command failures;
- malformed API requests;
- repository storage failures;
- database mutation failures.

No new V1.0.0 product functionality is introduced by this work package.

---

## 3. Observability Test Cases

| Test Case | Description | Priority | Result |
|---|---|---:|---|
| TC-OBS-001 | Liveness Endpoint Returns Healthy Response | P2 | PASS |
| TC-OBS-002 | Readiness Endpoint Returns Healthy Response | P2 | PASS |
| TC-OBS-003 | Readiness Detects Database Dependency Failure | P2 | PASS |
| TC-OBS-004 | Readiness Detects Git Dependency Failure | P2 | PASS |
| TC-OBS-005 | Request Observability Produces Correlation Information | P3 | PASS |
| TC-OBS-006 | Logs Do Not Expose PAT Secret | P1 | PASS |

All formal observability cases passed.

---

## 4. Failure and Exception Test Cases

| Test Case | Description | Priority | Result |
|---|---|---:|---|
| TC-FAIL-001 | Missing Repository Produces Controlled Response | P2 | PASS |
| TC-FAIL-002 | Invalid Commit Identifier Produces Controlled Response | P2 | PASS |
| TC-FAIL-003 | Unsupported Git Object Produces Controlled Response | P2 | PASS |
| TC-FAIL-004 | Git Command Failure Does Not Corrupt Repository | P1 | PASS |
| TC-FAIL-005 | Malformed API Payload Is Rejected | P2 | PASS |
| TC-FAIL-006 | Storage Failure Is Reported Safely | P1 | PASS |
| TC-FAIL-007 | Database Failure Does Not Produce False Success | P1 | PASS |

All formal failure and exception cases passed.

---

## 5. TC-OBS-001 — Liveness Endpoint

Evidence:

`backend/tests/Feature/Observability/HealthReadinessTest.php`

Relevant test:

`test_liveness_endpoint_remains_available`

The test requests:

    /up

Verification confirms that the application liveness endpoint responds successfully while the application process is available.

Result:

    PASS

---

## 6. TC-OBS-002 — Readiness Endpoint

Evidence:

`backend/tests/Feature/Observability/HealthReadinessTest.php`

Relevant test:

`test_readiness_returns_minimal_ok_response_when_dependencies_are_ready`

Verification confirms that when all required dependencies are available:

- readiness reports an HTTP success response;
- the response contains the minimal public readiness status;
- dependency details are not exposed;
- a request correlation identifier is returned.

Result:

    PASS

---

## 7. TC-OBS-003 — Database Dependency Failure

Evidence:

`backend/tests/Feature/Observability/InfrastructureObservabilityTest.php`

Relevant test:

`test_database_connectivity_failure_records_database_and_readiness_events`

The test configures an unavailable database connection and executes real `HealthService` readiness evaluation.

Verification confirms that:

- readiness becomes false;
- the database check becomes `unavailable`;
- a `database_unavailable` operational event is recorded;
- a `readiness_failed` event is recorded;
- `failed_checks` includes `database`;
- the application does not report a false-ready state.

Supporting HTTP evidence:

`backend/tests/Feature/Observability/HealthReadinessTest.php`

Relevant test:

`test_readiness_returns_503_without_exposing_dependency_details`

Result:

    PASS

---

## 8. TC-OBS-004 — Git Dependency Failure

Evidence:

`backend/tests/Feature/Observability/InfrastructureObservabilityTest.php`

Dedicated test:

`test_git_dependency_failure_marks_readiness_unavailable`

The test configures the injected `GitCommandRunner` so that its Git version check throws an exception.

Verification confirms that:

- readiness becomes false;
- `checks.git` becomes `unavailable`;
- database remains available;
- repository storage remains available;
- configuration remains available;
- `readiness_failed` is recorded;
- `failed_checks` includes `git`.

This confirms that Git dependency failure is detected independently from other readiness dependencies.

Result:

    PASS

---

## 9. TC-OBS-005 — Request Correlation

Evidence:

`backend/tests/Feature/Observability/RequestObservabilityTest.php`

Relevant tests:

- `test_request_receives_server_generated_correlation_id`
- `test_request_start_and_completion_use_same_request_id`

Supporting evidence:

`backend/tests/Feature/Observability/AuthorizationObservabilityTest.php`

`backend/tests/Unit/Services/Git/GitCommandRunnerObservabilityTest.php`

Verification confirms that:

- the server generates a request correlation identifier;
- client-controlled request IDs are not blindly trusted;
- the response contains `X-Request-ID`;
- request-start and request-completion events use the same identifier;
- authorization events preserve the same correlation context;
- Git process observability uses the active request identifier where applicable.

Result:

    PASS

---

## 10. TC-OBS-006 — PAT Secret Is Not Logged

Evidence:

`backend/tests/Feature/Observability/ApplicationObservabilityTest.php`

Relevant tests:

- `test_pat_creation_log_never_contains_plaintext_or_hash`
- `test_pat_authentication_failure_never_logs_basic_credentials`

Supporting evidence:

`backend/tests/Feature/Observability/RequestObservabilityTest.php`

Relevant test:

`test_sensitive_request_data_is_not_in_request_log_context`

Verification confirms that application log contexts do not expose:

- raw PAT values;
- PAT hashes;
- Basic authentication credentials;
- passwords;
- authorization headers;
- sensitive token request payload data.

Result:

    PASS

---

## 11. TC-FAIL-001 — Missing Repository

Evidence includes:

`backend/tests/Feature/Api/V1/RepositoryBrowserTest.php`

Relevant test:

`test_missing_repository_storage_fails_closed`

Supporting service evidence:

`backend/tests/Unit/Services/Git/GitSmartHttpServiceTest.php`

Relevant test:

`test_missing_repository_storage_is_mapped_to_transport_failure`

Supporting infrastructure evidence:

`backend/tests/Feature/Observability/InfrastructureObservabilityTest.php`

Relevant test:

`test_missing_repository_storage_records_named_event`

Verification confirms that missing repository storage:

- does not crash the application;
- produces controlled failure behavior;
- is translated to the expected application or transport failure;
- produces an operationally identifiable event.

Result:

    PASS

---

## 12. TC-FAIL-002 — Invalid Commit Identifier

Evidence:

`backend/tests/Feature/Api/V1/RepositoryBrowserTest.php`

Relevant test:

`test_invalid_commit_identifier_is_rejected`

Supporting service evidence:

`backend/tests/Unit/Services/Git/RepositoryBrowserServiceTest.php`

Relevant tests:

- `test_it_rejects_invalid_commit_identifier`
- `test_it_rejects_unknown_commit_identifier`

Verification confirms that invalid or unknown commit identifiers produce controlled validation or not-found behavior.

Result:

    PASS

---

## 13. TC-FAIL-003 — Unsupported Git Object

Evidence:

`backend/tests/Unit/Services/Git/RepositoryBrowserServiceTest.php`

Relevant tests:

- `test_it_rejects_non_commit_git_object`
- `test_it_rejects_directory_when_blob_is_requested`
- `test_it_rejects_binary_blob`

Verification confirms that incompatible Git object usage is rejected without application instability.

In particular, a non-commit Git object cannot be accepted as a commit resource.

Result:

    PASS

---

## 14. TC-FAIL-004 — Git Command Failure

Evidence:

`backend/tests/Unit/Services/Git/GitSmartHttpServiceTest.php`

Relevant tests:

- `test_git_command_failure_is_translated_to_transport_exception`
- `test_git_command_timeout_is_translated_to_transport_timeout`

Supporting evidence:

`backend/tests/Unit/Services/Git/GitCommandRunnerObservabilityTest.php`

Relevant tests:

- `test_failed_process_logs_bounded_failure_without_stderr_or_arguments`
- `test_timeout_logs_classified_timeout_without_process_payload`

Supporting Git HTTP evidence:

`backend/tests/Feature/Git/GitSmartHttpTest.php`

Relevant behavior includes corrupt repository storage failing closed for Git transport operations.

Verification confirms that:

- Git process failures are converted into controlled application exceptions;
- Git timeout conditions are classified;
- sensitive process payloads are not exposed through logs;
- repository operations fail closed rather than producing a false successful result.

Result:

    PASS

---

## 15. TC-FAIL-005 — Malformed API Payload

Evidence exists across the API regression suite.

Primary examples include:

`backend/tests/Feature/Api/V1/AuthenticationTest.php`

`backend/tests/Feature/Api/V1/RepositoryTest.php`

`backend/tests/Feature/Api/V1/PersonalAccessTokenTest.php`

`backend/tests/Feature/Api/V1/RepositoryBrowserTest.php`

Verified negative behavior includes:

- invalid authentication payloads;
- invalid repository names;
- invalid repository visibility values;
- invalid PAT creation input;
- invalid repository-browser query parameters.

Verification confirms that malformed or invalid API input produces controlled validation responses and does not produce unintended persistence.

Result:

    PASS

---

## 16. TC-FAIL-006 — Storage Failure

Evidence:

`backend/tests/Feature/Api/V1/RepositoryTest.php`

Relevant test:

`test_failed_storage_creation_rolls_back_repository_metadata`

Supporting evidence:

`backend/tests/Unit/Services/Git/RepositoryStorageServiceTest.php`

Relevant tests:

- `test_cleanup_partial_removes_failed_creation_storage`
- `test_cleanup_partial_is_idempotent_for_missing_storage`
- `test_failed_initialization_leaves_no_repository_directory`
- `test_resolve_existing_rejects_missing_storage`

Supporting observability evidence:

`backend/tests/Feature/Observability/ApplicationObservabilityTest.php`

Relevant test:

`test_failed_repository_storage_never_logs_repository_success`

Verification confirms that:

- repository metadata is rolled back when storage creation fails;
- failed initialization does not leave a managed repository directory;
- partial-storage cleanup is safe;
- failed storage creation does not produce repository success logging.

Result:

    PASS

---

## 17. TC-FAIL-007 — Database Failure Without False Success

Dedicated evidence:

`backend/tests/Feature/Failure/DatabaseFailureTest.php`

Test:

`test_database_failure_does_not_report_successful_repository_mutation`

The test creates a real repository and then installs a temporary SQLite trigger that forces `UPDATE repositories` to fail.

The test then attempts to change repository visibility.

Verification confirms that:

- the database mutation fails;
- the API returns a server error;
- the response does not contain the repository-update success message;
- repository visibility remains unchanged;
- persisted repository data remains in its pre-operation state.

This provides direct automated evidence that a dependent database mutation failure does not result in a false successful operation.

Result:

    PASS

---

## 18. Additional Observability Evidence

### 18.1 Application Events

`ApplicationObservabilityTest.php` verifies:

- classified login failure;
- PAT creation secrecy;
- PAT authentication secrecy;
- repository creation success only after complete creation;
- repository creation failure behavior;
- repository visibility-change logging.

### 18.2 Authorization Events

`AuthorizationObservabilityTest.php` verifies:

- anonymous public read allow decisions;
- private repository denial classification;
- non-owner write denial classification.

### 18.3 Infrastructure Events

`InfrastructureObservabilityTest.php` verifies:

- missing repository storage events;
- unexpected existing target events;
- unsafe storage path events;
- database dependency failure;
- Git dependency failure.

### 18.4 Git Process Events

`GitCommandRunnerObservabilityTest.php` verifies:

- Git process start and completion correlation;
- classified failure;
- classified timeout;
- bounded log payloads.

---

## 19. WP-15 Aggregate Test Run

Command:

    php artisan test `
        tests/Feature/Observability `
        tests/Feature/Failure `
        tests/Feature/Api/V1/RepositoryTest.php `
        tests/Feature/Api/V1/RepositoryBrowserTest.php `
        tests/Unit/Services/Git/RepositoryBrowserServiceTest.php `
        tests/Unit/Services/Git/GitSmartHttpServiceTest.php `
        tests/Unit/Services/Git/RepositoryStorageServiceTest.php `
        tests/Unit/Services/Git/GitCommandRunnerObservabilityTest.php

Result:

    Tests:    1 skipped, 102 passed (333 assertions)
    Failures: 0
    Duration: 145.46s

The skipped test is:

`RepositoryStorageServiceTest::test_symlinked_repositories_directory_outside_root_is_rejected`

The skip is caused by the existing Windows/PHP environment limitation where directory symlink creation is unavailable.

This is an environment limitation and not a V1.0.0 product failure.

---

## 20. Focused New Test Results

### 20.1 Git Readiness Failure

Command:

    php artisan test tests/Feature/Observability/InfrastructureObservabilityTest.php

Result:

    Tests:    5 passed (20 assertions)
    Failures: 0
    Duration: 0.45s

The suite includes the newly added:

`test_git_dependency_failure_marks_readiness_unavailable`

### 20.2 Database Mutation Failure

Command:

    php artisan test tests/Feature/Failure/DatabaseFailureTest.php

Result:

    Tests:    1 passed (4 assertions)
    Failures: 0
    Duration: 0.51s

This provides dedicated evidence for `TC-FAIL-007`.

---

## 21. Code Style Verification

Modified or added PHP test files:

- `backend/tests/Feature/Observability/InfrastructureObservabilityTest.php`
- `backend/tests/Feature/Failure/DatabaseFailureTest.php`

Initial Pint verification found:

- line-ending normalization required for `DatabaseFailureTest.php`;
- end-of-file formatting issues in both files.

Pint corrected the formatting.

Final verification command:

    vendor\bin\pint --test `
        tests\Feature\Observability\InfrastructureObservabilityTest.php `
        tests\Feature\Failure\DatabaseFailureTest.php

Final result:

    PASS
    2 files

No remaining PHP style violations were reported.

---

## 22. Changes Introduced by WP-15

WP-15 required two automated-test coverage additions.

### 22.1 Git Dependency Readiness Coverage

Added:

`InfrastructureObservabilityTest::test_git_dependency_failure_marks_readiness_unavailable`

This closes the automated evidence gap for `TC-OBS-004`.

No production code modification was necessary because `HealthService` already handled Git dependency failure correctly.

### 22.2 Database Mutation Failure Coverage

Added:

`backend/tests/Feature/Failure/DatabaseFailureTest.php`

This closes the automated evidence gap for `TC-FAIL-007`.

The test uses an isolated test-database trigger to simulate a database mutation failure.

No production code modification was required.

---

## 23. Defects and Observations

No blocking V1.0.0 production defect was identified during WP-15.

Two formal test cases lacked sufficiently direct automated evidence before this work package:

- `TC-OBS-004` — Git dependency readiness failure;
- `TC-FAIL-007` — database mutation failure without false success.

Both gaps were closed through test-only changes.

Existing production behavior already satisfied the expected results.

The only skipped aggregate test is caused by the known Windows/PHP directory-symlink capability limitation.

---

## 24. WP-15 Traceability

| Test Case | Primary Evidence |
|---|---|
| TC-OBS-001 | `HealthReadinessTest.php` |
| TC-OBS-002 | `HealthReadinessTest.php` |
| TC-OBS-003 | `InfrastructureObservabilityTest.php`, `HealthReadinessTest.php` |
| TC-OBS-004 | `InfrastructureObservabilityTest.php` |
| TC-OBS-005 | `RequestObservabilityTest.php` |
| TC-OBS-006 | `ApplicationObservabilityTest.php`, `RequestObservabilityTest.php` |
| TC-FAIL-001 | `RepositoryBrowserTest.php`, `GitSmartHttpServiceTest.php` |
| TC-FAIL-002 | `RepositoryBrowserTest.php`, `RepositoryBrowserServiceTest.php` |
| TC-FAIL-003 | `RepositoryBrowserServiceTest.php` |
| TC-FAIL-004 | `GitSmartHttpServiceTest.php`, `GitCommandRunnerObservabilityTest.php` |
| TC-FAIL-005 | API feature test suite |
| TC-FAIL-006 | `RepositoryTest.php`, `RepositoryStorageServiceTest.php` |
| TC-FAIL-007 | `DatabaseFailureTest.php` |

---

## 25. WP-15 Acceptance Assessment

WP-15 exit conditions are satisfied.

Evidence confirms:

- liveness is available;
- readiness reports success only when required dependencies are available;
- database dependency failure produces not-ready behavior;
- Git dependency failure produces not-ready behavior;
- request correlation information is generated consistently;
- sensitive PAT and credential material is excluded from applicable log context;
- missing repository resources fail in a controlled manner;
- invalid commit identifiers are rejected safely;
- incompatible Git objects are rejected safely;
- Git command failures are translated into controlled application failures;
- malformed API requests are rejected;
- repository storage failures preserve database/filesystem consistency;
- database mutation failure does not produce false success;
- the aggregate WP-15 evidence run passes with 102 tests and 333 assertions;
- no blocking V1.0.0 observability or failure-handling defect remains open.

Therefore:

> WP-15 — Observability and Failure Testing is complete and approved for GitForge V1.0.0.
