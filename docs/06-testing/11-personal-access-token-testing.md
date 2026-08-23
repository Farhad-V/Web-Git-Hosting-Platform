# Personal Access Token Testing



| Field | Value |
|---|---|
| Project | GitForge — Web Git Hosting Platform |
| Version | V1.0.0 |
| SDLC phase | 6 — Testing |
| Milestone | M5 — Testing |
| Work package | WP-11 — Personal Access Token Testing |
| Document | Personal Access Token Testing |
| Status | Approved |
| Date | 2026-08-22 |
| Branch | `testing/m5` |



---



## 1. Purpose



This document records the formal execution and results of Personal Access Token testing for GitForge V1.0.0.



WP-11 verifies the complete PAT lifecycle and its use as the Git Smart HTTP authentication credential.



The work package covers:



- authenticated PAT creation;

- unauthenticated PAT creation rejection;

- secure PAT persistence;

- PAT metadata listing;

- cross-user PAT isolation;

- PAT revocation;

- malformed credential rejection;

- active PAT identity resolution;

- repository authorization separation from token validity.



The formal Personal Access Token test identifiers are:



```text

TC-PAT-001

TC-PAT-002

TC-PAT-003

TC-PAT-004

TC-PAT-005

TC-PAT-006

TC-PAT-007

TC-PAT-008

TC-PAT-009

```



---



## 2. Objectives



WP-11 verifies that:



- an authenticated user can create a PAT;

- the raw PAT is returned only at creation according to approved behavior;

- unauthenticated clients cannot manage PATs;

- the raw PAT secret is not stored in plaintext;

- stored token data uses a one-way representation;

- users can list their own PAT metadata;

- raw token values are not exposed in PAT listing responses;

- one user cannot access another user's PAT metadata;

- users can revoke tokens they own;

- revoked tokens can no longer authenticate Git requests;

- malformed PAT credentials fail safely;

- valid active PATs resolve the correct owning user identity;

- PAT validity alone does not grant repository authority;

- a valid PAT owned by a non-owner cannot perform an owner-only Git operation.



---



## 3. Test Basis



The primary formal test basis is:



```text

docs/06-testing/02-test-case-specification.md

```



Formal test cases:



```text

TC-PAT-001 — Create PAT as Authenticated User

TC-PAT-002 — Reject PAT Creation Without Authentication

TC-PAT-003 — Raw PAT Is Not Persisted as Plaintext

TC-PAT-004 — List Current User PATs

TC-PAT-005 — User Cannot List Another User's PATs

TC-PAT-006 — Revoke Owned PAT

TC-PAT-007 — Reject Malformed Token

TC-PAT-008 — Valid Active PAT Resolves Correct Owner Identity

TC-PAT-009 — Valid PAT Does Not Elevate Repository Authority

```



Supporting prior M5 evidence includes:



```text

WP-04 — Authentication Testing

WP-06 — Authorization Testing

WP-10 — Git Smart HTTP Read Testing

```



---



## 4. Scope



### 4.1 In Scope



WP-11 covers:



- PAT API creation behavior;

- PAT API listing behavior;

- PAT revocation behavior;

- PAT validation;

- PAT persistence;

- PAT hashing;

- PAT uniqueness;

- malformed PAT rejection;

- unknown token identifier rejection;

- wrong token secret rejection;

- revoked PAT rejection;

- identity resolution from active PAT;

- Git Smart HTTP Basic authentication using PAT;

- authorization separation between token validity and repository ownership.



### 4.2 Out of Scope



The following are outside WP-11:



- repository creation;

- repository deletion;

- Git repository browsing;

- Git write transport as a full work package;

- frontend PAT management UI;

- SSH keys;

- OAuth;

- PAT scopes;

- PAT permissions by repository;

- organization-level PATs;

- expiring PATs unless explicitly implemented;

- deployment secret management.



PAT read/write scopes are not part of the approved V1.0.0 requirements.



---



## 5. Primary Test Files



Dedicated PAT feature test:



```text

backend/tests/Feature/Api/V1/PersonalAccessTokenTest.php

```



