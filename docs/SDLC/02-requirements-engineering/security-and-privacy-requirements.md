# Security and privacy requirements

| ID | Requirement | Evidence |
| --- | --- | --- |
| RE-S01 | Enforce repository permissions server-side and conceal private data from unauthorized callers | API/policy/security tests |
| RE-S02 | Require owner authority for repository settings, push, branches/default changes, merge, and Actions mutations | Owner/non-owner and anonymous cases |
| RE-S03 | Protect session mutations with CSRF and apply throttling | Middleware and request tests |
| RE-S04 | Persist non-reversible token hashes, reject revoked/invalid credentials, and avoid logging secrets | Token and credential-safe logging tests |
| RE-S05 | Validate paths, refs, nested resource ownership, process arguments, and input limits | Traversal/injection/scoping/boundary tests |
| RE-S06 | Keep Git storage outside public roots and use appropriate TLS/least privilege in production | Deployment/security review |
| RE-S07 | Limit collection/exposure to required metadata and respect repository visibility in previews/workflows | Serialization/policy and privacy review |

Public repository visibility exposes its permitted Git content and workflow data; users must review visibility intentionally. Public Git metadata can include author identity. Never put credentials or sensitive test data into docs, fixtures, commits, or logs.

This document is an engineering requirement summary, not a legal compliance certification. Formal retention, erasure, and privacy obligations require project-owner/operator policy decisions. See [data handling](data-classification-retention.md), [trust boundaries](../03-system-analysis/15-trust-boundary-analysis.md), and [historical authorization matrix](12-authorization-requirements-matrix.md).
