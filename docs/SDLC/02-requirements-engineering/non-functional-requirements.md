# Non-functional requirements

| ID | Quality requirement | Verification |
| --- | --- | --- |
| RE-N01 | Preserve metadata and managed Git data across normal operations and controlled failure paths | Storage/compensation tests and coordinated recovery review |
| RE-N02 | Bound transport/preview inputs and subprocess execution | Stream/size/time-limit tests; inspect configuration |
| RE-N03 | Make concurrent merge decisions against reviewed refs | Hash, atomic-ref, concurrent-close, and recovery checks |
| RE-N04 | Provide readable light/dark surfaces, keyboard focus, responsive layout, and reduced-motion/fallback behavior | Frontend tests/build plus browser/accessibility scenarios |
| RE-N05 | Expose controlled health and diagnostic information without leaking credentials | Readiness and observability/security tests |
| RE-N06 | Keep delivery reproducible through lockfiles, migrations, CI, and documented runtime | Build/audit/deployment gates |
| RE-N07 | Separate historical evidence from current candidate validation | Exact commit/environment and skipped checks recorded |

No measured availability SLA, load capacity, accessibility certification, or latency guarantee is assigned without an agreed target and fresh measurements. Token-level contrast measurements and historical passing checks are evidence within their stated scope, not universal certification.

Known constraints include a main bundle-size warning, platform-dependent symlink coverage, browser PDF behavior, and manual local worker restart after reboot. See [limitations](../../Releases/v1/KNOWN-LIMITATIONS.md) and [historical quality requirements](05-non-functional-requirements.md).
