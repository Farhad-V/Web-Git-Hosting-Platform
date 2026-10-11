# Requirements lifecycle diagram

```mermaid
flowchart LR
  Need[Stakeholder need or defect] --> Refine[Scope and acceptance criteria]
  Refine --> Analyze[Analysis and design]
  Analyze --> Implement[Focused implementation]
  Implement --> Verify[Candidate-specific verification]
  Verify --> Review[Review evidence and limitations]
  Review --> Merge[Merge approved work]
  Merge --> Release[Separate authorized release gate]
  Review -->|Unmet criteria| Refine
```

Requirement documentation, merged code, passing checks, and a tagged release are distinct outcomes. Update traceability when scope or acceptance changes. [Editable source](diagrams/requirements-lifecycle.mmd), [refinement process](../../Project-Management/backlog-refinement.md), and [traceability](requirements-traceability-matrix.md).
