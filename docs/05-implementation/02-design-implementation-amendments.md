# GitForge V1.0.0 Design-to-Implementation Amendments



## 1. Document Information



| Field | Value |

|---|---|

| Project | GitForge - Web Git Hosting Platform |

| Target release | V1.0.0 |

| Document | Design-to-Implementation Amendments |

| SDLC phase | Implementation |

| Milestone | M4 - Implementation |

| Project owner | Farhad Valizadegan |

| Status | Approved |

| Last updated | 2026-08-18 |



---



## 2. Purpose



This document records implementation-discovered clarifications or corrections required to make the approved System Design implementable without silently changing product behavior.



An amendment may be recorded only when implementation reveals that an approved design decision requires additional precision.



This document does not authorize uncontrolled scope expansion.



---



# Amendment Register



## 3. AMD-001 — Stable Owner Namespace Identifier



### Status



```text

Approved

```



### Classification



```text

Design clarification

```



### Affected capability



```text

Logical repository namespace

```



### Approved namespace



GitForge V1.0.0 identifies repositories logically as:



```text

<owner>/<repository>

```



Example:



```text

farhad/my-project

```



Git Smart HTTP URLs depend on this namespace.



Example:



```text

/farhad/my-project.git

```



### Implementation Discovery



During M4 implementation inspection, the existing application was found to use:



```text

users.username

```



as the owner portion of the repository namespace.



The approved System Design defines the logical owner/repository namespace but does not sufficiently specify a unique, URL-safe persisted identifier for the owner portion.



Using:



```text

users.name

```



would be unsuitable because a display name is not necessarily:



```text

Unique

Stable

URL-safe

Namespace-safe

```



### Decision



Retain:



```text

users.username

```



as the stable public owner namespace identifier for GitForge V1.0.0.



### Required properties



`username` must be:



```text

Required

Unique

Normalized to lowercase

URL-safe

Validated

Stored in PostgreSQL

```



### Namespace mapping



Logical repository identity:



```text

users.username

+

repositories.name

```



produces:



```text

<owner>/<repository>

```



Example:



```text

farhad/my-project

```



### Physical repository identity



The owner namespace is not used as the authoritative physical filesystem identifier.



Physical storage remains:



```text

<repository-root>/repositories/<repository-id>.git

```



where:



```text

<repository-id>

```



is the internal PostgreSQL repository primary key.



### Security consequence



Client-controlled owner and repository namespace values must never be concatenated directly into authoritative filesystem paths.



Resolution must occur as:



```text

Owner namespace

     ↓

Database lookup



Repository name

     ↓

Database lookup



Repository ID

     ↓

Repository Storage



Safe physical path

```



### Scope consequence



This clarification does not introduce:



```text

Organizations

Teams

Namespace transfers

Repository transfers

Repository renaming

User namespace renaming behavior

```



Those remain outside V1.0.0 unless separately approved.



### Implementation consequence



The V1.0.0 `users` metadata model includes:



```text

id

name

username

email

email_verified_at

password

remember_token

created_at

updated_at

```



The `username` field is the public owner namespace identifier.



---



## 4. Amendment Summary



| ID | Subject | Classification | Status |

|---|---|---|---|

| AMD-001 | Stable owner namespace identifier | Design clarification | Approved |



---



## 5. Blocking Design Amendments



```text

0

```



AMD-001 resolves the implementation-discovered namespace ambiguity.



---



## 6. Implementation Readiness



Following AMD-001:



```text

Owner namespace resolution      DEFINED

Repository logical identity     DEFINED

Repository physical identity    DEFINED

Implementation blocker          NONE

```



---



## 7. Current Status



```text

Approved

```



Implementation may proceed using `users.username` as the stable owner namespace identifier while continuing to use the repository database ID for physical repository storage.
