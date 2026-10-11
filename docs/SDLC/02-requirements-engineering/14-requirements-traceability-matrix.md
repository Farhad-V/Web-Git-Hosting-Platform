# GitForge V1.0.0 Requirements Traceability Matrix

## 1. Document Information

| Field          | Value                               |
| -------------- | ----------------------------------- |
| Project        | GitForge - Web Git Hosting Platform |
| Target release | V1.0.0                              |
| Document       | Requirements Traceability Matrix    |
| SDLC phase     | Requirements Engineering            |
| Milestone      | M1 - Requirements Engineering       |
| Project owner  | Farhad Valizadegan                  |
| Status         | Approved                            |
| Last updated   | 2026-08-17                          |

---

## 2. Purpose

This document establishes requirements traceability for GitForge V1.0.0.

The Requirements Traceability Matrix, or RTM, connects project objectives and requirements to:

* actors;
* use cases;
* user stories;
* authorization requirements;
* priority;
* future analysis artifacts;
* future design artifacts;
* future implementation artifacts;
* future verification artifacts.

The RTM is intended to ensure that every material V1.0.0 requirement:

* has an identified source;
* has an identified purpose;
* belongs to the approved scope;
* can be traced into later SDLC phases;
* can ultimately be verified.

---

# 3. Traceability Model

The intended end-to-end traceability chain is:

```text
Project Definition
        ↓
Requirement
        ↓
Use Case / User Story
        ↓
System Analysis
        ↓
System Design
        ↓
Implementation
        ↓
Test Case
        ↓
Release Verification
```

A shorter operational form is:

```text
Goal
  ↓
Requirement
  ↓
Behavior
  ↓
Design
  ↓
Code
  ↓
Test
```

---

# 4. Traceability Directions

GitForge uses both forward and backward traceability.

## 4.1 Forward Traceability

Forward traceability answers:

```text
Where is this requirement implemented and verified?
```

Example:

```text
FR-019
Repository creation
    ↓
UC-004
    ↓
US-004
    ↓
Future repository creation service
    ↓
Future feature/integration tests
```

---

## 4.2 Backward Traceability

Backward traceability answers:

```text
Why does this component or test exist?
```

Example:

```text
Future authorization middleware
        ↑
AR-001
AR-004
FR-061
FR-066
        ↑
Repository security objective
```

---

# 5. Traceability Status Values

The following status values may be used throughout the SDLC:

| Status      | Meaning                                                           |
| ----------- | ----------------------------------------------------------------- |
| Defined     | Requirement exists and has been documented                        |
| Mapped      | Requirement has been associated with behavior or another artifact |
| Designed    | Requirement has a corresponding approved design                   |
| Implemented | Requirement has corresponding implementation                      |
| Verified    | Requirement has passed its required verification                  |
| Deferred    | Requirement is intentionally postponed                            |
| Excluded    | Capability is outside V1.0.0                                      |
| Conditional | Requirement depends on an unresolved or optional scope decision   |

At the end of M1, most V1.0.0 requirements should be:

```text
Defined
Mapped
```

Implementation and verification statuses belong to later phases.

---

# 6. Traceability Source Artifacts

The RTM derives from the following approved M0 and M1 artifacts:

```text
01-project-definition/
    docs/Project-Definition/README.md#project-charter
    docs/Project-Definition/README.md#problem-statement
    docs/Project-Definition/README.md#product-vision
    docs/Project-Definition/README.md#project-goals
    docs/Project-Definition/README.md#stakeholders

02-requirements-engineering/
    01-requirements-engineering-plan.md
    02-requirements-elicitation.md
    03-actors-and-external-systems.md
    04-functional-requirements.md
    05-non-functional-requirements.md
    06-data-requirements.md
    07-interface-requirements.md
    08-business-rules.md
    09-use-case-model.md
    10-detailed-use-case-specifications.md
    11-user-stories-and-acceptance-criteria.md
    12-authorization-requirements-matrix.md
    13-requirements-prioritization.md
```

---

# 7. Project Goal Traceability

The following project-level goals provide the primary requirement origin.

## PG-001 — Deliver a Working Web Git Hosting Platform

Traceable requirements include:

```text
FR-013 – FR-049
FR-089 – FR-095
DR-008 – DR-029
IR-019 – IR-069
UC-004 – UC-019
US-004 – US-018
```

---

## PG-002 — Support Real Git Client Operations

Traceable requirements include:

```text
FR-036 – FR-049
NFR-026
NFR-027
NFR-063 – NFR-067
IR-026 – IR-044
IR-061 – IR-069
UC-015 – UC-019
US-015 – US-018
```

