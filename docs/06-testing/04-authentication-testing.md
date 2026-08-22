# Authentication Testing



| Field | Value |
|---|---|
| Project | GitForge — Web Git Hosting Platform |
| Version | V1.0.0 |
| SDLC phase | 6 — Testing |
| Milestone | M5 — Testing |
| Work package | WP-04 — Authentication Testing |
| Document | Authentication Testing |
| Status | Approved |
| Date | 2026-08-22 |
| Branch | `testing/m5` |



---
## 1. Purpose



This document records the formal execution and results of authentication testing for GitForge V1.0.0.



WP-04 verifies that user registration, login, logout, authenticated-session handling, protected-resource access, and CSRF protection behave according to the approved requirements, implementation, security rules, and test-case specification.



The work package executes the authentication test family defined in:



```text

docs/06-testing/02-test-case-specification.md

```



The formal authentication test identifiers are:



```text

TC-AUTH-001

TC-AUTH-002

TC-AUTH-003

TC-AUTH-004

TC-AUTH-005

TC-AUTH-006

TC-AUTH-007

TC-AUTH-008

TC-AUTH-009

TC-AUTH-010

```



---
## 2. Objectives



WP-04 verifies that:



- a valid user can register;

- duplicate email registration is rejected;

- duplicate username registration is rejected;

- a valid user can log in;

- an invalid password is rejected;

- an unknown account cannot authenticate;

- an authenticated user can log out;

- protected resources reject unauthenticated access;

- session-based state-changing operations are protected against CSRF;

- an authenticated session persists correctly across valid authenticated requests;

- authentication failures do not create authenticated state;

- authentication behavior does not expose sensitive credential information;

- authentication regression tests remain passing.



---
## 3. Test Basis



Authentication testing is based on the approved project artifacts.



Primary requirement basis:



```text

FR-001 – FR-012

FR-061 – FR-066

FR-075 – FR-078

NFR-001 – NFR-018

NFR-019 – NFR-022

UC-001 – UC-003

```



Primary test-case basis:



```text

TC-AUTH-001 – TC-AUTH-010

```



Implementation evidence includes:



```text

backend/tests/Feature/Api/V1/AuthenticationTest.php

backend/tests/Feature/Security/CsrfSecurityTest.php

```



Additional authentication-related observability and security tests may provide supporting evidence but do not replace the formal TC-AUTH test family.



---
## 4. Scope



WP-04 covers:



- registration;

- registration validation;

- unique email enforcement;

- unique username enforcement;

- login;

- login failure;

- authenticated session creation;

- authenticated profile access;

- unauthenticated protected-resource rejection;

- logout;

- session invalidation;

- CSRF bootstrap;

- CSRF rejection;

- CSRF acceptance;

- authenticated-session persistence.



---
## 5. Out of Scope



The following are tested in later M5 work packages:



- repository metadata behavior;

- repository authorization beyond authentication boundaries;

- personal access token lifecycle;

- Git Smart HTTP authentication using PAT credentials;

- repository-storage security;

- repository-browser authorization;

- complete web UI testing;

- dedicated security penetration-oriented scenarios;

- system-wide end-to-end acceptance.



Authentication-related behavior discovered by those work packages may still reference WP-04 results.



---
## 6. Entry Criteria



WP-04 may execute when:



- M4 Implementation is complete;

- WP-01 Testing Plan is approved;

- WP-02 Test Case Specification is approved;

- WP-03 Test Environment Validation is approved;

- all WP-03 P0 environment checks pass;

- backend automated-test infrastructure is operational;

- PostgreSQL is reachable;

- Laravel can bootstrap;

- branch `testing/m5` is active.



Current entry assessment:



