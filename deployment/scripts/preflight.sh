#!/usr/bin/env bash
set -euo pipefail

REPO_ROOT="$(cd "$(dirname "${BASH_SOURCE[0]}")/../.." && pwd)"

required_commands=(php composer node npm git psql pg_dump nginx curl)
missing=0

for command_name in "${required_commands[@]}"; do
    if ! command -v "$command_name" >/dev/null 2>&1; then
        echo "ERROR: required command not found: $command_name" >&2
        missing=1
    fi
done

if [[ "$missing" -ne 0 ]]; then
    exit 1
fi

required_files=(
    "$REPO_ROOT/deployment/env/production.env.example"
    "$REPO_ROOT/deployment/nginx/gitforge.conf"
    "$REPO_ROOT/deployment/php-fpm/gitforge.conf"
    "$REPO_ROOT/backend/composer.json"
    "$REPO_ROOT/frontend/package.json"
)

for file_path in "${required_files[@]}"; do
    [[ -f "$file_path" ]] || { echo "ERROR: missing deployment input: $file_path" >&2; exit 1; }
done

if [[ -f "$REPO_ROOT/deployment/env/production.env" ]]; then
    echo "ERROR: production secrets must not be stored under deployment/env." >&2
    exit 1
fi

grep -Fxq 'APP_ENV=production' "$REPO_ROOT/deployment/env/production.env.example"
grep -Fxq 'APP_DEBUG=false' "$REPO_ROOT/deployment/env/production.env.example"
grep -Fxq 'SESSION_SECURE_COOKIE=true' "$REPO_ROOT/deployment/env/production.env.example"
grep -Fq 'server_tokens off;' "$REPO_ROOT/deployment/nginx/gitforge.conf"
grep -Fq 'fastcgi_request_buffering off;' "$REPO_ROOT/deployment/nginx/gitforge.conf"

echo "GitForge deployment preflight: PASS"