---

## PG-003 — Protect Repository Access

Traceable requirements include:

```text
FR-061 – FR-066
NFR-001 – NFR-018
BR-017 – BR-031
AR-001 – AR-060
UC-023
UC-024
US-022
US-023
```

---

## PG-004 — Protect Credentials and Authentication

Traceable requirements include:

```text
FR-001 – FR-009
FR-050 – FR-060
NFR-019 – NFR-022
DR-030 – DR-043
IR-082 – IR-085
AR-035 – AR-044
UC-001
UC-002
UC-020 – UC-022
US-001
US-002
US-019 – US-021
```

---

## PG-005 — Preserve Repository and Data Integrity

Traceable requirements include:

```text
FR-067 – FR-078
NFR-028 – NFR-038
DR-017 – DR-029
DR-055 – DR-070
BR-032 – BR-040
BR-062 – BR-075
US-024 – US-026
```

---

## PG-006 — Provide Maintainable and Testable Engineering

Traceable requirements include:

```text
NFR-042 – NFR-053
NFR-071 – NFR-082
FR-096 – FR-098
US-029
```

---

# 8. Functional Requirements Traceability Matrix

## 8.1 Identity and Authentication

| Requirement     | Related Use Case       | User Story             | Priority | Status |
| --------------- | ---------------------- | ---------------------- | -------- | ------ |
| FR-001 – FR-004 | UC-001                 | US-001                 | Must     | Mapped |
| FR-005 – FR-009 | UC-002, UC-003         | US-002, US-003         | Must     | Mapped |
| FR-010 – FR-012 | UC-002, UC-004, UC-020 | US-002, US-004, US-019 | Must     | Mapped |

---

## 8.2 Repository Creation

| Requirement     | Related Use Case | User Story | Priority | Status |
| --------------- | ---------------- | ---------- | -------- | ------ |
| FR-013 – FR-020 | UC-004           | US-004     | Must     | Mapped |

Primary related authorization requirements:

```text
AR-010
AR-011
AR-012
```

---

## 8.3 Repository Retrieval and Visibility

| Requirement     | Related Use Case | User Story     | Priority | Status |
| --------------- | ---------------- | -------------- | -------- | ------ |
| FR-021 – FR-024 | UC-005, UC-006   | US-005, US-006 | Must     | Mapped |
| FR-025 – FR-028 | UC-006, UC-023   | US-006, US-022 | Must     | Mapped |

Primary authorization mapping:

```text
AR-013 – AR-018
```

---

## 8.4 Repository Browser

| Requirement | Related Use Case       | User Story             | Priority | Status |
| ----------- | ---------------------- | ---------------------- | -------- | ------ |
| FR-029      | UC-009                 | US-009                 | Must     | Mapped |
| FR-030      | UC-010                 | US-010                 | Must     | Mapped |
| FR-031      | UC-011                 | US-011                 | Must     | Mapped |
| FR-032      | UC-012                 | US-012                 | Should   | Mapped |
| FR-033      | UC-013                 | US-013                 | Must     | Mapped |
| FR-034      | UC-014                 | US-014                 | Must     | Mapped |
| FR-035      | UC-009, UC-013, UC-014 | US-009, US-013, US-014 | Must     | Mapped |

Primary authorization mapping:

```text
AR-029 – AR-034
```

---

## 8.5 Git Smart HTTP Read

| Requirement     | Related Use Case               | User Story                     | Priority | Status |
| --------------- | ------------------------------ | ------------------------------ | -------- | ------ |
| FR-036 – FR-039 | UC-015, UC-016                 | US-015                         | Must     | Mapped |
| FR-040 – FR-042 | UC-016, UC-017, UC-021, UC-023 | US-016, US-017, US-020, US-022 | Must     | Mapped |

Primary authorization mapping:

```text
AR-045 – AR-049
```

---

## 8.6 Git Smart HTTP Write

| Requirement     | Related Use Case               | User Story             | Priority | Status |
| --------------- | ------------------------------ | ---------------------- | -------- | ------ |
| FR-043 – FR-049 | UC-018, UC-019, UC-021, UC-024 | US-018, US-020, US-023 | Must     | Mapped |

Primary authorization mapping:

```text
AR-050 – AR-055
```

---

## 8.7 Personal Access Tokens

| Requirement     | Related Use Case | User Story | Priority | Status |
| --------------- | ---------------- | ---------- | -------- | ------ |
| FR-050 – FR-054 | UC-020           | US-019     | Must     | Mapped |
| FR-055 – FR-057 | UC-021           | US-020     | Must     | Mapped |
| FR-058 – FR-060 | UC-022           | US-021     | Must     | Mapped |

