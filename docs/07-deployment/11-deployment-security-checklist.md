# Deployment Security Checklist

| Control | Required V1.0.0 State |
|---|---|
| Production environment | `APP_ENV=production` |
| Debugging | `APP_DEBUG=false` |
| TLS | HTTPS required for public use |
| Session cookies | Secure, HTTP-only, encrypted |
| Application secret | Unique `APP_KEY`, never committed |
| Database | PostgreSQL not publicly exposed by default |
| Runtime identity | Dedicated least-privilege `gitforge` user |
| Repository storage | Outside public web roots |
| Git binary | Deployment-controlled `/usr/bin/git` |
| Nginx server tokens | Disabled |
| Git request buffering | Disabled on Smart HTTP route |
| Production `.env` | Permission-restricted and untracked |
| Logs | No credentials or authorization secrets |
| Backups | Database + Git storage, integrity checks included |
| Rollback | Does not overwrite persistent data casually |

## Approval Rule

A known failure of a required control blocks M6 approval for V1.0.0 until corrected or explicitly re-scoped with documented justification.
