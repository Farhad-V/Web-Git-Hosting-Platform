# Acceptance Testing



| Field | Value |

| --- | --- |

| Project | Git Hosting Platform |

| Version | V1.0.0 |

| Milestone | M5 — Testing |

| SDLC Phase | Testing |

| Work Package | WP-18 — Acceptance Testing |

| Document Status | Approved |

| Test Date | 2026-08-23 |



## 1. Purpose



This document records the formal acceptance testing results for Git Hosting Platform V1.0.0.



The objective of WP-18 is to validate the implemented V1.0.0 system against the approved acceptance outcomes defined in the formal test-case specification.



Acceptance testing focuses on externally meaningful and business-visible behavior rather than internal implementation detail.



The acceptance basis consists of the ten formal acceptance cases:



- TC-ACC-001 through TC-ACC-010.



## 2. Acceptance Test Summary



| Test Case | Acceptance Outcome | Result |

| --- | --- | --- |

| TC-ACC-001 | User can establish an account and authenticate | PASS |

| TC-ACC-002 | User can create and access repository | PASS |

| TC-ACC-003 | Public repository supports intended public read | PASS |

| TC-ACC-004 | Private repository remains protected | PASS |

| TC-ACC-005 | User can create and revoke PAT | PASS |

| TC-ACC-006 | Valid Git clone and fetch work | PASS |

| TC-ACC-007 | Valid Git push works | PASS |

| TC-ACC-008 | Repository can be browsed through web application | PASS |

| TC-ACC-009 | GitForge rejects unauthorized Git write | PASS |

| TC-ACC-010 | GitForge is operationally observable | PASS |



All ten formal acceptance cases passed.



## 3. TC-ACC-001 — User Can Establish an Account and Authenticate



### Acceptance Basis



Identity and authentication user stories.



### Expected Result



Registration, login, authenticated use, and logout operate as approved.



### Verification



The following backend authentication flows were executed:



- user registration with session establishment;

- valid login with session establishment;

- authenticated profile access;

- logout with session invalidation.



### Result



PASS



### Evidence



- Tests passed: 4

- Assertions: 24

- Failures: 0

- Duration: 1.90 seconds



The executed flows demonstrated that a user can establish an account, authenticate, access an authenticated endpoint, and terminate the authenticated session successfully.



## 4. TC-ACC-002 — User Can Create and Access Repository



### Acceptance Basis



Repository creation and retrieval user stories.



### Expected Result



An authenticated user can create a repository and access its supported metadata.



### Verification



The following repository flows were executed:



- authenticated repository creation;

- authenticated retrieval of public repository metadata.



### Result



PASS



### Evidence



- Tests passed: 2

- Assertions: 8

- Failures: 0

- Duration: 1.48 seconds



The accepted repository creation and metadata-access behavior is available to authenticated users.



## 5. TC-ACC-003 — Public Repository Supports Intended Public Read



### Acceptance Basis



Public repository and read user stories.



### Expected Result



A public repository can be browsed and cloned through supported public interfaces.



### Verification



The following public-read flows were executed:



- authenticated browsing of a public repository;

- real Git client clone of a public repository.



### Result



PASS



### Evidence



- Tests passed: 2

- Assertions: 8

- Failures: 0

- Duration: 9.86 seconds



Public repository content is available through the supported repository-browser and Git Smart HTTP read interfaces.



## 6. TC-ACC-004 — Private Repository Remains Protected



### Acceptance Basis



Authorization user stories.



### Expected Result



Unauthorized actors cannot retrieve protected private repository content.



### Verification



The following private-repository protection flows were executed:



- authenticated non-owner cannot view private repository metadata;

- anonymous visitor cannot view private repository metadata;

- anonymous visitor cannot browse private repository content.



The repository-browser protection check covers private repository branches, tree, commits, commit details, and blob access through the relevant protected endpoints.



### Result



PASS



### Evidence



- Tests passed: 3

- Assertions: 7

- Failures: 0

- Duration: 3.54 seconds



Private repository information remains concealed from unauthorized actors.



## 7. TC-ACC-005 — User Can Create and Revoke PAT



### Acceptance Basis



Personal Access Token user stories.



### Expected Result



A user can create a usable Personal Access Token and revoke it successfully.



### Verification



The following token-management flows were executed:



- authenticated user creates a Personal Access Token;

- token owner revokes the owned Personal Access Token.



### Result



PASS



