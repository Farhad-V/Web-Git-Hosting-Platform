# Stakeholder ecosystem diagram

This view describes roles around the project. It is not an authorization diagram; server-side policies determine application permissions.

```mermaid
flowchart LR
  Owner[Project owner] -->|scope and release decisions| Project[GitForge project]
  RepoOwner[Repository owner] -->|repository and Git workflows| Project
  Participant[Permitted participant or public visitor] -->|read and permitted discussion| Project
  Operator[Operator] -->|runtime and recovery| Project
  Maintainer[Maintainer] -->|implementation and documentation| Project
  Reviewer[Reviewer or evaluator] -->|verification and feedback| Project
  Upstream[Dependency maintainers] -->|tool and library updates| Project
  Project -->|source and CI evidence| GitHub[GitHub engineering repository]
```

[Editable Mermaid source](diagrams/stakeholder-ecosystem.mmd). Role details and external boundaries are in [stakeholders and external systems](stakeholders-and-external-systems.md).
