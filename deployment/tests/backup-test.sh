#!/usr/bin/env bash
set -euo pipefail

project_root="$(cd "$(dirname "${BASH_SOURCE[0]}")/../.." && pwd)"
test_root="$(mktemp -d "${TMPDIR:-/tmp}/gitforge-backup-test.XXXXXX")"
trap 'rm -rf "$test_root"' EXIT

mkdir -p "$test_root/bin" "$test_root/backend/storage/framework" "$test_root/storage/repositories/1.git"
printf 'preserved repository data\n' >"$test_root/storage/repositories/1.git/data.txt"

cat >"$test_root/bin/pg_dump" <<'STUB'
#!/usr/bin/env bash
set -euo pipefail
for argument in "$@"; do
    if [[ "$argument" == --file=* ]]; then
        printf 'isolated database fixture\n' >"${argument#--file=}"
    fi
done
if [[ "${REMOVE_MAINTENANCE_MARKER:-}" == 1 ]]; then
    rm -- "$GITFORGE_BACKEND_ROOT/storage/framework/down"
fi
STUB
chmod +x "$test_root/bin/pg_dump"

export PATH="$test_root/bin:$PATH"
export DB_HOST=127.0.0.1 DB_PORT=5432 DB_DATABASE=fixture DB_USERNAME=fixture PGPASSWORD=fixture
export GITFORGE_REPOSITORY_ROOT="$test_root/storage"
export GITFORGE_BACKEND_ROOT="$test_root/backend"
export BACKUP_DIR="$test_root/backups"

expect_refused() {
    if bash "$project_root/deployment/scripts/backup.sh" >"$test_root/refusal.log" 2>&1; then
        echo 'FAIL: unsafe backup unexpectedly succeeded' >&2
        exit 1
    fi
    grep -Fq 'Backup refused:' "$test_root/refusal.log"
}

touch "$GITFORGE_BACKEND_ROOT/storage/framework/down"
unset GITFORGE_BACKUP_WRITES_PAUSED
expect_refused
[[ ! -d "$BACKUP_DIR" ]]

export GITFORGE_BACKUP_WRITES_PAUSED=1
rm -- "$GITFORGE_BACKEND_ROOT/storage/framework/down"
expect_refused
[[ ! -d "$BACKUP_DIR" ]]

touch "$GITFORGE_BACKEND_ROOT/storage/framework/down"
backup_path="$(bash "$project_root/deployment/scripts/backup.sh")"
backup_path="${backup_path#Backup created: }"
bash "$project_root/deployment/scripts/verify-backup.sh" "$backup_path"
[[ -f "$GITFORGE_BACKEND_ROOT/storage/framework/down" ]]

export BACKUP_DIR="$test_root/interrupted-backups"
export REMOVE_MAINTENANCE_MARKER=1
expect_refused
[[ -z "$(find "$BACKUP_DIR" -mindepth 1 -print -quit)" ]]
[[ -f "$GITFORGE_REPOSITORY_ROOT/repositories/1.git/data.txt" ]]

echo 'Backup coordination tests: PASS'