Dedicated PAT service unit test:



```text

backend/tests/Unit/Services/Auth/PersonalAccessTokenServiceTest.php

```



Supporting Git authorization test:



```text

backend/tests/Feature/Git/GitSmartHttpTest.php

```



Supporting real-client Git evidence from WP-10:



```text

backend/tests/Feature/Git/GitSmartHttpReadEndToEndTest.php

```



---



## 6. Existing PAT Feature Tests



The PAT feature suite contains:



```text

test\_authenticated\_user\_can\_create\_personal\_access\_token

test\_plaintext\_token\_is\_not\_exposed\_when\_listing\_tokens

test\_user\_lists\_only\_their\_own\_tokens

test\_token\_creation\_requires\_valid\_name

test\_token\_name\_is\_trimmed

test\_user\_can\_revoke\_owned\_token

test\_revocation\_is\_idempotent

test\_user\_cannot\_revoke\_another\_users\_token

test\_unauthenticated\_user\_cannot\_manage\_tokens

```



These tests cover the HTTP-facing PAT lifecycle.



---



## 7. Existing PAT Service Tests



The PAT service unit suite contains:



```text

test\_it\_issues\_token\_using\_v1\_format\_and\_persists\_only\_hash

test\_it\_resolves\_valid\_active\_token

test\_it\_rejects\_malformed\_token

test\_it\_rejects\_unknown\_token\_identifier

test\_it\_rejects\_token\_with\_wrong\_secret

test\_it\_rejects\_revoked\_token

test\_issued\_tokens\_are\_unique

```



These tests cover PAT issuance, storage, validation, and identity resolution.



---



## 8. Authentication Model



PAT authentication uses a Git-compatible Basic authorization header.



Conceptually:



```text

username:PAT

```



is encoded as:



```text

Authorization: Basic <base64(username:PAT)>

```



The PAT establishes application identity.



Repository authorization is evaluated separately.



---



## 9. Authorization Separation



The approved V1.0.0 model does not treat possession of a valid PAT as repository permission.



The authorization sequence is:



```text

PAT presented

&#x20;   |

&#x20;   v

PAT validated

&#x20;   |

&#x20;   v

User identity resolved

&#x20;   |

&#x20;   v

Repository authorization evaluated

&#x20;   |

&#x20;   +--> authorized -> operation allowed

&#x20;   |

&#x20;   +--> unauthorized -> operation rejected

```



This separation is central to `TC-PAT-009`.



---



## 10. TC-PAT-001 — Create PAT as Authenticated User



### Objective



Verify that an authenticated user can create a PAT with a valid name.



### Evidence



Feature test:



```text

test\_authenticated\_user\_can\_create\_personal\_access\_token

```



Supporting service test:



```text

test\_it\_issues\_token\_using\_v1\_format\_and\_persists\_only\_hash

```



The tests verify PAT issuance and persistence.



### Result



PASS.



---



## 11. TC-PAT-002 — Reject PAT Creation Without Authentication



### Objective



Verify that an unauthenticated client cannot create or manage PATs.



### Evidence



Feature test:



```text

test\_unauthenticated\_user\_cannot\_manage\_tokens

```



### Result



PASS.



---



## 12. TC-PAT-003 — Raw PAT Is Not Persisted as Plaintext



### Objective



Verify that the recoverable raw PAT secret is not stored in plaintext.



### Evidence



Service test:



```text

test\_it\_issues\_token\_using\_v1\_format\_and\_persists\_only\_hash

```



The test verifies that the issued plaintext token is distinct from the persisted representation.



### Result



PASS.



---



## 13. TC-PAT-004 — List Current User PATs



### Objective



Verify that the authenticated user can list their PAT metadata without exposing raw token secrets.



### Evidence



Feature tests:



```text

test\_plaintext\_token\_is\_not\_exposed\_when\_listing\_tokens

test\_user\_lists\_only\_their\_own\_tokens

```



### Result



PASS.



---



## 14. TC-PAT-005 — User Cannot List Another User's PATs



### Objective



Verify that token metadata belonging to another user is not disclosed.



### Evidence



Feature test:



```text

test\_user\_lists\_only\_their\_own\_tokens

```



The test establishes multiple users and verifies PAT listing isolation.



### Result



PASS.



---



## 15. TC-PAT-006 — Revoke Owned PAT



### Objective



Verify that a user can revoke a PAT they own and that the revoked token can no longer authenticate.



### Evidence



Feature test:



```text

test\_user\_can\_revoke\_owned\_token

```



Supporting tests:



```text

test\_revocation\_is\_idempotent

test\_it\_rejects\_revoked\_token

```



Additional Git transport evidence from WP-10 confirms revoked PAT credentials fail on private repository read transport.



### Result



PASS.



---



## 16. TC-PAT-007 — Reject Malformed Token



### Objective



Verify that malformed token values fail authentication safely.



### Evidence



Service test:



```text

test\_it\_rejects\_malformed\_token

```



Additional negative tests:



```text

test\_it\_rejects\_unknown\_token\_identifier

test\_it\_rejects\_token\_with\_wrong\_secret

```



### Result



PASS.



---



## 17. TC-PAT-008 — Valid Active PAT Resolves Correct Owner Identity



### Objective



Verify that a valid active PAT resolves to the correct owning user identity.



### Evidence



Service test:



```text

test\_it\_resolves\_valid\_active\_token

```



Supporting Git authentication tests use PAT credentials associated with explicit users.



### Result



PASS.



---



## 18. TC-PAT-009 — Valid PAT Does Not Elevate Repository Authority



### Objective



Verify that a valid PAT belonging to a non-owner does not grant owner-level repository permissions.



### Evidence



Git feature tests:



```text

test\_non\_owner\_cannot\_advertise\_receive\_pack

test\_non\_owner\_cannot\_execute\_receive\_pack\_rpc

```



The helper used by these tests:



```text

basicAuthHeaders(User $user)

```



creates a real PAT for the supplied user and constructs a Git Basic authorization header.



The non-owner therefore authenticates with a valid PAT.



The request targets a repository owned by another user.



Expected result:



```text

403 Forbidden

```



The receive-pack RPC test additionally snapshots repository refs before the request and verifies that refs remain unchanged afterward.



### Result



PASS.



This directly proves that PAT validity establishes identity but does not elevate repository authority.



---



## 19. Supporting Cross-User Revocation Protection



Feature test:



```text

test\_user\_cannot\_revoke\_another\_users\_token

```



This verifies that one authenticated user cannot revoke a PAT owned by another user.



Result:



PASS.



This is supporting evidence for PAT ownership isolation.



---



## 20. Supporting PAT Name Validation



Feature test:



```text

test\_token\_creation\_requires\_valid\_name

```



Result:



PASS.



The API rejects invalid PAT creation input.



---



## 21. Supporting PAT Name Normalization



Feature test:



```text

test\_token\_name\_is\_trimmed

```



Result:



PASS.



This verifies normalized PAT metadata persistence.



---



## 22. Supporting Revocation Idempotency



Feature test:



```text

test\_revocation\_is\_idempotent

```



Result:



PASS.



Repeated revocation of an already revoked owned PAT remains controlled and safe.



---



## 23. Supporting Unknown Identifier Rejection



Service test:



```text

test\_it\_rejects\_unknown\_token\_identifier

```



Result:



PASS.



A structurally valid token referencing a non-existent identifier cannot authenticate.



---



## 24. Supporting Wrong Secret Rejection



Service test:



```text

test\_it\_rejects\_token\_with\_wrong\_secret

```



Result:



PASS.



Possession of a valid token identifier without the correct secret is insufficient for authentication.



---



## 25. Supporting Token Uniqueness



Service test:



```text

test\_issued\_tokens\_are\_unique

```



Result:



PASS.



Separate token issuance operations produce distinct credentials.



---



## 26. Dedicated PAT Test Execution



Command executed:



```powershell

php artisan test `

&#x20;   tests\\Feature\\Api\\V1\\PersonalAccessTokenTest.php `

&#x20;   tests\\Unit\\Services\\Auth\\PersonalAccessTokenServiceTest.php