```text

M4 complete: YES

WP-01 approved: YES

WP-02 approved: YES

WP-03 approved: YES

Test environment ready: YES

WP-04 execution: READY

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



WP-04 cannot be approved while any P1 authentication test remains `FAIL`, `BLOCKED`, or `NOT EXECUTED`.



---
## 8. Authentication Test Inventory



| Test ID | Description | Priority | Initial status |
|---|---|---|---|
| TC-AUTH-001 | Register a valid user | P2 | PASS |
| TC-AUTH-002 | Reject duplicate email registration | P2 | PASS |
| TC-AUTH-003 | Reject duplicate username registration | P2 | PASS |
| TC-AUTH-004 | Login with valid credentials | P1 | PASS |
| TC-AUTH-005 | Reject invalid password | P1 | PASS |
| TC-AUTH-006 | Reject unknown user login | P1 | PASS |
| TC-AUTH-007 | Logout authenticated user | P2 | PASS |
| TC-AUTH-008 | Reject protected request without authentication | P1 | PASS |
| TC-AUTH-009 | CSRF protection for session-based state change | P1 | PASS |
| TC-AUTH-010 | Authenticated session persists across valid requests | P2 | PASS |



Total formal authentication test cases:



```text

10

```



---
## 9. Test Environment



Authentication tests execute against the approved WP-03 environment.



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



Project branch:



```text

testing/m5

```



Backend test command location:



```text

C:\\xampp\\htdocs\\projects\\web\\Web-Git-Hosting-Platform\\backend

```



---
## 10. Test Data



Authentication tests must use synthetic test identities.



Representative test data may include:



```text

username: test-user

email: test-user@example.test

password: synthetic test password

```



Additional test users may be generated automatically by Laravel factories.



Test data must not use:



- real personal credentials;

- production credentials;

- reusable external-service passwords;

- real personal access tokens.



Passwords must never be copied into committed execution evidence.



---
## 11. Test Execution Strategy



WP-04 uses automated Laravel feature and security tests as the primary execution mechanism.



Primary automated test classes:



```text

Tests\\Feature\\Api\\V1\\AuthenticationTest

Tests\\Feature\\Security\\CsrfSecurityTest

```



The test implementation provides repeatable verification of:



- registration;

- validation;

- login;

- session establishment;

- protected API access;

- logout;

- CSRF enforcement.



Where a formal TC-AUTH scenario spans behavior represented by more than one automated assertion, the combined passing automated evidence may satisfy the formal test case.



---
## 12. Primary Authentication Test Command



From the backend directory:



```powershell

php artisan test --filter=AuthenticationTest

```



Expected result:



- authentication feature tests execute;

- all authentication tests pass;

- no authentication feature test fails.



Initial result:



```text

PASS

```



---
## 13. Primary CSRF Test Command



From the backend directory:



```powershell

php artisan test --filter=CsrfSecurityTest

```



Expected result:



- CSRF security tests execute;

- state-changing session request without valid CSRF protection is rejected;

- request with valid CSRF protection is accepted;

- all CSRF security tests pass.



Initial result:



```text

PASS

```



---
## 14. TC-AUTH-001 — Register a Valid User



### Priority



```text

P2

```



### Requirement Basis



```text

FR-001 – FR-004

```



### Preconditions



- user is not authenticated;

- submitted email does not already exist;

- submitted username does not already exist;

- valid registration data is supplied.



### Action



Submit valid registration data.



### Expected Result



- user account is created;

- password is not stored as plaintext;

- authenticated session behavior follows the approved implementation;

- registration succeeds without exposing sensitive data.



### Automated Evidence



Expected supporting test:



```text

user can register and session is established

```



### Result



```text

PASS

```



---
## 15. TC-AUTH-002 — Reject Duplicate Email Registration



### Priority



```text

P2

```



### Requirement Basis



```text

FR-001 – FR-004

FR-075 – FR-078

```



### Preconditions



An existing account already uses the submitted email.



### Action



Attempt registration using the same email.



### Expected Result



- registration is rejected;

- validation behavior is returned;

- no duplicate user record is created;

- existing account remains unchanged.



### Automated Evidence



Expected supporting test:



```text

email must be unique

```



### Result



```text

PASS

```



---
## 16. TC-AUTH-003 — Reject Duplicate Username Registration



### Priority



```text

P2

```



### Requirement Basis



```text

FR-001 – FR-004

FR-075 – FR-078

```



### Preconditions



An existing account already uses the submitted username.



### Action



Attempt registration using the same username.



### Expected Result



- registration is rejected;

- no duplicate account is created;

- existing account remains unchanged.



### Automated Evidence



Expected supporting test:



```text

username must be unique

```



### Result



```text

PASS

```



---
## 17. TC-AUTH-004 — Login with Valid Credentials



### Priority



```text

P1

