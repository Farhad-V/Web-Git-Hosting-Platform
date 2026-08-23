#!/usr/bin/env bash
set -euo pipefail

BASE_URL="${BASE_URL:-}"

if [[ -z "$BASE_URL" ]]; then
    echo "Usage: BASE_URL=https://gitforge.example.com $0" >&2
    exit 2
fi

BASE_URL="${BASE_URL%/}"

curl_common=(--fail --silent --show-error --location --max-time 15)

printf 'Checking frontend root... '
curl "${curl_common[@]}" "$BASE_URL/" >/dev/null
echo "PASS"

printf 'Checking Laravel liveness endpoint... '
curl "${curl_common[@]}" "$BASE_URL/up" >/dev/null
echo "PASS"

printf 'Checking HTTPS requirement... '
if [[ "$BASE_URL" != https://* ]]; then
    echo "FAIL"
    echo "Production smoke tests require an https:// BASE_URL." >&2
    exit 1
fi
echo "PASS"

echo "GitForge basic deployment smoke test: PASS"