### Evidence



- Tests passed: 2

- Assertions: 22

- Failures: 0

- Duration: 0.46 seconds



Personal Access Token creation and revocation operate according to the approved V1.0.0 behavior.



## 8. TC-ACC-006 — Valid Git Clone and Fetch Work



### Acceptance Basis



Git Smart HTTP read user stories.



### Expected Result



A real Git client can clone and fetch a supported repository.



### Verification



The following real Git client operations were executed:



- clone of a public repository;

- fetch of a newly created public commit.



The fetch verification confirmed that the remote branch advanced to the newly created commit and that the fetched object content was readable from the remote-tracking reference.



### Result



PASS



### Evidence



- Tests passed: 2

- Assertions: 12

- Failures: 0

- Duration: 13.08 seconds



Supported Git Smart HTTP read operations work with a real Git client.



## 9. TC-ACC-007 — Valid Git Push Works



### Acceptance Basis



Git Smart HTTP write user stories.



### Expected Result



An authorized repository owner can push using a valid active Personal Access Token.



### Verification



A real Git client performed a push of a new commit using valid owner credentials and an active PAT.



### Result



PASS



### Evidence



- Tests passed: 1

- Assertions: 9

- Failures: 0

- Duration: 7.57 seconds



The authorized Git Smart HTTP write flow succeeds as required.



## 10. TC-ACC-008 — Repository Can Be Browsed Through Web Application



### Acceptance Basis



Repository browser user stories.



### Expected Result



Required tree, file, commit-list, and commit-detail behavior works.



### Verification



The following repository-browser behaviors were executed:



- repository tree browsing;

- repository blob/file reading;

- repository commit listing;

- repository commit-detail retrieval.



### Result



PASS



### Evidence



- Tests passed: 4

- Assertions: 33

- Failures: 0

- Duration: 15.35 seconds



All required V1.0.0 repository-browser read capabilities are available.



## 11. TC-ACC-009 — GitForge Rejects Unauthorized Git Write



### Acceptance Basis



Authorization and Git write-security requirements.



### Expected Result



An unauthorized push cannot modify the repository.



### Verification



The following real Git client rejection flows were executed:



- push without credentials;

- push by an authenticated non-owner.



The existing write end-to-end tests verify rejection and preservation of repository references after unauthorized push attempts.



### Result



PASS



### Evidence



- Tests passed: 2

- Assertions: 12

- Failures: 0

- Duration: 12.66 seconds



Unauthorized Git write operations are rejected and do not modify repository state.



## 12. TC-ACC-010 — GitForge Is Operationally Observable



### Acceptance Basis



FR-096 through FR-098 and the approved observability requirements.



### Expected Result



Required health, readiness, and applicable observability behavior are available.



### Verification



The following operational behaviors were executed:



- liveness endpoint remains available;

- readiness endpoint returns the expected minimal healthy response when dependencies are ready;

- requests receive a server-generated correlation identifier;

- request-start and request-completion observability events use the same request identifier.



### Result



PASS



### Evidence



- Tests passed: 4

- Assertions: 14

- Failures: 0

- Duration: 0.77 seconds



The required operational health, readiness, request-correlation, and structured request-observability behavior is available.



## 13. Aggregate Acceptance Evidence



The WP-18 targeted acceptance executions produced:



- Acceptance cases passed: 10 of 10

- Targeted tests passed: 26

- Assertions: 149

- Failures: 0

- Blocked acceptance cases: 0

- Failed acceptance cases: 0

- Aggregate targeted execution duration: approximately 66.67 seconds



No acceptance-specific production-code changes were required during WP-18.



## 14. Acceptance Assessment



The acceptance evidence demonstrates that Git Hosting Platform V1.0.0 satisfies the defined business-visible outcomes for:



- identity and authentication;

- repository creation and access;

- public repository reading;

- private repository protection;

- Personal Access Token lifecycle;

- Git clone;

- Git fetch;

- Git push;

- repository browsing;

- unauthorized Git write rejection;

- health and operational observability.



No unresolved acceptance failure was identified.



## 15. Work Package Result



WP-18 — Acceptance Testing is complete.



All ten formal acceptance cases defined for V1.0.0 have passed.



The system may proceed to WP-19 — Test Traceability.



## 16. Approval



| Role | Decision | Date |

| --- | --- | --- |

| Project owner | Approved | 2026-08-23 |



**Final Status: Approved**