Primary authorization mapping:

```text
AR-035 – AR-044
```

---

## 8.8 Core Authorization

| Requirement     | Related Use Case | User Story | Priority | Status |
| --------------- | ---------------- | ---------- | -------- | ------ |
| FR-061 – FR-063 | UC-023           | US-022     | Must     | Mapped |
| FR-064 – FR-066 | UC-024           | US-023     | Must     | Mapped |

Primary authorization mapping:

```text
AR-001 – AR-005
AR-013 – AR-024
```

---

## 8.9 Repository Storage

| Requirement     | Related Use Case                               | User Story                                             | Priority | Status |
| --------------- | ---------------------------------------------- | ------------------------------------------------------ | -------- | ------ |
| FR-067 – FR-070 | UC-004, UC-009, UC-016, UC-017, UC-019, UC-025 | US-004, US-009, US-016, US-017, US-018, US-024, US-025 | Must     | Mapped |

---

## 8.10 Git Process Execution

| Requirement     | Related Use Case | User Story              | Priority | Status |
| --------------- | ---------------- | ----------------------- | -------- | ------ |
| FR-071 – FR-074 | UC-015 – UC-019  | US-015 – US-018, US-026 | Must     | Mapped |

---

## 8.11 Validation

| Requirement     | Related Use Case | User Story | Priority | Status |
| --------------- | ---------------- | ---------- | -------- | ------ |
| FR-075 – FR-078 | Multiple         | US-027     | Must     | Mapped |

---

## 8.12 Error Handling

| Requirement     | Related Use Case | User Story             | Priority | Status |
| --------------- | ---------------- | ---------------------- | -------- | ------ |
| FR-079 – FR-083 | Multiple, UC-025 | US-025, US-028, US-029 | Must     | Mapped |

---

## 8.13 Repository Lifecycle

| Requirement     | Related Use Case | User Story | Priority                     | Status      |
| --------------- | ---------------- | ---------- | ---------------------------- | ----------- |
| FR-084 – FR-085 | UC-007           | US-007     | Should / Must-if-implemented | Mapped      |
| FR-086 – FR-088 | UC-008           | US-008     | Could / Conditional          | Conditional |

---

## 8.14 Web Interfaces

| Requirement     | Related Use Case                | User Story                      | Priority | Status |
| --------------- | ------------------------------- | ------------------------------- | -------- | ------ |
| FR-089 – FR-095 | UC-001 – UC-014, UC-020, UC-022 | US-001 – US-014, US-019, US-021 | Must     | Mapped |

---

## 8.15 Logging

| Requirement | Related Use Case        | User Story              | Priority | Status |
| ----------- | ----------------------- | ----------------------- | -------- | ------ |
| FR-096      | UC-025                  | US-029                  | Should   | Mapped |
| FR-097      | UC-020 – UC-022, UC-025 | US-019 – US-021, US-029 | Must     | Mapped |
| FR-098      | UC-025                  | US-025, US-029          | Should   | Mapped |

---

# 9. Non-Functional Requirements Traceability

## 9.1 Security

| Requirement Range | Related Functional Areas                                        | Priority | Verification Direction |
| ----------------- | --------------------------------------------------------------- | -------- | ---------------------- |
| NFR-001 – NFR-018 | Authentication, authorization, repository access, Git execution | Must     | Security tests         |

---

## 9.2 Authentication and Session Quality

| Requirement Range | Related Artifacts              | Priority | Verification Direction |
| ----------------- | ------------------------------ | -------- | ---------------------- |
| NFR-019 – NFR-022 | UC-002, UC-003, US-002, US-003 | Must     | Feature/security tests |

---

## 9.3 Performance

| Requirement Range | Related Areas                 | Priority | Verification Direction              |
| ----------------- | ----------------------------- | -------- | ----------------------------------- |
| NFR-023 – NFR-025 | Web UI and repository browser | Should   | Performance testing                 |
| NFR-026 – NFR-027 | Git Smart HTTP                | Must     | Git integration/performance testing |

---

## 9.4 Reliability and Integrity

| Requirement Range | Related Areas                          | Priority | Verification Direction    |
| ----------------- | -------------------------------------- | -------- | ------------------------- |
| NFR-028 – NFR-033 | Git/repository operations              | Must     | Integration/failure tests |
| NFR-034 – NFR-038 | Metadata and physical repository state | Must     | Integrity tests           |

