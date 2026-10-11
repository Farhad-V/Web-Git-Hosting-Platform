# Dependency Update Policy

## Objective

Keep dependencies supportable without destabilizing the V1.0.0 baseline.

## Backend

- inspect `composer outdated` before planned updates;
- run `composer audit --locked` on every release gate;
- keep Laravel/PHP compatibility explicit;
- regenerate and review `composer.lock` whenever dependency resolution changes;
- run migrations, Pint, and the full backend suite after dependency changes.

## Frontend

- inspect npm dependency changes before merging;
- run `npm audit --omit=dev --audit-level=high` on release gates;
- use `npm ci` for reproducible validation;
- run ESLint, Vitest, and the production build after dependency changes;
- commit `package-lock.json` changes together with `package.json` changes.

## Update Strategy

Security fixes take priority. Routine major-version updates are isolated from unrelated feature work and require compatibility review. Automated green checks are necessary but do not replace review of breaking changes, migrations, or security-sensitive behavior.
