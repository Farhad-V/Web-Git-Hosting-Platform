# Backup and Recovery Maintenance

GitForge data integrity depends on both PostgreSQL metadata and persistent bare Git repository storage. Maintenance must keep the backup and recovery procedures aligned with schema and storage changes.

## Required Maintenance Checks

- keep `deployment/scripts/backup.sh` compatible with the current PostgreSQL/database naming model;
- keep `deployment/scripts/verify-backup.sh` compatible with the backup manifest format;
- review restore instructions after migration or storage-layout changes;
- never treat a database-only or repository-only backup as a complete GitForge recovery set;
- preserve the rule that missing storage is not silently recreated and orphan physical repositories are not automatically adopted.

## Portfolio Scope

V1.0.0 does not require a continuously running backup schedule because the project is not publicly hosted. The documented backup/verification/recovery procedures remain part of the engineering baseline and are exercised when a real runtime is maintained.
