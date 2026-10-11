# Sprint workflow

Use this process for future planned sprints or bounded delivery increments. Historical work remains recorded under its original milestone or increment labels.

1. Refine backlog items and agree a focused goal.
2. Record included items, dependencies, exclusions, and acceptance criteria.
3. Implement in reviewable slices; keep one owner for each shared write surface.
4. Verify each slice with appropriate checks and isolated data.
5. Review integrated behavior and update documentation and traceability.
6. Record completion, incomplete work, operational handoff, and exact verification commits.
7. Carry unfinished items back to the backlog with their actual state.

```mermaid
flowchart LR
  Refine[Refine backlog] --> Plan[Agree goal and scope]
  Plan --> Work[Implement slices]
  Work --> Check[Verify integrated behavior]
  Check --> Review[Review and record outcomes]
  Review --> Done[Handoff completed work]
  Review --> Backlog[Return unfinished items]
  Backlog --> Refine
```

[Editable source](diagrams/sprint-workflow.mmd), [record template](../Sprints/TEMPLATE.md), [definition of done](DELIVERY.md), and [product backlog](product-backlog.md).
