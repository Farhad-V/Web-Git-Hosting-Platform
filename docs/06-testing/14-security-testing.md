# Security Testing

| Field | Value |
|---|---|
| Project | GitForge — Web Git Hosting Platform |
| Release | V1.0.0 |
| Milestone | M5 — Testing |
| SDLC phase | Testing |
| Work package | WP-14 — Security Testing |
| Status | Approved |
| Last updated | 2026-08-22 |

---

## 1. Purpose

This document records the security verification results for GitForge V1.0.0.

The objective of WP-14 is to verify the approved security controls and abuse-case protections defined for the V1.0.0 release.

Security testing focuses on externally reachable attack surfaces, repository authorization boundaries, repository-browser input handling, Personal Access Token protection, CSRF enforcement, Git process execution safety, and deployment-security configuration.

---

## 2. Scope

WP-14 verifies the following security areas:

- SQL injection resistance
- command injection resistance
- repository-browser path traversal protection
- private repository concealment
- Git Smart HTTP authorization
- Personal Access Token secrecy
- revoked-token rejection
- CSRF protection
- secure production configuration
- sensitive-file exposure prevention
- controlled Git executable and service execution

The formal scope is defined by:

- `TC-SEC-001` through `TC-SEC-012`

No new V1.0.0 product functionality is introduced by this work package.

---

## 3. Approved Security Test Cases

| Test Case | Description | Priority | Result |
|---|---|---:|---|
| TC-SEC-001 | SQL Injection Attempt in Authentication Input | P1 | PASS |
| TC-SEC-002 | SQL Injection Attempt in Repository Input | P1 | PASS |
| TC-SEC-003 | Command Injection Through Repository Identifier | P1 | PASS |
| TC-SEC-004 | Path Traversal Through Browser Path | P1 | PASS |
| TC-SEC-005 | Private Repository Concealment Through API | P1 | PASS |
| TC-SEC-006 | Private Repository Concealment Through Git HTTP | P1 | PASS |
| TC-SEC-007 | Raw PAT Is Never Included in Token Listing | P1 | PASS |
| TC-SEC-008 | Revoked PAT Cannot Authenticate | P1 | PASS |
| TC-SEC-009 | CSRF Protection Rejects Invalid State-Changing Request | P1 | PASS |
| TC-SEC-010 | Production Configuration Does Not Enable Unsafe Debug Defaults | P1 | PASS |
| TC-SEC-011 | Sensitive Files Are Not Intended for Direct Web Exposure | P1 | PASS |
| TC-SEC-012 | Git Process Cannot Execute Arbitrary Binary | P1 | PASS |

All formal WP-14 security test cases passed.

---

## 4. Security Test Evidence

### 4.1 TC-SEC-001 — Authentication SQL Injection

Dedicated test:

`backend/tests/Feature/Security/SqlInjectionSecurityTest.php`

Test method:

`test_login_sql_injection_payload_does_not_bypass_authentication`

The test submits a SQL-injection-oriented email payload to the login endpoint and verifies that:

- authentication does not succeed
- the request is rejected
- no authenticated session is established
- the persisted user remains unchanged

Result:

    PASS

---

### 4.2 TC-SEC-002 — Repository SQL Injection

Evidence:

`backend/tests/Feature/Security/SqlInjectionSecurityTest.php`

Relevant tests:

- `test_repository_name_sql_injection_payload_is_rejected_as_invalid_input`
- `test_repository_owner_filter_treats_user_identity_as_bound_value`

Verification confirms that:

- SQL-injection-oriented repository names are rejected by validation
- no unauthorized repository row is created
- user identity used in repository filtering behaves as a bound value
- repository query semantics are not altered

Result:

    PASS

---

### 4.3 TC-SEC-003 — Command Injection

Evidence:

`backend/tests/Feature/Security/ProcessInjectionSecurityTest.php`

Relevant test:

`test_process_arguments_are_not_interpreted_by_a_shell`

The test provides a shell-metacharacter payload containing a command separator and output-redirection syntax.

Verification confirms that:

- the payload is passed as a literal process argument
- the payload is not interpreted by a shell
- no marker file is created
- no unintended operating-system command executes

Supporting evidence:

`test_branch_name_cannot_be_used_as_git_option`

This confirms that option-shaped untrusted branch input is rejected before Git execution.

Result:

    PASS

---

### 4.4 TC-SEC-004 — Repository Browser Path Traversal

Dedicated test:

`backend/tests/Feature/Api/V1/RepositoryBrowserTest.php`

Test method:

`test_repository_browser_rejects_path_traversal_attempt`

The test submits:

    ../README.md

through the repository blob browser path.

Verification confirms:

- the request is rejected
- response status is `422 Unprocessable Entity`
- error reason is `invalid_path`
- traversal outside the Git repository tree is not allowed

