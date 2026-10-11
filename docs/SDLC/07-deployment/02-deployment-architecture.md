# Deployment Architecture

| Field | Value |
|---|---|
| Project | GitForge — Web Git Hosting Platform |
| Version | V1.0.0 |
| Milestone | M6 — Deployment |
| Status | Approved |

## Production Topology

```text
Internet
   |
 HTTPS :443
   |
 Nginx
   |--------------------------|
   |                          |
 React SPA                    FastCGI
 frontend/dist                |
                              v
                         PHP-FPM 8.4
                              |
                           Laravel 12
                         /      |       \
                        /       |        \
                PostgreSQL 18  Git       /var/lib/gitforge/repositories
```

Nginx is the public ingress. It serves the compiled React SPA and forwards API, health and Git Smart HTTP requests to Laravel through the dedicated PHP-FPM socket.

## Trust Boundaries

- TLS terminates at Nginx.
- Laravel is not exposed directly to the public network.
- PostgreSQL is bound to localhost or a trusted private network.
- repository storage is outside all web roots;
- Git executable selection is controlled by deployment configuration;
- secrets exist only in the deployed backend `.env` or equivalent secret store.

## Persistent State

Two independent data domains must survive application releases:

1. PostgreSQL metadata and identity data.
2. Bare Git repositories under `/var/lib/gitforge/repositories`.

A backup that contains only one domain is incomplete.

## V1.0.0 Deployment Boundary

V1.0.0 intentionally uses a single-host topology for clarity and reproducibility. Kubernetes, distributed storage, SSH Git transport and high-availability database clustering are outside this release scope.

## Production deployment topology

![Production deployment topology](diagrams/02-deployment-architecture.svg)

[PlantUML source](diagrams/02-deployment-architecture.puml) · [Full-size SVG](diagrams/02-deployment-architecture.svg)

This diagram illustrates the documented procedure. Historical approvals remain tied to their recorded release and evidence; new candidates require fresh validation.
