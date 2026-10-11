# Future scope

These are options for future planning, not implemented capabilities or scheduled commitments.

| Option | Decisions needed before delivery |
| --- | --- |
| Organizations and team permissions | Membership model, roles, repository policies, and migration strategy |
| Fork and cross-repository pull requests | Ownership boundaries, ref access, comparison, and authorization |
| Reviews and protected branches | Approval rules, exemptions, required checks, and atomic enforcement |
| Arbitrary CI runners or workflow YAML | Isolation, secret handling, execution limits, trust boundaries, and operator model |
| SSH transport | Key lifecycle, command restrictions, authentication, and deployment |
| Repository archive/deletion | Recovery window, retention, authorization, and coordinated storage handling |
| Distributed storage | Consistency, backup/recovery, replication, and failure handling |

The selected Actions implementation runs built-in repository checks only. Changing that boundary needs a separate design and approval. See [current scope](../Project-Definition/project-scope.md), [roadmap](product-roadmap.md), and [backlog refinement](backlog-refinement.md).