```



Result:



```text

PASS  Tests\\Feature\\Api\\V1\\PersonalAccessTokenTest

PASS  Tests\\Unit\\Services\\Auth\\PersonalAccessTokenServiceTest



Tests:    16 passed (62 assertions)

Duration: 0.91s

```



Aggregate dedicated PAT result:



```text

16 passed

62 assertions

0 failed

```



---



## 27. TC-PAT-009 Targeted Execution



Command executed:



```powershell

php artisan test `

&#x20;   tests\\Feature\\Git\\GitSmartHttpTest.php `

&#x20;   --filter="non\_owner\_cannot\_(advertise|execute)\_receive\_pack"

```



Result:



```text

PASS  Tests\\Feature\\Git\\GitSmartHttpTest



✓ non owner cannot advertise receive pack

✓ non owner cannot execute receive pack rpc



Tests:    2 passed (3 assertions)

Duration: 3.14s

```



Targeted authorization result:



```text

2 passed

3 assertions

0 failed

```



---



## 28. Code Style Verification



Command executed:



```powershell

vendor\\bin\\pint --test `

&#x20;   tests\\Feature\\Api\\V1\\PersonalAccessTokenTest.php `

&#x20;   tests\\Unit\\Services\\Auth\\PersonalAccessTokenServiceTest.php `

&#x20;   tests\\Feature\\Git\\GitSmartHttpTest.php

```



Result:



```text

PASS

3 files

```



No Pint issue remains in the WP-11 evidence set.



---



## 29. Coverage Matrix



| Test case | Primary evidence | Result |
|---|---|---|
| TC-PAT-001 | PAT feature + PAT service issuance tests | PASS |
| TC-PAT-002 | unauthenticated PAT management test | PASS |
| TC-PAT-003 | persisted hash test | PASS |
| TC-PAT-004 | PAT listing tests | PASS |
| TC-PAT-005 | current-user-only PAT listing test | PASS |
| TC-PAT-006 | revoke + revoked-token rejection tests | PASS |
| TC-PAT-007 | malformed-token rejection test | PASS |
| TC-PAT-008 | active-token identity resolution test | PASS |
| TC-PAT-009 | valid non-owner PAT receive-pack denial tests | PASS |



Coverage result:



```text

9 / 9 formal WP-11 test cases covered

```



---



## 30. Security Assessment



WP-11 confirms that:



- raw PAT secrets are not persisted in plaintext;

- raw token values are not disclosed in PAT listing responses;

- unauthenticated users cannot manage PATs;

- PAT lists are user-isolated;

- cross-user revocation is rejected;

- malformed PATs fail safely;

- unknown token identifiers fail safely;

- wrong token secrets fail safely;

- revoked tokens fail authentication;

- valid active tokens resolve their correct user identity;

- valid authentication does not bypass repository authorization.



No unresolved PAT security defect was identified.



---



## 31. Identity and Authorization Assessment



The PAT system correctly separates authentication from authorization.



Authentication answers:



```text

Who is this Git client?

```



Repository authorization answers:



```text

May this authenticated user perform this repository operation?

```



`TC-PAT-009` confirms that these two decisions are not conflated.



A valid PAT owned by `other-user` successfully represents `other-user`, but an owner-only receive-pack operation against another user's repository is still denied.



Result:



PASS.



---



## 32. Persistence Assessment



The PAT persistence model stores a non-recoverable token representation rather than the plaintext secret.



This reduces the impact of database disclosure because the application does not need to retain the original raw PAT value.



Result:



PASS.



---



## 33. Revocation Assessment



Revocation behavior is verified at both the management and authentication layers.



Management evidence:



```text

test\_user\_can\_revoke\_owned\_token

test\_revocation\_is\_idempotent

```



Authentication evidence:



```text

test\_it\_rejects\_revoked\_token

```



Supporting Smart HTTP evidence also verifies revoked credentials cannot access protected Git read transport.



Result:



PASS.



---



## 34. Negative Authentication Assessment



The following invalid credential forms are covered:



```text

malformed token

unknown token identifier

wrong token secret

revoked token

