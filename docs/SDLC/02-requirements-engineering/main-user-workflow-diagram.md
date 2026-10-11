# Main user workflow diagram

```mermaid
flowchart TD
  Start[Authenticate as repository owner] --> Create[Create repository and choose visibility]
  Create --> Token[Issue Git token if needed]
  Token --> Push[Clone or initialize and push committed data]
  Push --> Browse[Browse branch history files and tags]
  Browse --> Branch[Create a branch and push changes]
  Branch --> Request[Open same-repository pull request]
  Request --> Review[Review comparison and hashes]
  Review --> Valid{Refs unchanged and merge clean?}
  Valid -->|Yes| Merge[Owner merges reviewed change]
  Valid -->|No| Resolve[Resolve conflict or refresh review]
  Resolve --> Review
  Merge --> Checks[Optionally queue built-in checks]
  Checks --> Worker[Running worker records results]
```

This is a representative owner journey, not the only supported ordering. Checks are optional and are not required merge gates. A stopped worker leaves a run queued. [Editable source](diagrams/main-user-workflow.mmd) and [acceptance criteria](acceptance-criteria.md).
