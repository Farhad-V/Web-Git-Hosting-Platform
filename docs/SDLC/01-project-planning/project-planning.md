# Project planning process

## Inputs

Use the user request, current implementation, project definition, backlog, operational constraints, and relevant historical evidence. Confirm whether work targets a defect, feature, improvement, documentation increment, or release candidate.

## Planning steps

1. Describe the problem, affected user, desired outcome, and measurable acceptance criteria.
2. Record scope, exclusions, dependencies, authorization boundaries, and data impact.
3. Review architecture and operations where the change crosses APIs, storage, security, queues, or recovery boundaries.
4. Refine a bounded backlog item and sequence dependent work.
5. Choose implementation and verification appropriate to the risk, using isolated test data.
6. Record review, documentation, and operational handoff requirements.
7. Keep dates, estimates, and release versions unset until agreed; do not invent historical sprint commitments.

## Outputs and review gate

A ready plan identifies scope, acceptance criteria, affected SDLC phases, risks, dependencies, evidence, and unresolved decisions. Implemented, tested, merged, and released are distinct states. Apply the [delivery criteria](../../Project-Management/DELIVERY.md) and [release checklist](../../Releases/v1/RELEASE-CHECKLIST.md) at their respective gates.

Planning does not authorize arbitrary repository code execution, destructive operations, or release publication. Refer to [current scope](../../Project-Definition/project-scope.md) and [assumptions](../../Project-Definition/assumptions-and-constraints.md).