invalid Git PAT

missing Git credentials

```



All fail safely.



Result:



PASS.



---



## 35. Production Code Changes



No production code changes were required during WP-11.



The existing PAT implementation satisfied the approved formal test cases.



---



## 36. Test Code Changes



No new test code was required during WP-11.



Existing automated tests already provided complete coverage for all nine formal PAT cases.



WP-11 therefore consisted of:



- coverage analysis;

- fresh execution;

- targeted authorization verification;

- code-style verification;

- documentation.



---



## 37. Defect Assessment



No production PAT defect was discovered during WP-11.



No unresolved test coverage gap was discovered.



Defect classification:



```text

Production defect: none

Test coverage gap: none

Test harness defect: none

```



---



## 38. Formal Test Result Summary



| Test ID | Result |
|---|---|
| TC-PAT-001 | PASS |
| TC-PAT-002 | PASS |
| TC-PAT-003 | PASS |
| TC-PAT-004 | PASS |
| TC-PAT-005 | PASS |
| TC-PAT-006 | PASS |
| TC-PAT-007 | PASS |
| TC-PAT-008 | PASS |
| TC-PAT-009 | PASS |



Formal result:



```text

9 PASS

0 FAIL

0 BLOCKED

0 NOT EXECUTED

```



---



## 39. Exit Criteria



WP-11 exit criteria are:



- all nine formal `TC-PAT-*` cases are mapped to executable evidence;

- authenticated PAT creation is verified;

- unauthenticated PAT management rejection is verified;

- raw PAT plaintext persistence is rejected;

- PAT listing is verified;

- PAT list isolation is verified;

- owned PAT revocation is verified;

- revoked token rejection is verified;

- malformed PAT rejection is verified;

- valid active PAT identity resolution is verified;

- PAT validity is shown not to elevate repository authority;

- dedicated PAT suites pass;

- targeted authorization tests pass;

- relevant Pint checks pass;

- no unresolved production defect remains;

- no formal coverage gap remains;

- results are documented.



Exit criteria status:



```text

SATISFIED

```



---



## 40. Evidence Summary



Executed WP-11 evidence confirms that:



- authenticated users can create PATs;

- raw PATs are returned according to creation behavior;

- persisted PAT data uses a hash rather than plaintext;

- PAT list responses do not expose raw secrets;

- users see only their own PAT metadata;

- invalid PAT creation input is rejected;

- PAT names are normalized;

- users can revoke their own PATs;

- revocation is idempotent;

- users cannot revoke another user's PAT;

- unauthenticated users cannot manage PATs;

- valid active PATs resolve the correct user;

- malformed PATs are rejected;

- unknown token identifiers are rejected;

- incorrect token secrets are rejected;

- revoked tokens are rejected;

- issued tokens are unique;

- a valid PAT belonging to a non-owner cannot perform owner-only repository operations;

- rejected non-owner write operations leave repository refs unchanged;

- no production PAT defect remains unresolved.



---



## 41. WP-11 Result



WP-11 execution result:



```text

PASS

```



Formal PAT cases:



```text

9 / 9 PASS

```



Dedicated PAT execution:



```text

16 passed

62 assertions

0 failures

```



Targeted repository-authorization execution:



```text

2 passed

3 assertions

0 failures

```



Code style:



```text

Pint PASS

3 files

```



Production changes:



```text

None

```



Test-code changes:



```text

None

```



Unresolved defects:



```text

None

```



---



## 42. Conclusion



Personal Access Token Testing for GitForge V1.0.0 has been executed successfully.



All nine formal `TC-PAT-*` cases have passing executable evidence.



The PAT implementation correctly supports issuance, secure persistence, metadata listing, ownership isolation, revocation, malformed credential rejection, active-token identity resolution, and Git authentication.



The testing also confirms the critical security boundary that token validity does not grant repository authority. A valid PAT authenticates its owning user, but repository authorization remains independently enforced.



No production code or test code modification was required during WP-11.



No unresolved PAT defect or formal test coverage gap remains.



WP-11 satisfies its defined testing objectives and exit criteria and is ready for document review and approval.
