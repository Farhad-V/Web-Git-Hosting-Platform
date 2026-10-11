# Release validation and evidence

Validation belongs to an exact candidate commit and environment. A successful historical checkpoint is useful evidence but does not certify later code or a live production deployment.

## Recorded checkpoints

| Checkpoint | Recorded result | Scope |
| --- | --- | --- |
| v1.0.1 patch audit | 205 local backend tests passed, 1,012 assertions; one Windows symlink skip; 36 frontend tests passed | Historical patch validation described in its release notes |
| Collaboration checkpoint 22ee75b | 227 GitHub backend tests passed, 1,153 assertions; four CI gates passed | Merged feature candidate |
| Local QA 2026-10-11 | 57 frontend and 65 backend unit tests passed; one Windows symlink skip | Local checks, not a fresh full backend run |
| Documentation checkpoint 9d5d496 | Backend CI, Deployment Validation, and Release Readiness passed | Reorganized documentation and workflow references |

For later commits, consult their actual [GitHub Actions runs](https://github.com/Farhad-Valizadegan/Web-Git-Hosting-Platform/actions). These counts are recorded history, not a live dashboard.

## Candidate evidence requirements

- Record candidate SHA, platform, dependency locks, and relevant configuration.
- Review backend formatting, authentication/policy tests, Git transport/storage, and affected concurrency/recovery behavior.
- Run frontend lint, tests, type checking/build, and dependency audits; review warnings rather than masking them.
- Verify deployment migrations, health, queue supervision, release-note paths, and metadata consistency.
- Exercise relevant visual, keyboard, responsive, and PDF fallback scenarios with isolated data.
- Record skipped/blocked scenarios, known defects, and operational checks that were not performed.

CI path filters may skip an independent workflow for a documentation-only change. Inspect actual triggered runs and the release gate's coverage rather than assuming every workflow ran. Use the [candidate checklist](RELEASE-CHECKLIST.md) and [known limitations](KNOWN-LIMITATIONS.md).
