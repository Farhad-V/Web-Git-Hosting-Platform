# Use-case diagram

```mermaid
flowchart LR
  Owner[Repository owner] --> Manage[Manage repositories tokens and branches]
  Owner --> Merge[Review and merge requests]
  Owner --> Checks[Run or cancel built-in checks]
  Reader[Permitted reader] --> Browse[Browse code history and tags]
  SignedIn[Permitted signed-in participant] --> Discuss[Propose and discuss requests]
  Client[Authorized Git client] --> Transport[Clone fetch and permitted push]
  Operator[Operator] --> Recovery[Health worker supervision and recovery]
```

Actor permission depends on repository visibility and server policy. Public visitors can read public content; private repositories remain owner-only. Operator recovery is an operational procedure, not a public application endpoint. [Editable source](diagrams/use-case.mmd) and [use cases](use-cases.md).
