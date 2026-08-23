\# Test Traceability



| Field | Value |

|---|---|

| Project | Git Hosting Platform |

| Version | V1.0.0 |

| Milestone | M5 — Testing |

| SDLC Phase | Testing |

| Work Package | WP-19 — Test Traceability |

| Document | Test Traceability |

| Status | Approved |

| Date | 2026-08-23 |



\---



\## 1. Purpose



This document establishes traceability between the approved V1.0.0 requirements, the formal test-case specification, implemented automated tests, executed verification activities, and recorded testing evidence.



The purpose of WP-19 is to verify that:



1\. formal V1.0.0 requirements have an identified verification path;

2\. formal test cases have corresponding execution evidence;

3\. database and performance cases are explicitly accounted for;

4\. requirements that are not appropriately verified by an automated runtime test are linked to their prescribed review, deployment, environment, security, or operational verification mechanism;

5\. no formal test case remains without an execution-evidence path before Testing Validation begins.



\---



\## 2. Traceability Sources



The traceability review uses the following approved or completed artifacts:



\- `docs/02-requirements-engineering/`

\- `docs/03-system-analysis/`

\- `docs/04-system-design/`

\- `docs/05-implementation/`

\- `docs/06-testing/01-testing-plan.md`

\- `docs/06-testing/02-test-case-specification.md`

\- `docs/06-testing/03-test-environment-validation.md`

\- `docs/06-testing/04-authentication-testing.md`

\- `docs/06-testing/05-repository-metadata-testing.md`

\- `docs/06-testing/06-authorization-testing.md`

\- `docs/06-testing/07-repository-storage-testing.md`

\- `docs/06-testing/08-git-process-execution-testing.md`

\- `docs/06-testing/09-repository-browser-testing.md`

\- `docs/06-testing/10-git-smart-http-read-testing.md`

\- `docs/06-testing/11-personal-access-token-testing.md`

\- `docs/06-testing/12-git-smart-http-write-testing.md`

\- `docs/06-testing/13-web-ui-testing.md`

\- `docs/06-testing/14-security-testing.md`

\- `docs/06-testing/15-observability-and-failure-testing.md`

\- `docs/06-testing/16-system-and-end-to-end-testing.md`

\- `docs/06-testing/17-regression-testing.md`

\- `docs/06-testing/18-acceptance-testing.md`

\- automated backend and frontend test suites

\- targeted real-Git Smart HTTP execution

\- database integrity execution

\- performance measurements

\- environment validation results



\---



\## 3. Requirement Inventory



The approved V1.0.0 requirements contain the following primary requirement families:



| Requirement Family | Range | Count |

|---|---:|---:|

| Functional Requirements | FR-001–FR-098 | 98 |

| Non-Functional Requirements | NFR-001–NFR-098 | 98 |

| Data Requirements | DR-001–DR-094 | 94 |

| Interface Requirements | IR-001–IR-100 | 100 |

| \*\*Total\*\* |  | \*\*390\*\* |



Supporting requirement artifacts also include business rules, authorization rules, use cases, and user stories.



Those supporting artifacts are used as contextual traceability sources but are not counted as additional primary requirements in the 390-requirement inventory.



\---



\## 4. Formal Test-Case Inventory



The approved Test Case Specification defines 141 formal test cases.



| Test Family | Count |

|---|---:|

| Acceptance | 10 |

| Authentication | 10 |

| Authorization | 8 |

| Repository Browser | 9 |

| Database | 5 |

| End-to-End | 7 |

| Failure Handling | 7 |

| Git Process Execution | 7 |

| Git Smart HTTP Read | 8 |

| Git Smart HTTP Write | 8 |

| Observability | 6 |

| Personal Access Token | 9 |

| Performance | 3 |

| Regression | 6 |

| Repository Metadata | 10 |

| Security | 12 |

| Repository Storage | 7 |

| Web UI | 9 |

| \*\*Total\*\* | \*\*141\*\* |



\---



\## 5. Functional Requirement Traceability



All functional requirements in the V1.0.0 scope have an explicit verification path.



The formal Test Case Specification explicitly references:



\- FR-001 through FR-098.



Therefore:



| Requirement Range | Verification Status |

|---|---|

| FR-001–FR-098 | COVERED |



Functional requirements are exercised through one or more of the following test families:



\- authentication;

\- authorization;

\- repository metadata;

