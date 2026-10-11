# Security Maintenance

## Security Baseline

Security maintenance preserves the controls verified before V1.0.0: server-side authorization, private repository concealment, CSRF protection, throttling, non-reversible PAT storage, controlled Git process execution, path validation, bounded request/blob sizes, credential-safe logging, least-privilege runtime configuration, and storage outside public web roots.

## Vulnerability Handling

1. confirm affected component and supported version;
2. assess exploitability and impact on GitForge;
3. patch or upgrade in an isolated branch;
4. add regression coverage when applicable;
5. run backend, frontend, dependency, and release-readiness gates;
6. document the fix in the changelog;
7. publish a patch release when the fix affects distributed code.

## Secrets

No passwords, PAT plaintext, Authorization headers, production `.env` files, private keys, or user repository data are committed to the repository.

## Security Regression Rule

A change that weakens authorization, storage isolation, process execution safety, CSRF protection, or credential handling is a release blocker until corrected and verified.
