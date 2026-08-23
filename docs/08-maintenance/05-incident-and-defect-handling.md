# Incident and Defect Handling

## Intake

Record the observed behavior, affected version, reproducible steps, expected behavior, actual behavior, logs that do not contain secrets, and environment information.

## Classification

- Critical: data loss/corruption, authentication bypass, authorization bypass, credential exposure, or unusable core Git transport.
- High: major core workflow failure without a safe workaround.
- Medium: incorrect behavior with a reasonable workaround or limited impact.
- Low: cosmetic, documentation, or minor ergonomics defects.

## Resolution Workflow

1. reproduce or gather sufficient evidence;
2. isolate root cause;
3. create a focused fix;
4. add regression tests where applicable;
5. run the relevant focused suite;
6. run full release gates for release-bound changes;
7. update documentation and changelog;
8. merge only after green validation.

## Regression Policy

Previously fixed critical/high defects should remain covered by automated tests whenever technically practical.
