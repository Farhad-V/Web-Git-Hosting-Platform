# PB-02: improve local Actions worker startup

Status: improvement pending; manual starter exists. Responsible role: operator/maintainer. Schedule: not assigned.

Local Actions requires a running dedicated queue worker. The existing Windows helper starts a hidden worker and reuses a matching process, but startup is required again after reboot.

Acceptance criteria:

- Agree whether startup should be part of a local launch command or an explicitly configured supervised service.
- Preserve process identity checks and prevent duplicate workers.
- Start only the dedicated queue with documented timeout/retry settings.
- Verify restart behavior, failure visibility, and cleanup without terminating unrelated processes.
- Document startup/restart steps and avoid silently installing persistent system changes.

Reference: [Actions worker contracts](../../SDLC/Implementation/Features/repository-collaboration.md). Queue-running behavior is already implemented; this item concerns reliable startup and supervision.