---

## 9.5 Availability and Recovery

| Requirement Range | Priority | Verification Direction       |
| ----------------- | -------- | ---------------------------- |
| NFR-039           | Should   | Deployment observation       |
| NFR-040 – NFR-041 | Must     | Failure/restart tests        |
| NFR-075 – NFR-077 | Should   | Backup/recovery verification |
| NFR-078           | Must     | Recovery failure tests       |

---

## 9.6 Maintainability and Testability

| Requirement Range | Priority                      | Verification Direction   |
| ----------------- | ----------------------------- | ------------------------ |
| NFR-042 – NFR-047 | Should / Must where specified | Design/code review       |
| NFR-048 – NFR-053 | Must                          | Test architecture review |

---

## 9.7 Usability and Accessibility

| Requirement Range | Priority | Verification Direction |
| ----------------- | -------- | ---------------------- |
| NFR-054 – NFR-058 | Should   | UI acceptance testing  |
| NFR-059 – NFR-062 | Should   | Accessibility review   |

---

## 9.8 Compatibility

| Requirement Range | Priority | Verification Direction                 |
| ----------------- | -------- | -------------------------------------- |
| NFR-063 – NFR-067 | Must     | Git client/browser compatibility tests |

---

## 9.9 Scalability and Observability

| Requirement Range | Priority                  | Verification Direction      |
| ----------------- | ------------------------- | --------------------------- |
| NFR-068 – NFR-070 | Should / scope constraint | Load/design review          |
| NFR-071 – NFR-074 | Should                    | Log/diagnostic verification |

---

## 9.10 Deployment, Portability, Privacy and Safety

| Requirement Range | Priority | Verification Direction              |
| ----------------- | -------- | ----------------------------------- |
| NFR-079 – NFR-082 | Must     | Deployment testing                  |
| NFR-083 – NFR-085 | Should   | Environment verification            |
| NFR-086 – NFR-088 | Must     | Security/privacy review             |
| NFR-089 – NFR-091 | Must     | Error-handling tests                |
| NFR-092 – NFR-094 | Must     | Content-safety tests                |
| NFR-095 – NFR-098 | Must     | 07-deployment/operational verification |

---

# 10. Data Requirements Traceability

| Data Requirement Range | Functional Dependency       | Priority           | Future Design Mapping              |
| ---------------------- | --------------------------- | ------------------ | ---------------------------------- |
| DR-001 – DR-007        | Identity/authentication     | Must               | User schema                        |
| DR-008 – DR-016        | Repository metadata         | Must               | Repository schema                  |
| DR-017 – DR-023        | Physical repository storage | Must               | Repository storage design          |
| DR-024 – DR-029        | Git-native objects and refs | Must               | Git storage/process design         |
| DR-030 – DR-039        | PAT metadata                | Must               | Token schema/authentication design |
| DR-040 – DR-043        | Sessions                    | Must               | Authentication/session design      |
| DR-044 – DR-048        | Repository browser          | Must               | Git repository access components   |
| DR-049 – DR-054        | Entity relationships        | Must               | Database schema                    |
| DR-055 – DR-060        | Integrity                   | Must               | Constraints/transactions/services  |
| DR-061 – DR-065        | Validation                  | Must               | Validation rules                   |
| DR-066 – DR-070        | Security                    | Must               | Security controls                  |
| DR-071 – DR-073        | Privacy                     | Must               | Privacy/security design            |
| DR-074 – DR-077        | Lifecycle                   | Must / Conditional | Lifecycle services                 |
| DR-078 – DR-081        | Retention                   | Should             | Operations design                  |
| DR-082 – DR-085        | Recovery                    | Should             | Backup/recovery design             |
| DR-086 – DR-090        | Derived Git data            | Must               | Git inspection services            |
| DR-091 – DR-094        | Operational data            | Should             | Logging/observability design       |

---

# 11. Interface Requirements Traceability

| Interface Requirement Range | Related System Boundary    | Priority                  | Future Design Artifact          |
| --------------------------- | -------------------------- | ------------------------- | ------------------------------- |
| IR-001 – IR-018             | Browser ↔ GitForge         | Must / Should             | UI information architecture     |
| IR-019 – IR-025             | HTTP application interface | Must                      | API/route design                |
| IR-026 – IR-040             | Git Smart HTTP             | Must                      | Git HTTP protocol design        |
| IR-041 – IR-044             | Git Client                 | Must                      | Compatibility design            |
| IR-045 – IR-050             | PostgreSQL                 | Must                      | Database design                 |
| IR-051 – IR-060             | Filesystem                 | Must                      | Repository storage design       |
| IR-061 – IR-069             | Git executable             | Must                      | Git process-execution design    |
| IR-070 – IR-075             | Runtime configuration      | Must                      | 07-deployment/configuration design |
| IR-076 – IR-081             | Logging                    | Should / Must for secrets | Observability design            |
| IR-082 – IR-085             | Authentication             | Must                      | Authentication design           |
| IR-086 – IR-090             | Authorization              | Must                      | Authorization design            |
| IR-091 – IR-094             | Validation                 | Must                      | Validation design               |
| IR-095 – IR-100             | Error responses            | Must                      | Error-handling design           |

