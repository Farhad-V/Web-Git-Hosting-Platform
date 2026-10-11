# Requirements traceability matrix

| Requirement group | Design or behavior contract | Verification area |
| --- | --- | --- |
| RE-F01, RE-F03, RE-S03, RE-S04 | [API specification](../04-system-design/06-api-specification.md) | Authentication and token tests |
| RE-F02, RE-F06, RE-N01, RE-S01 | [Storage design](../04-system-design/08-repository-storage-design.md), [authorization](../04-system-design/07-authorization-design-matrix.md) | Repository metadata/storage/permission tests |
| RE-F04, RE-N02, RE-I02, RE-I03 | [Git execution design](../04-system-design/09-git-process-execution-design.md) | Real Git transport and process tests |
| RE-F05, RE-F07 | [Browser analysis](../03-system-analysis/08-repository-browser-analysis.md), [current contracts](../05-implementation/Features/repository-collaboration.md) | Branch/commit/tag/tree/blob/PDF and sidebar tests |
| RE-F08 to RE-F11, RE-N03, RE-I05 | [Collaboration contracts](../05-implementation/Features/repository-collaboration.md) | Workflow/merge/recovery/Actions tests |
| RE-F12, RE-N04 | [Visual system](../05-implementation/Features/visual-system.md) | Frontend checks plus candidate-specific visual scenarios |
| RE-N05, RE-N06, RE-I04, RE-D02 | [Deployment runbook](../07-deployment/04-deployment-runbook.md) | Health/deployment/audit/coordinated recovery checks |
| RE-N07, RE-I07 | [Release validation](../../Releases/v1/VALIDATION.md) | Exact commit, CI, limitations, and release identity |

This maps verification areas rather than asserting every test passed on a current candidate. Link each change to its exact code/review, test outcomes, and release status. Keep IDs stable when extending this summary.

Historical detailed matrices: [requirements](14-requirements-traceability-matrix.md), [implementation](../05-implementation/03-implementation-traceability.md), and [testing](../06-testing/19-test-traceability.md). [Management traceability](../../Project-Management/traceability.md) connects work to delivery status.