\- repository storage;

\- Git process execution;

\- repository browser;

\- Git Smart HTTP read;

\- Git Smart HTTP write;

\- PAT management;

\- Web UI;

\- security;

\- failure handling;

\- observability;

\- system and end-to-end workflows;

\- regression;

\- acceptance.



No V1.0.0 functional requirement is currently identified as lacking a verification path.



\---



\## 6. Non-Functional Requirement Traceability



The Test Case Specification explicitly references:



\- NFR-001 through NFR-041.



These requirements are directly associated with formal runtime, security, reliability, performance, Git, API, and system verification cases.



NFR-042 through NFR-098 are not all represented as direct automated-test references because the approved requirement traceability model prescribes other verification mechanisms for many of them.



This is intentional and is not treated as an automatic coverage defect.



\### 6.1 NFR Verification Mechanisms



| NFR Range | Primary Verification Mechanism |

|---|---|

| NFR-001–NFR-041 | Formal automated/manual test cases |

| NFR-042–NFR-047 | Design and code review |

| NFR-048–NFR-053 | Test architecture review |

| NFR-054–NFR-058 | UI acceptance verification |

| NFR-059–NFR-062 | Accessibility review |

| NFR-063–NFR-067 | Compatibility verification |

| NFR-068–NFR-070 | Performance/load and design review |

| NFR-071–NFR-074 | Logging and observability verification |

| NFR-075–NFR-077 | Backup and recovery verification |

| NFR-078 | Recovery failure verification |

| NFR-079–NFR-082 | Deployment verification |

| NFR-083–NFR-085 | Environment verification |

| NFR-086–NFR-088 | Security and privacy verification |

| NFR-089–NFR-091 | Error-handling verification |

| NFR-092–NFR-094 | Content-safety verification |

| NFR-095–NFR-098 | Deployment and operational verification |



These requirements remain traceable through their approved verification mechanisms even when a dedicated `TC-\*` identifier is not the appropriate evidence type.



\---



\## 7. Data Requirement Traceability



DR-001 through DR-094 define persistent-data structures, relationships, integrity expectations, lifecycle behavior, and storage requirements.



The DR family is not artificially mapped one-to-one to formal test cases.



Its verification chain is:



> Data Requirement → System Analysis → Database/System Design → Implementation → Database/Repository Tests → System Verification



Relevant execution evidence includes:



\- repository metadata testing;

\- repository storage testing;

\- authentication persistence tests;

\- PAT persistence and ownership tests;

\- database integrity tests;

\- Git repository consistency tests;

\- system end-to-end tests.



The dedicated database integrity execution performed during WP-19 provides additional explicit evidence for critical schema and relationship behavior.



\---



\## 8. Interface Requirement Traceability



IR-001 through IR-100 define the interfaces between:



\- browser clients;

\- REST APIs;

\- authentication mechanisms;

\- repository browser operations;

\- Git Smart HTTP endpoints;

\- Git processes;

\- repository storage;

\- database persistence;

\- application services.



The IR family is verified through the architectural and implementation traceability chain rather than through artificial one-to-one `IR → TC` mappings.



Primary verification evidence includes:



\- authentication testing;

\- authorization testing;

\- repository metadata testing;

\- Git process execution testing;

\- repository browser testing;

\- Git Smart HTTP read testing;

\- Git Smart HTTP write testing;

\- Web UI testing;

\- observability and failure testing;

\- system and end-to-end testing;

\- regression testing;

\- acceptance testing.



\---



\## 9. Database Test Traceability



The formal specification defines five database test cases:



\- TC-DB-001

\- TC-DB-002

\- TC-DB-003

\- TC-DB-004

\- TC-DB-005



Earlier testing work provided distributed evidence for these cases, but the formal IDs were not explicitly recorded in the execution-result documents.



WP-19 therefore added dedicated automated database integrity coverage:



`backend/tests/Feature/Database/DatabaseIntegrityTest.php`



\### 9.1 Database Execution Result



Execution:



```text

PASS  Tests\\Feature\\Database\\DatabaseIntegrityTest



✓ required database migrations are applied

✓ user identity constraints are enforced

✓ repository owner relationship and constraints are preserved

✓ personal access token owner relationship and constraints are preserved

✓ repository metadata and managed storage remain consistent



Tests:    5 passed (24 assertions)

Duration: 1.39s