```



### Requirement Basis



```text

FR-005 – FR-009

```



### Preconditions



A valid user account exists.



### Action



Submit valid login credentials.



### Expected Result



- authentication succeeds;

- authenticated session state is established;

- authenticated user identity can be retrieved through protected behavior.



### Automated Evidence



Expected supporting tests:



```text

user can login and session is established

authenticated user can view profile

```



### Result



```text

PASS

```



---
## 18. TC-AUTH-005 — Reject Invalid Password



### Priority



```text

P1

```



### Requirement Basis



```text

FR-005 – FR-009

NFR-019 – NFR-022

```



### Preconditions



A valid user exists.



### Action



Submit the correct user identity with an incorrect password.



### Expected Result



- authentication fails;

- authenticated session is not created;

- credentials are not exposed;

- protected authenticated state is unavailable.



### Automated Evidence



Expected supporting test:



```text

login rejects invalid credentials

```



### Result



```text

PASS

```



---
## 19. TC-AUTH-006 — Reject Unknown User Login



### Priority



```text

P1

```



### Requirement Basis



```text

FR-005 – FR-009

```



### Preconditions



Submitted account identity does not exist.



### Action



Attempt login.



### Expected Result



- authentication fails;

- no session is authenticated;

- response does not expose sensitive account information;

- failure behavior does not provide an unsafe account-discovery mechanism.



### Automated Evidence



This scenario is evaluated through the authentication validation and invalid-credential behavior implemented by the application.



### Result



```text

PASS

```



---
## 20. TC-AUTH-007 — Logout Authenticated User



### Priority



```text

P2

```



### Requirement Basis



```text

FR-005 – FR-012

```



### Preconditions



A user is authenticated.



### Action



Execute logout.



### Expected Result



- active authenticated session is invalidated;

- protected authenticated resources require authentication again;

- logout does not leave stale authenticated state.



### Automated Evidence



Expected supporting test:



```text

user can logout and session is invalidated

```



### Result



```text

PASS

```



---
## 21. TC-AUTH-008 — Reject Protected Request Without Authentication



### Priority



```text

P1

```



### Requirement Basis



```text

FR-010 – FR-012

FR-061 – FR-066

```



### Preconditions



No authenticated session exists.



### Action



Request an authenticated-only API resource.



### Expected Result



The request is rejected according to the approved authentication behavior.



No protected user information is returned.



### Automated Evidence



Expected supporting test:



```text

unauthenticated user cannot view profile

```



### Result



```text

PASS

```



---
## 22. TC-AUTH-009 — CSRF Protection for Session-Based State Change



### Priority



```text

P1

```



### Requirement Basis



```text

NFR-001 – NFR-018

NFR-019 – NFR-022

```



### Preconditions



A session-based request context exists.



### Action



Submit an applicable state-changing request without valid CSRF protection.



### Expected Result



- request is rejected;

- application state is unchanged;

- request with valid CSRF protection is accepted according to application rules.



### Automated Evidence



Expected supporting tests:



```text

state changing session request without csrf token is rejected

state changing session request with valid csrf token is accepted

csrf bootstrap returns session csrf token

```



### Result



```text

PASS

```



---
## 23. TC-AUTH-010 — Authenticated Session Persists Across Valid Requests



### Priority



```text

P2

```



### Requirement Basis



```text

FR-005 – FR-012

```



### Preconditions



A valid login has completed.



### Action



Perform multiple authenticated requests within the active session.



### Expected Result



- authenticated identity remains consistent;

- session remains valid until logout, invalidation, or expiration;

- protected requests continue to recognize the authenticated user.



### Automated Evidence



Expected supporting behavior:



```text

user can login and session is established

authenticated user can view profile

user can logout and session is invalidated

```



### Result



```text

PASS

```



---
## 24. Registration Validation Coverage



Supporting automated authentication tests are expected to verify invalid registration input in addition to the ten formal authentication scenarios.



Expected supporting test:



```text

registration requires valid data

```



The supporting test strengthens:



```text

TC-AUTH-001

TC-AUTH-002

TC-AUTH-003

