# System context diagram

```mermaid
flowchart LR
  Browser[Browser user] --> UI[React interface]
  UI --> API[Laravel API and policies]
  Client[Git client] --> HTTP[Git Smart HTTP]
  HTTP --> Git[Controlled Git processes]
  API --> Git
  API --> DB[(PostgreSQL metadata)]
  API --> Queue[Dedicated Actions queue]
  Queue --> Worker[Built-in checks worker]
  Worker --> Git
  Git --> Storage[(Managed bare repositories)]
```

The diagram describes runtime boundaries; GitHub hosts this project's engineering source/CI separately. [Editable source](diagrams/system-context.mmd), [integration requirements](integration-requirements.md), and [trust-boundary analysis](../03-system-analysis/15-trust-boundary-analysis.md).