---

# 12. Business Rules Traceability

## 12.1 Identity Rules

```text
BR-001 – BR-010
```

Primary mappings:

```text
FR-001 – FR-012
DR-001 – DR-007
UC-001
UC-002
US-001
US-002
```

---

## 12.2 Repository Ownership and Visibility Rules

```text
BR-011 – BR-031
```

Primary mappings:

```text
FR-013 – FR-028
FR-043 – FR-049
AR-010 – AR-024
UC-004
UC-006
UC-019
UC-023
UC-024
US-004
US-006
US-018
US-022
US-023
```

---

## 12.3 Repository Storage and Consistency Rules

```text
BR-032 – BR-040
```

Primary mappings:

```text
FR-067 – FR-070
DR-017 – DR-029
US-024
US-025
```

---

## 12.4 PAT Rules

```text
BR-041 – BR-050
```

Primary mappings:

```text
FR-050 – FR-060
DR-030 – DR-039
AR-035 – AR-044
UC-020 – UC-022
US-019 – US-021
```

---

## 12.5 Authorization Rules

```text
BR-051 – BR-061
```

Primary mappings:

```text
FR-061 – FR-066
AR-001 – AR-060
UC-023
UC-024
US-022
US-023
```

---

## 12.6 Git Process and Validation Rules

```text
BR-062 – BR-075
```

Primary mappings:

```text
FR-071 – FR-083
IR-061 – IR-069
US-026 – US-028
```

---

## 12.7 Repository Lifecycle Rules

```text
BR-076 – BR-080
```

Primary mappings:

```text
FR-084 – FR-088
UC-007
UC-008
US-007
US-008
```

---

## 12.8 Content, Operations and Scope Rules

```text
BR-081 – BR-096
```

Primary mappings:

```text
FR-029 – FR-035
FR-096 – FR-098
NFR-092 – NFR-098
UC-009 – UC-014
UC-025
US-009 – US-014
US-029
```

---

# 13. Authorization Requirements Traceability

| Authorization Range | Primary Requirement Area | Use Cases               | User Stories                   |
| ------------------- | ------------------------ | ----------------------- | ------------------------------ |
| AR-001 – AR-005     | General authorization    | UC-023, UC-024          | US-022, US-023                 |
| AR-006 – AR-009     | Account access           | UC-001 – UC-003         | US-001 – US-003                |
| AR-010 – AR-012     | Repository creation      | UC-004                  | US-004                         |
| AR-013 – AR-018     | Repository reads         | UC-006, UC-023          | US-006, US-022                 |
| AR-019 – AR-024     | Repository writes        | UC-019, UC-024          | US-018, US-023                 |
| AR-025 – AR-028     | Repository settings      | UC-007, UC-008          | US-007, US-008                 |
| AR-029 – AR-034     | Repository browser       | UC-009 – UC-014         | US-009 – US-014                |
| AR-035 – AR-039     | PAT management           | UC-020, UC-022          | US-019, US-021                 |
| AR-040 – AR-044     | PAT authentication       | UC-021                  | US-020                         |
| AR-045 – AR-049     | Git read                 | UC-015 – UC-017, UC-023 | US-015 – US-017, US-022        |
| AR-050 – AR-055     | Git write                | UC-018, UC-019, UC-024  | US-018, US-023                 |
| AR-056 – AR-060     | Authorization failures   | UC-023 – UC-025         | US-022, US-023, US-028, US-029 |

---

# 14. Use Case Traceability

