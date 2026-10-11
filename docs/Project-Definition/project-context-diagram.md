# Project context diagram

This diagram shows the platform boundary and its primary runtime dependencies. PostgreSQL metadata and bare Git storage remain separate and require coordinated recovery.

```mermaid
flowchart LR
  User[Repository owner or permitted reader] --> Browser[Web browser]
  Owner[Repository owner] --> Client[Git client]
  subgraph Platform[GitForge platform]
    UI[React and TypeScript UI] --> API[Laravel API and policies]
    HTTP[Git Smart HTTP endpoint] --> Runner[Controlled Git subprocesses]
    API --> Runner
    API --> Queue[Actions queue]
    Queue --> Worker[Built-in checks worker]
    Worker --> Runner
  end
  Browser --> UI
  Client --> HTTP
  API --> DB[(PostgreSQL metadata)]
  Runner --> Bare[(Managed bare Git repositories)]
```

[Editable Mermaid source](diagrams/project-context.mmd). For deployment detail, see the [architecture design](../SDLC/System-Design/03-container-architecture.md).