The production `RepositoryBrowserService` additionally rejects path segments equal to:

- `.`
- `..`

Result:

    PASS

---

### 4.5 TC-SEC-005 — Private Repository Concealment Through API

Evidence:

`backend/tests/Feature/Security/PrivateRepositoryConcealmentSecurityTest.php`

Relevant tests:

- `test_non_owner_receives_not_found_for_private_repository`
- `test_anonymous_visitor_receives_not_found_for_private_repository`
- `test_missing_repository_has_same_external_status_as_concealed_private_repository`

Supporting repository-browser tests also verify concealment for:

- branch listing
- tree browsing
- commit listing
- commit details
- blob access

Verification confirms that unauthorized requesters do not receive protected private-repository metadata.

Result:

    PASS

---

### 4.6 TC-SEC-006 — Private Repository Concealment Through Git HTTP

Evidence:

`backend/tests/Feature/Git/GitSmartHttpTest.php`

Relevant tests include:

- `test_private_repository_requires_authentication_for_upload_pack`
- `test_private_upload_pack_rpc_requires_authentication`
- `test_invalid_token_cannot_access_private_repository`
- `test_revoked_token_cannot_access_private_repository`
- `test_token_username_must_match_token_owner_namespace`

Verification confirms that:

- unauthenticated private Git reads are rejected
- invalid PAT authentication is rejected
- revoked PAT authentication is rejected
- mismatched namespace credentials are rejected
- Git repository content is not transferred to unauthorized requesters

Result:

    PASS

---

## 5. Personal Access Token Security

### 5.1 TC-SEC-007 — Plaintext PAT Is Not Listed

Evidence:

`backend/tests/Feature/Api/V1/PersonalAccessTokenTest.php`

Relevant test:

`test_plaintext_token_is_not_exposed_when_listing_tokens`

Verification confirms that PAT listing responses expose only safe token metadata and never include the raw plaintext secret.

Result:

    PASS

---

### 5.2 TC-SEC-008 — Revoked PAT Rejection

Evidence:

`backend/tests/Feature/Git/GitSmartHttpTest.php`

Relevant test:

`test_revoked_token_cannot_access_private_repository`

Supporting real Git client evidence from earlier M5 work packages also verifies revoked-token rejection for clone and push operations.

Verification confirms that a revoked PAT cannot authenticate successfully against protected Git Smart HTTP operations.

Result:

    PASS

---

## 6. CSRF Security

### TC-SEC-009 — Invalid State-Changing Request Rejected

Evidence:

`backend/tests/Feature/Security/CsrfSecurityTest.php`

Relevant tests:

- `test_state_changing_session_request_without_csrf_token_is_rejected`
- `test_state_changing_session_request_with_valid_csrf_token_is_accepted`

Verification confirms that:

- state-changing session-authenticated requests without valid CSRF context are rejected
- valid CSRF context is accepted
- CSRF enforcement behaves as expected outside Laravel's automatic testing bypass

Result:

    PASS

---

## 7. Deployment Security

### 7.1 TC-SEC-010 — Secure Production Defaults

Evidence:

`backend/tests/Feature/Security/DeploymentSecurityTest.php`

Relevant test:

`test_production_environment_template_uses_secure_defaults`

Verification confirms that the production configuration template does not prescribe unsafe debug exposure or committed application secrets.

Result:

    PASS

---

### 7.2 TC-SEC-011 — Sensitive File Exposure Prevention

Evidence:

`backend/tests/Feature/Security/DeploymentSecurityTest.php`

Relevant tests include:

- `test_nginx_profile_enforces_https_and_separates_public_storage`
- `test_php_fpm_profile_uses_least_privilege_runtime_identity`
- `test_deployment_guide_preserves_security_and_recovery_boundaries`

Verification confirms that:

- the application web root is separated from backend-sensitive storage
- environment and backend-sensitive files are not intentionally exposed as public web assets
- HTTPS expectations are enforced in the deployment profile
- PHP-FPM uses a restricted runtime identity
- deployment guidance preserves security boundaries

Result:

    PASS

---

## 8. Git Process Execution Security

### TC-SEC-012 — Arbitrary Binary Execution Prevention

Evidence includes:

`backend/app/Http/Controllers/GitSmartHttpController.php`

`backend/app/Services/Git/GitSmartHttpService.php`

`backend/tests/Feature/Git/GitSmartHttpTest.php`

`backend/tests/Feature/Security/ProcessInjectionSecurityTest.php`

`backend/tests/Unit/Services/Git/GitCommandRunnerTest.php`

The externally reachable Git Smart HTTP controller maps request service values only to:

- `upload-pack`
- `receive-pack`

Unsupported service values are rejected.

`GitSmartHttpService` independently validates the same fixed allowlist before Git execution.

The HTTP request does not provide:

- a binary path
- an executable name
- an arbitrary process command