```



Failure of registration validation is treated as an authentication-family defect even where the supporting automated test is not itself assigned a separate TC-AUTH identifier.



---
## 25. Session Establishment Verification



Successful registration and login must establish authenticated state only after valid authentication behavior completes.



Validation includes:



- authenticated identity exists after successful registration where defined by implementation;

- authenticated identity exists after successful login;

- authentication is unavailable after failed login;

- authentication is unavailable for unauthenticated requests;

- logout invalidates the active session.



---
## 26. Session Persistence Verification



Session persistence is considered valid when:



1\. authentication succeeds;

2\. a subsequent protected request recognizes the same authenticated identity;

3\. no reauthentication is required during the valid session;

4\. logout invalidates that session;

5\. subsequent protected access requires authentication again.



The test does not require validation of arbitrary long-duration session expiration behavior unless explicitly required by the approved V1.0.0 requirements.



---
## 27. Password Security Verification



WP-04 must not expose or persist plaintext passwords in test evidence.



Registration and login tests must preserve the following security expectations:



- stored password representation is not plaintext;

- failed password authentication does not create a session;

- password values are not included in committed evidence;

- authentication logs must not intentionally expose passwords.



Detailed application logging security is tested further in WP-14 and WP-15.



---
## 28. Authentication Failure Behavior



Authentication failures must fail safely.



Required behavior includes:



- no authenticated session after failure;

- no protected resource access;

- no plaintext credential disclosure;

- controlled validation or authentication response;

- no unexpected application exception.



Where account-enumeration resistance is required by approved security behavior, failure responses must avoid unnecessary disclosure of account existence.



---
## 29. CSRF Verification



GitForge uses session-based authentication for web/API session flows.



State-changing session requests must therefore enforce the application's CSRF protection rules.



WP-04 verifies:



- CSRF bootstrap behavior;

- rejection without valid CSRF protection;

- acceptance with valid CSRF protection.



CSRF verification must not be confused with personal access token authentication, which is tested separately.



---
## 30. Authentication Test Isolation



Automated authentication tests must isolate database and session state.



Test execution must not depend on:



- pre-existing personal user accounts;

- production credentials;

- manually created persistent sessions;

- unrelated repository records.



Laravel test infrastructure and project fixtures should provide controlled state for each relevant test.



---
## 31. Expected Automated Test Set



The currently implemented authentication feature test suite is expected to include tests equivalent to:



```text

csrf bootstrap returns session csrf token

user can register and session is established

registration requires valid data

username must be unique

email must be unique

user can login and session is established

login rejects invalid credentials

login rejects unknown user

authenticated user can view profile

unauthenticated user cannot view profile

user can logout and session is invalidated

```



The dedicated CSRF security suite is expected to include tests equivalent to:



```text

state changing session request without csrf token is rejected

state changing session request with valid csrf token is accepted

```



The exact PHPUnit output generated during WP-04 execution is the authoritative fresh evidence.



---
## 32. Defect Handling



If an authentication test fails:



1\. preserve the failed test identifier;

2\. preserve the relevant test output;

3\. identify expected behavior;

4\. identify actual behavior;

5\. determine affected requirement identifiers;

6\. classify severity;

7\. correct implementation or test infrastructure as appropriate;

8\. rerun the failed test;

9\. rerun related authentication tests;

10\. preserve the final result.



A failing authentication test must not be changed merely to force a passing result when the implementation violates an approved requirement.



---
## 33. Severity Guidance



Authentication defects should generally be classified using the following guidance.



| Severity | Example |
|---|---|
| Critical | Authentication bypass or protected resource accessible without required authentication |
| High | Valid logout fails to invalidate session; CSRF protection absent from required state-changing operation |
| Medium | Valid registration/login behavior fails without security bypass |
| Low | Non-security-impacting authentication response inconsistency |



Final severity depends on actual impact.



---
## 34. Security Escalation Rule



Any evidence of the following must be escalated into WP-14 Security Testing:



- authentication bypass;

- session fixation;

- CSRF bypass;

- credential disclosure;

- password exposure;

- unauthorized protected-resource access;

- unsafe session persistence;

- account-enumeration behavior that violates approved requirements.



WP-04 may not be approved with an unresolved authentication-related release-blocking security defect.



---
## 35. Evidence Requirements



WP-04 evidence must include:



- executed command;

- PHPUnit test-class results;

- number of tests executed;

- pass/fail status;

- relevant assertion count when reported;

- mapping to `TC-AUTH-001` through `TC-AUTH-010`;

- defects if any;

- final work-package decision.



Sensitive credentials must not be copied into this document.



---
## 36. Execution Record



Primary authentication test command:



```powershell

