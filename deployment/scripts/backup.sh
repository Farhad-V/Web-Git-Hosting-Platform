#!/usr/bin/env bash
set -euo pipefail

: "${BACKUP_DIR:?Set BACKUP_DIR to the protected backup destination}"
: "${DB_HOST:?Set DB_HOST}"
: "${DB_PORT:=5432}"
: "${DB_DATABASE:?Set DB_DATABASE}"
: "${DB_USERNAME:?Set DB_USERNAME}"
: "${PGPASSWORD:?Set PGPASSWORD without placing it on the command line}"
: "${GITFORGE_REPOSITORY_ROOT:?Set GITFORGE_REPOSITORY_ROOT}"

repository_dir="$GITFORGE_REPOSITORY_ROOT/repositories"
[[ -d "$repository_dir" ]] || { echo "Repository directory does not exist: $repository_dir" >&2; exit 1; }

umask 077
timestamp="$(date -u +%Y%m%dT%H%M%SZ)"
final_dir="$BACKUP_DIR/gitforge-$timestamp"
staging_dir="$BACKUP_DIR/.gitforge-$timestamp.tmp"

mkdir -p "$BACKUP_DIR"
mkdir "$staging_dir"
cleanup() { rm -rf "$staging_dir"; }
trap cleanup ERR INT TERM

pg_dump \
    --host="$DB_HOST" \
    --port="$DB_PORT" \
    --username="$DB_USERNAME" \
    --dbname="$DB_DATABASE" \
    --format=custom \
    --file="$staging_dir/database.dump"

tar -C "$GITFORGE_REPOSITORY_ROOT" -czf "$staging_dir/repositories.tar.gz" repositories

cat >"$staging_dir/manifest.txt" <<EOF
format=gitforge-backup-v1
created_utc=$timestamp
database=$DB_DATABASE
repository_root=$GITFORGE_REPOSITORY_ROOT
EOF

(
    cd "$staging_dir"
    sha256sum database.dump repositories.tar.gz manifest.txt >SHA256SUMS
)

mv "$staging_dir" "$final_dir"
trap - ERR INT TERM

echo "Backup created: $final_dir"
