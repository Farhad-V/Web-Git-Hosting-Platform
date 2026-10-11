# Integration requirements

| ID | Boundary | Requirement |
| --- | --- | --- |
| RE-I01 | Browser/API | Use versioned APIs, session authentication, CSRF-protected mutations, and controlled errors |
| RE-I02 | Git client/transport | Honor Smart HTTP discovery/read/write protocol with token authentication and repository authorization |
| RE-I03 | Backend/Git | Use structured process arguments, validated refs/paths, bounded input, and controlled timeouts |
| RE-I04 | Metadata/Git storage | Keep ID-based managed storage separate from names; coordinate backup/recovery across both stores |
| RE-I05 | Actions/queue | Dispatch to gitforge-actions, retain run state/results, and preserve cancellation against concurrent completion |
| RE-I06 | Development/production | Local frontend 5173 proxies API to backend 8000; production uses appropriate public origins and runtime profiles |
| RE-I07 | Engineering/GitHub | Preserve source, review, CI, and version identity; hosted GitForge repositories are distinct from this engineering repository |

External Git and dependency tooling must match documented compatibility. Modern merge-tree needs Git 2.38 or later. Restart queue workers when deploying new code. No arbitrary repository script execution or external-provider synchronization is introduced by these requirements.

See [API design](../04-system-design/06-api-specification.md), [collaboration API contracts](../05-implementation/Features/repository-collaboration.md), and [context diagram](system-context-diagram.md).