| Use Case                                     | Primary Functional Requirements | User Story     | Priority |
| -------------------------------------------- | ------------------------------- | -------------- | -------- |
| UC-001 Register User Account                 | FR-001 – FR-004                 | US-001         | Must     |
| UC-002 Authenticate User                     | FR-005 – FR-009                 | US-002         | Must     |
| UC-003 Logout                                | FR-008                          | US-003         | Must     |
| UC-004 Create Repository                     | FR-013 – FR-020                 | US-004         | Must     |
| UC-005 List Owned Repositories               | FR-021                          | US-005         | Must     |
| UC-006 View Repository                       | FR-022 – FR-028                 | US-006         | Must     |
| UC-007 Change Repository Visibility          | FR-084 – FR-085                 | US-007         | Should   |
| UC-008 Delete Repository                     | FR-086 – FR-088                 | US-008         | Could    |
| UC-009 Browse Repository                     | FR-029                          | US-009         | Must     |
| UC-010 List Branches                         | FR-030                          | US-010         | Must     |
| UC-011 View Commit History                   | FR-031                          | US-011         | Must     |
| UC-012 View Commit Details                   | FR-032                          | US-012         | Should   |
| UC-013 Browse Repository Tree                | FR-033, FR-035                  | US-013         | Must     |
| UC-014 View File Content                     | FR-034                          | US-014         | Must     |
| UC-015 Discover Git Read Service             | FR-036 – FR-039                 | US-015         | Must     |
| UC-016 Clone Repository                      | FR-036 – FR-041                 | US-015, US-016 | Must     |
| UC-017 Fetch Repository                      | FR-042                          | US-017         | Must     |
| UC-018 Discover Git Write Service            | FR-043 – FR-045                 | US-018         | Must     |
| UC-019 Push Repository Changes               | FR-043 – FR-049                 | US-018         | Must     |
| UC-020 Create PAT                            | FR-050 – FR-054                 | US-019         | Must     |
| UC-021 Authenticate Git Request with PAT     | FR-055 – FR-057                 | US-020         | Must     |
| UC-022 Revoke PAT                            | FR-058 – FR-060                 | US-021         | Must     |
| UC-023 Authorize Repository Read             | FR-061 – FR-063                 | US-022         | Must     |
| UC-024 Authorize Repository Write            | FR-064 – FR-066                 | US-023         | Must     |
| UC-025 Diagnose Repository Operation Failure | FR-096 – FR-098                 | US-025, US-029 | Should   |

---

# 15. User Story Traceability

| User Story | Use Case       | Requirement Area         | Priority |
| ---------- | -------------- | ------------------------ | -------- |
| US-001     | UC-001         | Registration             | Must     |
| US-002     | UC-002         | Authentication           | Must     |
| US-003     | UC-003         | Logout                   | Must     |
| US-004     | UC-004         | Repository creation      | Must     |
| US-005     | UC-005         | Repository listing       | Must     |
| US-006     | UC-006         | Repository retrieval     | Must     |
| US-007     | UC-007         | Visibility update        | Should   |
| US-008     | UC-008         | Repository deletion      | Could    |
| US-009     | UC-009         | Repository browser       | Must     |
| US-010     | UC-010         | Branch listing           | Must     |
| US-011     | UC-011         | Commit history           | Must     |
| US-012     | UC-012         | Commit details           | Should   |
| US-013     | UC-013         | Tree browsing            | Must     |
| US-014     | UC-014         | File viewing             | Must     |
| US-015     | UC-015, UC-016 | Public Git read          | Must     |
| US-016     | UC-016         | Private clone            | Must     |
| US-017     | UC-017         | Fetch                    | Must     |
| US-018     | UC-018, UC-019 | Push                     | Must     |
| US-019     | UC-020         | PAT creation             | Must     |
| US-020     | UC-021         | PAT authentication       | Must     |
| US-021     | UC-022         | PAT revocation           | Must     |
| US-022     | UC-023         | Read authorization       | Must     |
| US-023     | UC-024         | Write authorization      | Must     |
| US-024     | Supporting     | Repository path safety   | Must     |
| US-025     | UC-025         | Missing storage handling | Must     |
| US-026     | Supporting     | Safe Git execution       | Must     |
| US-027     | Supporting     | Validation               | Must     |
| US-028     | Supporting     | Safe errors              | Must     |
| US-029     | UC-025         | Diagnostics              | Should   |

---

# 16. Future System Analysis Traceability

During System Analysis, the current requirement groups should map to analysis responsibilities.

| Requirement Area    | Future Analysis Focus                     |
| ------------------- | ----------------------------------------- |
| Identity            | User/account behavior                     |
| Authentication      | Authentication flows and trust boundaries |
| Repository metadata | Repository domain model                   |
| Repository storage  | Logical repository-storage behavior       |
| Git Smart HTTP      | Git protocol workflows                    |
| PAT                 | Credential lifecycle                      |
| Authorization       | Access-control decision model             |
| Browser             | Repository inspection behavior            |
| Validation          | Input/domain validation                   |
| Error handling      | Failure-state model                       |
| Logging             | Operational event model                   |