The central Git process runner additionally verifies argument integrity and avoids shell interpretation.

Supporting tests verify:

- unsupported Git services are rejected
- null-byte process arguments are rejected
- invalid process arguments are rejected
- shell metacharacters are not interpreted
- controlled Git process failures are converted into safe application failures

Result:

    PASS

---

## 9. Dedicated Security Suite Result

Command:

    php artisan test tests/Feature/Security

Result:

    Tests:    15 passed (80 assertions)
    Failures: 0
    Duration: 1.11s

The suite includes:

- CSRF security
- deployment security
- orphan repository security
- private repository concealment
- process injection security
- SQL injection security

---

## 10. Security and Repository Browser Aggregate

Command:

    php artisan test `
        tests/Feature/Security `
        tests/Feature/Api/V1/RepositoryBrowserTest.php

Result:

    Tests:    32 passed (166 assertions)
    Failures: 0
    Duration: 60.58s

This aggregate specifically confirms the newly added authentication SQL-injection and browser-path-traversal evidence together with the existing security regression suite.

---

## 11. Complete WP-14 Evidence Run

Command:

    php artisan test `
        tests/Feature/Security `
        tests/Feature/Api/V1/RepositoryBrowserTest.php `
        tests/Feature/Api/V1/PersonalAccessTokenTest.php `
        tests/Feature/Git/GitSmartHttpTest.php `
        tests/Unit/Services/Git/GitCommandRunnerTest.php

Result:

    Tests:    73 passed (298 assertions)
    Failures: 0
    Duration: 89.01s

The complete run verifies the primary evidence set for all twelve formal WP-14 security cases.

---

## 12. Code Style Verification

Modified PHP test files:

- `backend/tests/Feature/Security/SqlInjectionSecurityTest.php`
- `backend/tests/Feature/Api/V1/RepositoryBrowserTest.php`

Initial Pint verification found only end-of-file formatting issues.

Pint corrected:

- `single_blank_line_at_eof`

Final command:

    vendor\bin\pint --test `
        tests\Feature\Security\SqlInjectionSecurityTest.php `
        tests\Feature\Api\V1\RepositoryBrowserTest.php

Final result:

    PASS
    2 files

No remaining PHP style violations were reported.

---

## 13. Defects and Observations

No V1.0.0 production security defect was identified during WP-14.

Two formal security cases initially lacked dedicated automated evidence:

- authentication SQL-injection attempt
- repository-browser path traversal attempt

Inspection showed that the corresponding production controls already existed.

The missing automated coverage was added without modifying production behavior.

The following test-only changes were introduced:

- authentication SQL-injection regression test
- repository-browser traversal regression test

No production source file required correction.

---

## 14. Security Traceability

| Security Area | Primary Evidence |
|---|---|
| Authentication SQL injection | `SqlInjectionSecurityTest.php` |
| Repository SQL injection | `SqlInjectionSecurityTest.php` |
| Command injection | `ProcessInjectionSecurityTest.php` |
| Git option injection | `ProcessInjectionSecurityTest.php` |
| Browser path traversal | `RepositoryBrowserTest.php` |
| API private-repository concealment | `PrivateRepositoryConcealmentSecurityTest.php` |
| Repository-browser authorization | `RepositoryBrowserTest.php` |
| Git HTTP private-repository protection | `GitSmartHttpTest.php` |
| PAT plaintext secrecy | `PersonalAccessTokenTest.php` |
| Revoked PAT rejection | `GitSmartHttpTest.php` |
| CSRF enforcement | `CsrfSecurityTest.php` |
| Production debug safety | `DeploymentSecurityTest.php` |
| Sensitive-file exposure prevention | `DeploymentSecurityTest.php` |
| Git service allowlisting | `GitSmartHttpController.php`, `GitSmartHttpService.php`, `GitSmartHttpTest.php` |
| Process argument safety | `ProcessInjectionSecurityTest.php`, `GitCommandRunnerTest.php` |

---

## 15. WP-14 Acceptance Assessment

WP-14 exit conditions are satisfied.

Evidence confirms:

- all twelve formal `TC-SEC-*` cases pass
- SQL-injection attack paths are controlled
- command-injection and Git-option-injection paths are controlled
- repository-browser traversal attempts are rejected
- private repositories remain protected across API and Git HTTP boundaries
- plaintext PAT secrets are not disclosed through token listings
- revoked PATs cannot authenticate
- CSRF protection is enforced for applicable session-authenticated state changes
- production configuration uses secure defaults
- sensitive backend resources are separated from public exposure
- externally reachable Git execution is limited to approved Smart HTTP services
- the complete WP-14 evidence run passes with 73 tests and 298 assertions
- no blocking V1.0.0 security defect remains open

Therefore:

> WP-14 — Security Testing is complete and approved for GitForge V1.0.0.