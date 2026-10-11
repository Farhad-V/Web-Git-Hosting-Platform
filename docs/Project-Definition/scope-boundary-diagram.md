# Scope boundary diagram

Development capabilities shown here are implemented on main; a new tagged release has not been declared. Excluded capabilities are future options, not promised work.

```mermaid
flowchart TB
  subgraph Included[Implemented scope]
    Identity[Sessions and personal access tokens]
    Repos[Owner-centric repositories and visibility]
    Git[Git Smart HTTP and managed storage]
    Browse[Branches commits tags files and supported PDFs]
    Pulls[Same-repository pull requests and owner merges]
    Actions[Built-in repository checks]
    UI[Light and dark responsive interface]
  end
  subgraph Excluded[Outside current scope]
    Teams[Organizations and team permissions]
    Forks[Fork and cross-repository requests]
    Rules[Protected branches and approval reviews]
    Scripts[Arbitrary workflow scripts and YAML]
    SSH[SSH transport and distributed storage]
  end
  Definition[Project definition] --> Included
  Definition -. records exclusions .-> Excluded
```

[Editable Mermaid source](diagrams/scope-boundary.mmd). See [scope](project-scope.md) for operational limitations and release distinctions.