Exact analysis artifact names will be established in SDLC Phase 3.

---

# 17. Future System Design Traceability

The current requirement groups are expected to map to the following System Design deliverables:

| Requirement Area            | Expected Design Deliverable            |
| --------------------------- | -------------------------------------- |
| Identity and Authentication | Authentication design                  |
| Repository metadata         | Database schema                        |
| Repository storage          | Repository-storage design              |
| Git Smart HTTP              | API/protocol design                    |
| Git execution               | Git process-execution design           |
| Authorization               | Authorization matrix and policy design |
| Browser                     | Component design / UI architecture     |
| PAT                         | Authentication/token design            |
| Deployment NFRs             | Deployment design                      |
| Logging                     | Observability design                   |
| Security                    | Security design                        |
| Data integrity              | Database/service design                |

---

# 18. Future Implementation Traceability

Exact implementation artifacts are intentionally not fixed during Requirements Engineering.

Expected implementation categories include:

```text
Controllers
Routes
Middleware
Policies
Services
Models
Database migrations
Repositories / storage abstractions
Git process wrappers
Git Smart HTTP handlers
Validation objects
Authentication components
PAT components
Views / frontend components
Logging configuration
Tests
```

Implementation names will be added to the RTM only after corresponding design decisions exist.

---

# 19. Future Test Traceability

Every Must requirement shall ultimately map to one or more verification artifacts.

Expected verification categories include:

| Requirement Area            | Expected Test Type                  |
| --------------------------- | ----------------------------------- |
| Registration/authentication | Feature and security tests          |
| Repository creation         | Feature/integration tests           |
| Repository ownership        | Authorization tests                 |
| Private repository access   | Security tests                      |
| Git clone/fetch             | Git interoperability tests          |
| Git push                    | Git interoperability/security tests |
| PAT lifecycle               | Authentication/security tests       |
| Path safety                 | Security tests                      |
| Git process execution       | Integration/security tests          |
| Repository browser          | Feature/integration tests           |
| Data integrity              | Integration tests                   |
| Validation                  | Feature/unit tests                  |
| Error handling              | Feature/integration tests           |
| Performance                 | Performance tests                   |
| Deployment                  | Deployment verification             |

---

# 20. Release-Critical Traceability

The following areas require complete traceability before V1.0.0 release:

```text
Authentication
Repository ownership
Private repository read authorization
Repository write authorization
Anonymous write prevention
Non-owner write prevention
PAT security
PAT revocation
Repository path isolation
Git process safety
Clone
Fetch
Push
Metadata/storage consistency
Credential protection
Critical validation
```

For these areas, the expected final traceability chain is:

```text
Requirement
    ↓
Design
    ↓
Implementation
    ↓
Automated Test
    ↓
Release Evidence
```

---

# 21. Negative Requirement Traceability

Security and authorization requirements must include negative verification.

Examples:

| Requirement     | Required Negative Scenario          |
| --------------- | ----------------------------------- |
| FR-061 / AR-016 | Anonymous private repository access |
| FR-062 / AR-021 | Anonymous write                     |
| FR-064 / AR-022 | Authenticated non-owner write       |
| FR-056 / AR-042 | Revoked token use                   |
| FR-068          | Path traversal input                |
| FR-073          | Unapproved Git command              |
| FR-078          | Invalid security-sensitive input    |
| FR-097          | Credential exposure in logs         |

Positive-only testing is insufficient for these requirements.

---

# 22. Conditional Requirement Traceability

The following requirements remain conditional at this stage.

## 22.1 Repository Deletion

```text
FR-086 – FR-088
UC-008
US-008
AR-027
BR-078 – BR-080
```

Current priority:

```text
Could
```

Status:

```text
Conditional
```

---

## 22.2 Repository Rename

Rename remains a potential optional capability and shall not be treated as a release blocker unless formally promoted into V1.0.0 scope.

Status:

```text
Conditional
```

---

## 22.3 PAT Scopes

Separate read/write PAT scopes remain optional.

The mandatory authorization rule remains:

```text
A PAT cannot grant access that its owner does not possess.
```

---

## 22.4 PAT Expiration

PAT expiration is optional for V1.0.0.

Token revocation remains mandatory.

---

# 23. Scope Exclusion Traceability

The following capabilities are explicitly outside V1.0.0 and therefore shall not generate implementation obligations for this release:

| Capability                | Status   |
| ------------------------- | -------- |
| Organizations             | Excluded |
| Teams                     | Excluded |
| Repository collaborators  | Excluded |
| Forks                     | Excluded |
| Pull requests             | Excluded |
| Merge requests            | Excluded |
| Code review               | Excluded |
| Issues                    | Excluded |
| Project boards            | Excluded |
| CI/CD                     | Excluded |
| Wiki                      | Excluded |
| Package registry          | Excluded |
| Container registry        | Excluded |
| Git LFS                   | Excluded |
| SSH Git transport         | Excluded |
| Billing                   | Excluded |
| Federation                | Excluded |
| Enterprise ACLs           | Excluded |
| Multi-region storage      | Excluded |
| Advanced analytics        | Excluded |
| Social feed functionality | Excluded |

---

# 24. Orphan Requirement Check

A requirement is considered orphaned if it:

* has no source;
* has no actor or behavioral purpose;
* has no relation to an approved project objective;
* cannot be mapped into later analysis/design/verification.

Current M1 requirement groups have been reviewed for orphan risk.

No core requirement class is intentionally orphaned.

Any orphan discovered during later analysis must trigger one of:

```text
Map requirement
Revise requirement
Justify requirement
Defer requirement
Remove requirement through controlled change
```

---

# 25. Orphan Design Check

Future design or implementation artifacts must also be checked backward.

A design element with no requirement or approved engineering justification may represent:

```text
Scope creep
Unnecessary complexity
Gold plating
Untraceable implementation
```

Not every technical implementation detail requires an individual end-user requirement, but material features and security behavior must remain traceable.

---

# 26. Coverage Rules

A Must requirement is considered adequately traced when it has:

1. a documented requirement identifier;
2. an approved source or business justification;
3. a related behavior or use case where applicable;
4. a priority;
5. a future verification direction.

Later phases will add:

6. analysis mapping;
7. design mapping;
8. implementation mapping;
9. test-case mapping;
10. verification result.

---

# 27. Requirements Change Traceability

If a requirement changes after M1 approval, the affected traceability entries must be updated.

A change review must inspect:

```text
Project scope
Related requirements
Use cases
User stories
Authorization rules
Priority
Analysis
Design
Implementation
Tests
Release criteria
```

A changed requirement must not leave stale downstream mappings.

---

# 28. Traceability Maintenance Rules

The RTM shall be maintained throughout the project.

Updates are required when:

* a requirement is added;
* a requirement is changed;
* a requirement is removed;
* priority changes;
* an analysis artifact is created;
* a design artifact is approved;
* implementation is completed;
* a test case is created;
* verification status changes;
* scope is deferred.

---

# 29. Requirements Coverage Summary

Current M1 traceability coverage includes:

```text
Functional Requirements      FR-001 – FR-098
Non-Functional Requirements  NFR-001 – NFR-098
Data Requirements            DR-001 – DR-094
Interface Requirements       IR-001 – IR-100
Business Rules               BR-001 – BR-096
Authorization Requirements   AR-001 – AR-060
Use Cases                    UC-001 – UC-025
User Stories                 US-001 – US-029
```

These requirement families have defined mapping paths into subsequent SDLC phases.

---

# 30. M1 Traceability Completion Criteria

Requirements traceability is considered sufficient for M1 when:

* every requirement family is represented;
* core functional requirements map to use cases or user stories;
* authorization requirements map to protected operations;
* priorities are incorporated;
* future analysis and design directions are identified;
* future verification directions are identified;
* conditional requirements are explicitly marked;
* excluded capabilities are documented;
* no known release-critical requirement is orphaned;
* traceability supports transition into System Analysis.

---

# 31. M1 Traceability Status

At the end of Requirements Engineering, the intended status is:

```text
Requirement Definition      Complete
Behavior Mapping            Complete
Priority Mapping            Complete
Authorization Mapping       Complete
Analysis Mapping            Planned
Design Mapping              Planned
Implementation Mapping      Planned
Test Mapping                Planned
Verification Mapping        Planned
```

This is expected because later mappings cannot be completed before their respective SDLC artifacts exist.

---

# 32. Relationship to Remaining M1 Documents

The RTM will be reviewed during:

```text
15-requirements-validation.md
```

and formally considered during:

```text
16-milestone-review.md
```

Any critical inconsistency discovered during validation must be corrected before M1 is declared complete.

---

# 33. Document Status

This document is currently:

```text
Approved
```

It will be changed to:

```text
Approved
```

after traceability has been reviewed for completeness, coverage, consistency, priority alignment, authorization coverage, scope control, and readiness for System Analysis.
