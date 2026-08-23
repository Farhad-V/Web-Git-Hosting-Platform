#!/usr/bin/env bash
set -euo pipefail

backup_dir="${1:-}"

if [[ -z "$backup_dir" ]]; then
    echo "Usage: $0 /path/to/gitforge-backup" >&2
    exit 2
fi

for required_file in database.dump repositories.tar.gz manifest.txt SHA256SUMS; do
    [[ -f "$backup_dir/$required_file" ]] || { echo "Missing backup component: $required_file" >&2; exit 1; }
done

grep -Fxq 'format=gitforge-backup-v1' "$backup_dir/manifest.txt"

(
    cd "$backup_dir"
    sha256sum --check SHA256SUMS
)

tar -tzf "$backup_dir/repositories.tar.gz" >/dev/null

echo "GitForge backup verification: PASS"