php artisan test --filter=AuthenticationTest

```



Execution status:



```text

PASS

```



Primary CSRF security test command:



```powershell

php artisan test --filter=CsrfSecurityTest

```



Execution status:



```text

PASS

```



---
## 37. Formal Test Result Summary



| Test ID | Priority | Result |
|---|---|---|
| TC-AUTH-001 | P2 | PASS |
| TC-AUTH-002 | P2 | PASS |
| TC-AUTH-003 | P2 | PASS |
| TC-AUTH-004 | P1 | PASS |
| TC-AUTH-005 | P1 | PASS |
| TC-AUTH-006 | P1 | PASS |
| TC-AUTH-007 | P2 | PASS |
| TC-AUTH-008 | P1 | PASS |
| TC-AUTH-009 | P1 | PASS |
| TC-AUTH-010 | P2 | PASS |



Summary:



```text

PASS: 10

FAIL: 0

BLOCKED: 0

NOT EXECUTED: 0

TOTAL: 10

```



---
## 38. Supporting Automated Test Result Summary



| Test group | Result |
|---|---|
| Authentication feature tests | PASS — 11 tests, 51 assertions |
| CSRF security tests | PASS — 2 tests, 3 assertions |



Supporting evidence is not complete until fresh WP-04 commands have been executed.



---
## 39. Requirement Coverage



WP-04 primarily contributes verification evidence for:



```text

FR-001 – FR-012

FR-061 – FR-066

FR-075 – FR-078

NFR-001 – NFR-018

NFR-019 – NFR-022

UC-001 – UC-003

```



A requirement is not considered fully verified solely because it appears in this work package.



Final requirement-to-test verification is consolidated by:



```text

WP-19 — Test Traceability

```



---
## 40. Regression Relationship



WP-04 produces authentication-family evidence that will later contribute to:



```text

WP-17 — Regression Testing

```



Authentication tests that pass during WP-04 must remain passing after later M5 testing activities and any defect corrections.



---
## 41. WP-04 Exit Criteria



WP-04 is complete only when:



- all ten formal authentication test cases have been executed;

- all P1 authentication tests pass;

- all P2 authentication tests pass or have an explicitly approved non-blocking disposition;

- authentication feature tests pass;

- CSRF security tests pass;

- valid registration is verified;

- registration uniqueness is verified;

- valid login is verified;

- invalid login is rejected;

- unauthenticated protected access is rejected;

- logout invalidates authenticated session state;

- session persistence is verified;

- CSRF behavior is verified;

- no unresolved release-blocking authentication defect remains;

- test evidence is recorded;

- result summary is updated;

- WP-04 is approved.



---
## 42. WP-04 Approval Rule



WP-04 may be approved only after fresh execution evidence demonstrates that the authentication test family satisfies its expected behavior.



Historical M4 test results may provide context but do not replace fresh WP-04 execution.



Possible decisions:



```text

APPROVED

BLOCKED

REQUIRES CORRECTION

```



---
## 43. Current WP-04 Status



Current state:



```text

WP-04 document created

Authentication scope defined

10 formal authentication test cases identified

Execution commands defined

Fresh authentication execution completed successfully

Status: APPROVED

```



Next:



- authentication feature tests executed successfully;

- CSRF security tests executed successfully;

- TC-AUTH-001 through TC-AUTH-010 evaluated as PASS;

- fresh evidence recorded;

- no blocking authentication defect remains;

- WP-04 exit criteria verified;

- WP-04 approved;

- begin WP-05 Repository Metadata Testing.



---
## 44. Conclusion



WP-04 defines the formal authentication-testing execution for GitForge V1.0.0.



The work package verifies the authentication boundary through:



- valid registration;

- duplicate registration rejection;

- valid login;

- invalid-login rejection;

- authenticated-session establishment;

- authenticated profile access;

- unauthenticated protected-resource rejection;

- logout and session invalidation;

- CSRF enforcement;

- authenticated-session continuity.



Current WP-04 decision:



```text

APPROVED

```
