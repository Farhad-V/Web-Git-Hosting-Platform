# Network, TLS and Runtime Configuration

| Field | Value |
|---|---|
| Project | GitForge — Web Git Hosting Platform |
| Version | V1.0.0 |
| Milestone | M6 — Deployment |
| Status | Approved |

## Nginx

`deployment/nginx/gitforge.conf` defines HTTP-to-HTTPS redirection, TLS ingress, SPA serving, API forwarding, Git Smart HTTP forwarding and the Laravel liveness route.

Git Smart HTTP request buffering is disabled for the transport route so large Git request bodies are streamed instead of unnecessarily buffered by Nginx.

## PHP-FPM

`deployment/php-fpm/gitforge.conf` defines a dedicated `gitforge` pool and a local Unix socket consumed by Nginx. PHP errors are not displayed to clients in production.

## TLS

A real public deployment replaces the example host and certificate paths with the actual domain and certificate material. PAT credentials must never be intentionally transmitted over unencrypted production HTTP.

## Public Ports

- 443/TCP: HTTPS application traffic.
- 80/TCP: redirect to HTTPS only.
- PostgreSQL is not publicly exposed by default.
