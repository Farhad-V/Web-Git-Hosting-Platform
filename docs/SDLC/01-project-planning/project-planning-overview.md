# Project planning overview

## Purpose and project context

Phase 01 establishes why a change is needed, what it will deliver, and how completion will be demonstrated. GitForge is a self-hosted Git platform and software-engineering portfolio project built with Laravel, React/TypeScript, PostgreSQL, and managed bare Git repositories.

The historical M0 milestone belongs to v1.0.0. The latest published baseline is v1.0.1; main includes later collaboration, UI, and documentation changes that remain unreleased. Planning for new work must use current implementation evidence rather than assume historical acceptance covers later changes.

## Planning inputs

| Input | Source |
| --- | --- |
| User problem and product purpose | [Project definition](../../Project-Definition/project-definition-overview.md) |
| Current capabilities and exclusions | [Product scope](../../Project-Definition/project-scope.md) |
| Goals and acceptance evidence | [Objectives](../../Project-Definition/project-goals-and-objectives.md), [success criteria](../../Project-Definition/project-success-criteria.md) |
| Runtime and operational constraints | [Assumptions](../../Project-Definition/assumptions-and-constraints.md), [risk register](../../Project-Definition/project-risks.md) |
| Pending work and dependencies | [Product backlog](../../Project-Management/product-backlog.md), [roadmap](../../Project-Management/product-roadmap.md) |
| Delivery history | [Milestones](../../Project-Management/milestones.md), [sprint register](../../Sprints/SPRINT-REGISTER.md) |

## Planning activities

1. Describe the affected user, trigger, observed problem, and desired outcome.
2. Define observable acceptance criteria, scope, exclusions, and dependencies.
3. Identify authorization, compatibility, data, security, queue, and recovery implications.
4. Refine a bounded backlog item and sequence work across affected SDLC phases.
5. Select verification scenarios and an isolated test environment appropriate to the change.
6. Define review, documentation, and operational handoff requirements.
7. Record unresolved decisions; assign dates, estimates, roles, and versions only when agreed.

Detailed working steps are in the [planning process](project-planning.md). Historical numbered sprint records are reconstructed delivery groupings, not evidence of originally approved sprint commitments.

## Expected outputs

- A clear goal and user-visible outcome.
- A scoped backlog item with acceptance criteria and exclusions.
- Dependencies, constraints, material risks, and unresolved decisions.
- A proposed implementation sequence and verification approach.
- Required documentation, review, and operational handoff.
- Explicit distinction between implemented, verified, merged, and released states.

## Completion and handoff

Planning is ready when the affected work can proceed without inventing material product decisions and its acceptance evidence is defined. Requirement details continue in [Phase 02](../02-requirements-engineering/README.md); analysis and design revisit affected system boundaries before implementation.

Use the [delivery completion criteria](../../Project-Management/DELIVERY.md) when implementing the plan and the separate [release checklist](../../Releases/v1/RELEASE-CHECKLIST.md) when proposing publication. Planning documentation alone does not execute pending backlog items or publish a version.

Return to the [planning index](README.md) or [SDLC index](../README.md).
