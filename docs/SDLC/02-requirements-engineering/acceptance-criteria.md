# Acceptance criteria

| Requirement | Observable acceptance criteria |
| --- | --- |
| RE-F01 / RE-S03 | Valid sign-in returns identity/session; invalid input and credentials produce controlled errors; logout ends authorized session use |
| RE-F02 / RE-S01 | Owner creates valid storage/metadata; visibility gates reads; failed initialization does not delete pre-existing storage |
| RE-F03 / RE-S04 | New token secret is shown once; persisted credentials are hashed; invalid/revoked token cannot authenticate |
| RE-F04 / RE-N02 | Real Git clone/fetch/push transfers expected history; unauthorized writes fail; limits/timeouts produce controlled failures |
| RE-F05 / RE-F07 | Selected refs/paths match Git content; invalid/missing/oversized/binary content is controlled; supported PDF download fallback is available |
| RE-F06 | Rename retains ID and physical Git data; updated clone URL is shown and old-name redirect is not implied |
| RE-F08 | Owner creates from valid source and selects an existing default; existing refs are not overwritten |
| RE-F09 | Requests/comments are repository-scoped; permitted author/owner transitions follow policy; unauthorized private data is concealed |
| RE-F10 / RE-N03 | Reviewed hashes are checked; stale/conflicting merge is rejected; successful refs/state agree; prepared merge recovery preserves user refs |
| RE-F11 / RE-I05 | Checks pin a committed revision, report controlled results under a worker, and retain cancellation against concurrent completion |
| RE-F12 / RE-N04 | Theme persists, navigation remains keyboard usable, and narrow layouts/fallback styles are reviewed on the candidate |
| RE-I04 / RE-D02 | Backup/restore evidence uses a coordinated metadata/Git recovery set and documented writer pause/drain |

Use isolated data. Record the exact candidate, environment, actual results, warnings, and blocked/skipped scenarios. No current test execution is implied solely by creating this table. See [traceability](requirements-traceability-matrix.md) and [testing phase](../06-testing/README.md).
