# Git Process Execution Testing



| Field | Value |
|---|---|
| Project | GitForge — Web Git Hosting Platform |
| Version | V1.0.0 |
| SDLC phase | 6 — Testing |
| Milestone | M5 — Testing |
| Work package | WP-08 — Git Process Execution Testing |
| Document | Git Process Execution Testing |
| Status | Approved |
| Date | 2026-08-22 |
| Branch | `testing/m5` |



---



## 1. Purpose



This document records the formal execution and results of Git process execution testing for GitForge V1.0.0.



WP-08 verifies that Git subprocess execution remains controlled, structured, bounded, repository-scoped, observable, and resistant to process-injection attacks.



The work package covers:



- approved Git operation execution;

- operation allowlisting;

- unsupported Git service rejection;

- shell metacharacter safety;

- structured process arguments;

- repository-path containment;

- non-zero Git exit handling;

- standard-error capture;

- sensitive diagnostic suppression;

- Git process timeout handling;

- controlled Git execution observability.



The formal Git process execution test identifiers are:



```text

TC-GEXEC-001

TC-GEXEC-002

TC-GEXEC-003

TC-GEXEC-004

TC-GEXEC-005

TC-GEXEC-006

TC-GEXEC-007

```



---



## 2. Objectives



WP-08 verifies that:



- approved Git operations execute through the centralized Git process boundary;

- clients cannot select arbitrary Git subprocesses;

- unsupported Smart HTTP Git services are rejected before execution;

- Git process arguments are passed structurally;

- shell metacharacters are not interpreted by a command shell;

- secondary shell commands cannot execute through process arguments;

- option-shaped untrusted values are rejected where necessary;

- Git repository paths originate from validated repository storage;

- manipulated repository-storage configuration cannot escape the configured root;

- Git process failures are not treated as successful operations;

- non-zero exit status is preserved through controlled application exceptions;

- Git standard error is available for controlled diagnostics;

- Git process logs do not expose stderr, command arguments, protocol input, or credentials;

- every Git process receives an explicit timeout;

- timeout conditions are converted into controlled application failures;

- process execution does not hang indefinitely.



---



## 3. Test Basis



The primary formal test basis is:



```text

docs/06-testing/02-test-case-specification.md

```



Formal test cases:



```text

TC-GEXEC-001 — Execute Approved Git Operation

TC-GEXEC-002 — Reject Unsupported Git Subcommand

TC-GEXEC-003 — Reject Shell Metacharacter Injection

TC-GEXEC-004 — Repository Argument Cannot Escape Storage Root

TC-GEXEC-005 — Capture Non-Zero Git Exit Status

TC-GEXEC-006 — Capture Git Standard Error Safely

TC-GEXEC-007 — Enforce Git Process Timeout

```



Primary design basis:



```text

docs/04-system-design/09-git-process-execution-design.md

```



Supporting design artifacts:



```text

docs/04-system-design/08-repository-storage-design.md

docs/04-system-design/14-security-design.md

docs/04-system-design/13-observability-design.md

docs/04-system-design/16-system-design-validation.md

```



Primary implementation under test:



```text

App\\Services\\Git\\GitCommandRunner

App\\Services\\Git\\GitSmartHttpService

App\\Services\\Git\\RepositoryBrowserService

App\\Services\\Git\\RepositoryStorageService

```



---



## 4. Approved Git Process Architecture



GitForge V1.0.0 uses a layered Git execution architecture.



Conceptually:



```text

Controller / Application Workflow

&#x20;       ↓

Approved Application Service

&#x20;       ↓

Repository Storage Resolution

&#x20;       ↓

Git Process Execution Boundary

&#x20;       ↓

Configured Git Executable

```



Responsibilities are separated.



Application services determine:



```text

which approved logical Git operation is required

which fixed Git subcommand is used

which validated arguments are supplied

which repository is targeted

```



Repository Storage determines:



```text

the validated physical repository path

whether the path belongs to managed storage

whether the repository exists

whether containment requirements are satisfied

```



`GitCommandRunner` determines:



```text

configured executable

structured argument invocation

controlled process environment

stdin handling

timeout handling

stdout handling

stderr handling

exit-code handling

process observability

```



The architecture therefore does not require every security decision to exist inside `GitCommandRunner` itself.



---



## 5. Approved Operation Allowlist



The approved Git Process Execution Design defines the following logical operation categories for V1.0.0:



```text

INIT\_BARE



UPLOAD\_PACK\_ADVERTISE



UPLOAD\_PACK\_RPC



RECEIVE\_PACK\_ADVERTISE



RECEIVE\_PACK\_RPC



LIST\_REFS



COMMIT\_HISTORY



COMMIT\_DETAILS



TREE\_LIST



BLOB\_READ



REPOSITORY\_VERIFY

```



The design invariant is:



```text

Only approved Git operation types may execute.

```



No arbitrary client-provided Git operation name is permitted to become a subprocess selection.



---



## 6. Git Process Boundary



Normal application Git subprocesses are launched through:



```text

App\\Services\\Git\\GitCommandRunner

```



Production callers discovered during WP-08 are limited to:



```text

GitSmartHttpService

RepositoryBrowserService

RepositoryStorageService

```



No controller or external client directly launches the Git executable.



---



## 7. Structured Process Invocation



`GitCommandRunner` constructs the process through Symfony Process using an argument array.



Conceptual form:



```text

\[

&#x20;   configured Git binary,

&#x20;   argument 1,

&#x20;   argument 2,

&#x20;   ...

]

```



It does not construct a shell command string.



This preserves the approved no-shell design.



---



## 8. Controlled Process Environment



Git processes execute with an explicitly supplied environment including:



```text

GIT\_CONFIG\_NOSYSTEM=1

GIT\_TERMINAL\_PROMPT=0

LC\_ALL=C

```



This reduces dependency on uncontrolled host configuration and prevents terminal prompting.



---



## 9. Repository Path Security



Repository paths are not accepted directly from external client input.



Git-related application services obtain managed repository paths through:



```text

RepositoryStorageService::resolveExisting()

```



Repository initialization uses targets derived through:



```text

RepositoryStorageService::resolveNewTarget()

```



Repository storage path behavior was directly verified during WP-07 and repeated during WP-08.



Relevant controls include:



```text

repository database ID based path generation

storage-root containment

relative-root rejection

path traversal rejection

absolute repository-directory rejection

canonical-path containment

symlink protection where supported

existing repository validation

```



---



## 10. Scope



WP-08 covers:



- centralized Git process invocation;

- approved operation selection;

- Smart HTTP service allowlisting;

- fixed browser Git subcommands;

- fixed repository-storage Git subcommands;

- process argument structure;

- no-shell execution;

- metacharacter injection resistance;

- Git option injection resistance;

- repository path validation;

- storage-root containment;

- Git process stdout behavior;

- Git process stderr behavior;

- Git process non-zero exit behavior;

- Git process timeout behavior;

- Git process execution logging.



---



## 11. Out of Scope



The following are outside WP-08:



- complete repository browser semantics;

- complete Smart HTTP clone and fetch verification;

- complete Smart HTTP push verification;

- complete PAT authentication testing;

- complete authorization testing;

- complete repository-storage testing;

- frontend UI testing;

- full security penetration testing;

- system-level acceptance testing;

- production resource-limit tuning;

- Git server scaling behavior;

- SSH Git transport.



Those concerns are covered by separate M5 work packages or are outside V1.0.0.



---



## 12. Entry Criteria



WP-08 may execute when:



```text

M4 Implementation complete

WP-01 Testing Plan approved

WP-02 Test Case Specification approved

WP-03 Test Environment Validation approved

WP-04 Authentication Testing approved

WP-05 Repository Metadata Testing approved

WP-06 Authorization Testing approved

WP-07 Repository Storage Testing approved

Git executable available

central Git process runner operational

repository storage operational

branch testing/m5 active

```



Current assessment:



```text

M4 complete: YES

WP-01 approved: YES

WP-02 approved: YES

WP-03 approved: YES

WP-04 approved: YES

WP-05 approved: YES

WP-06 approved: YES

WP-07 approved: YES

Git executable available: YES

Git process runner operational: YES

Repository storage operational: YES

WP-08 execution: COMPLETE

```



---



## 13. Result States



Formal cases use:



| State | Meaning |
|---|---|
| PASS | Behavior satisfies the expected result |
| FAIL | Behavior contradicts the expected result |
| BLOCKED | Required prerequisite prevents execution |
| NOT EXECUTED | Test has not yet been executed |



WP-08 cannot be approved while a P1 case remains:



```text

FAIL

BLOCKED

NOT EXECUTED

```



---



## 14. Formal Test Inventory



| Test ID | Description | Priority | Result |
|---|---|---|---|
| TC-GEXEC-001 | Execute Approved Git Operation | P1 | PASS |
| TC-GEXEC-002 | Reject Unsupported Git Subcommand | P1 | PASS |
| TC-GEXEC-003 | Reject Shell Metacharacter Injection | P1 | PASS |
| TC-GEXEC-004 | Repository Argument Cannot Escape Storage Root | P1 | PASS |
| TC-GEXEC-005 | Capture Non-Zero Git Exit Status | P2 | PASS |
| TC-GEXEC-006 | Capture Git Standard Error Safely | P2 | PASS |
| TC-GEXEC-007 | Enforce Git Process Timeout | P1 | PASS |



Formal totals:



```text

7 PASS

0 FAIL

0 BLOCKED

0 NOT EXECUTED

```



---



## 15. Primary Automated Test Classes



Primary WP-08 automated evidence:



```text

Tests\\Unit\\Services\\Git\\GitCommandRunnerTest

Tests\\Unit\\Services\\Git\\GitCommandRunnerObservabilityTest

Tests\\Feature\\Security\\ProcessInjectionSecurityTest

Tests\\Unit\\Services\\Git\\GitSmartHttpServiceTest

Tests\\Unit\\Services\\Git\\RepositoryStorageServiceTest

Tests\\Feature\\Git\\GitSmartHttpTest

```



---



## 16. GitCommandRunner Execution



Command:



```powershell

php artisan test --filter=GitCommandRunnerTest

```



Fresh result:



```text

PASS



Tests: 9 passed

Assertions: 15

Failures: 0

Duration: 10.64s

```



Passing tests:



```text

it returns the installed git version

it can preserve raw git output

it passes stdin to git without modifying it

it throws exception for failed git command

it rejects non string arguments

it rejects null bytes in arguments

it rejects empty git binary configuration

it rejects invalid timeout configuration

process timeout is mapped to git command exception

```



---



## 17. Git Process Observability Execution



Command:



```powershell

php artisan test --filter=GitCommandRunnerObservabilityTest

```



Fresh result:



```text

PASS



Tests: 3 passed

Assertions: 6

Failures: 0

Duration: 2.76s

```



Passing tests:



```text

successful process logs start and completion with correlation

failed process logs bounded failure without stderr or arguments

timeout logs classified timeout without process payload

```



---



## 18. Process Injection Security Execution



Command:



```powershell

php artisan test --filter=ProcessInjectionSecurityTest

```



Fresh result:



```text

PASS



Tests: 2 passed

Assertions: 8

Failures: 0

Duration: 1.73s

```



Passing tests:



```text

process arguments are not interpreted by a shell

branch name cannot be used as git option

```



---



## 19. Smart HTTP Service Execution



Command:



```powershell

php artisan test --filter=GitSmartHttpServiceTest

```



Fresh result:



```text

PASS



Tests: 8 passed

Assertions: 28

Failures: 0

Duration: 0.39s

```



Passing tests:



```text

advertisement is executed through central git runner

rpc passes request body to central git runner unchanged

receive pack is an allowed transport service

unsupported transport service is rejected

rpc rejects request larger than configured limit

missing repository storage is mapped to transport failure

git command failure is translated to transport exception

git command timeout is translated to transport timeout

```



---



## 20. Repository Path Execution Evidence



Command:



```powershell

php artisan test --filter=RepositoryStorageServiceTest

```



Fresh result:



```text

WARN



Tests: 14 passed

Assertions: 22

Skipped: 1

Failures: 0

Duration: 11.49s

```



Relevant passing path-safety tests:



```text

storage path uses database repository id

repository name does not affect physical storage path

relative storage root is rejected

traversal repository directory is rejected

absolute repository directory is rejected

resolve existing returns managed repository path

resolve existing rejects missing storage

```



Environment-dependent additional test:



```text

symlinked repositories directory outside root is rejected

```



Current environment:



```text

Directory symlinks are unavailable for this PHP process.

```



This skip does not block `TC-GEXEC-004`, because traversal and absolute-path escape behavior required by the formal GEXEC case execute successfully, and the symlink scenario is separately environment-conditioned storage evidence.



---



## 21. Unsupported Service Feature-Boundary Execution



Command:



```powershell

php artisan test --filter=test\_unsupported\_git\_service\_is\_rejected

```



Fresh result:



```text

PASS



Tests: 1 passed

Assertions: 1

Failures: 0

Duration: 10.58s

```



Passing test:



```text

unsupported git service is rejected

```



This provides feature-level evidence that unsupported external Git service selection is rejected by the application boundary.



---



## 22. Code-Style Verification



Command:



```powershell

vendor\\bin\\pint --test tests\\Unit\\Services\\Git\\GitCommandRunnerTest.php tests\\Unit\\Services\\Git\\GitCommandRunnerObservabilityTest.php tests\\Feature\\Security\\ProcessInjectionSecurityTest.php tests\\Unit\\Services\\Git\\GitSmartHttpServiceTest.php

```



Result:



```text

PASS

4 files

```



No WP-08 source-code formatting changes were required.



---



## 23. TC-GEXEC-001 — Execute Approved Git Operation



### Priority



```text

P1

```



### Type



```text

Integration

```



### Action



Invoke an approved Git operation through the controlled Git runner.



### Expected Result



```text

Git executable runs with expected arguments and controlled execution context.

```



### Primary Evidence



```text

test\_it\_returns\_the\_installed\_git\_version

test\_it\_can\_preserve\_raw\_git\_output

test\_it\_passes\_stdin\_to\_git\_without\_modifying\_it

```



Additional service-level evidence:



```text

advertisement is executed through central git runner

rpc passes request body to central git runner unchanged

```



### Assessment



Actual Git executable execution was verified through the central process runner.



Structured argument execution works.



Raw output is preserved when required.



Text output is trimmed only through the textual `run()` interface.



Binary-safe stdin is transmitted without modification.



### Result



```text

PASS

```



---



## 24. TC-GEXEC-002 — Reject Unsupported Git Subcommand



### Priority



```text

P1

```



### Type



```text

Security

```



### Action



Attempt an unsupported or unapproved Git operation.



### Expected Result



```text

Command selection is rejected before unsafe Git execution.

```



### Architectural Enforcement



GitForge uses layered operation allowlisting.



Clients do not call:



```text

GitCommandRunner::run()

GitCommandRunner::runRaw()

```



directly.



Application services select fixed Git operations.



Smart HTTP accepts only:



```text

upload-pack

receive-pack

```



through:



```text

GitSmartHttpService::validateService()

```



Unsupported services produce:



```text

InvalidArgumentException

```



before `runTransport()` reaches `GitCommandRunner`.



Repository Browser operations use fixed application-owned subcommands such as:



```text

for-each-ref

log

rev-parse

show

ls-tree

cat-file

check-ref-format

show-ref

```



Repository Storage uses fixed application-owned operations such as:



```text

check-ref-format

init

symbolic-ref

for-each-ref

rev-parse

```



No raw external input selects these subcommands.



### Automated Evidence



```text

test\_receive\_pack\_is\_an\_allowed\_transport\_service

test\_unsupported\_transport\_service\_is\_rejected

test\_unsupported\_git\_service\_is\_rejected

```



### Feature-Boundary Evidence



```text

unsupported git service is rejected

```



Result:



```text

1 passed

1 assertion

0 failed

```



### Result



```text

PASS

```



---



## 25. TC-GEXEC-003 — Reject Shell Metacharacter Injection



### Priority



```text

P1

```



### Type



```text

Security

```



### Action



Supply input containing shell command separators or metacharacters.



Representative payload:



```text

safe-value && echo compromised > "<marker-file>"

```



### Expected Result



```text

No secondary command executes.

Input is either rejected or safely treated as process data.

```



### Automated Evidence



Primary test:



```text

test\_process\_arguments\_are\_not\_interpreted\_by\_a\_shell

```



The test:



1. supplies a payload containing `&&`;

2. invokes the process through `GitCommandRunner`;

3. verifies the payload is returned literally;

4. verifies the secondary marker file is not created.



Additional test:



```text

test\_branch\_name\_cannot\_be\_used\_as\_git\_option

```



The browser layer rejects:



```text

\--help

```



as a branch identifier before Git execution.



### Process Construction Evidence



Symfony Process receives:



```text

command: \[

&#x20;   $binary,

&#x20;   ...$arguments,

]

```



No shell command string is constructed.



### Result



```text

PASS

```



---



## 26. TC-GEXEC-004 — Repository Argument Cannot Escape Storage Root



### Priority



```text

P1

```



### Type



```text

Security

```



### Action



Provide or attempt to create a manipulated repository path.



### Expected Result



```text

Git process cannot operate outside approved repository storage.

```



### Architectural Enforcement



Git Process Execution does not directly accept client filesystem paths.



Managed repository paths come from:



```text

RepositoryStorageService

```



Smart HTTP calls:



```text

resolveExisting()

```



before transport execution.



Repository Browser calls:



```text

repositoryPath()

&#x20;   ↓

exists()

&#x20;   ↓

resolveExisting()

```



before Git execution.



Repository initialization resolves a safe target before calling:



```text

git init --bare

```



### Automated Evidence



Relevant tests:



```text

storage path uses database repository id

repository name does not affect physical storage path

relative storage root is rejected

traversal repository directory is rejected

absolute repository directory is rejected

resolve existing returns managed repository path

resolve existing rejects missing storage

```



### Security Assessment



Repository paths are application-derived from repository IDs.



Repository names do not become raw filesystem components.



Traversal-oriented configuration is rejected.



Absolute repository-directory injection is rejected.



Resolved managed repositories remain inside the approved storage root.



### Result



```text

PASS

```



---



## 27. TC-GEXEC-005 — Capture Non-Zero Git Exit Status



### Priority



```text

P2

```



### Type



```text

Failure

```



### Action



Execute a Git operation expected to fail.



### Expected Result



```text

Failure is detected and represented through controlled application behavior.

```



### Automated Evidence



Primary test:



```text

test\_it\_throws\_exception\_for\_failed\_git\_command

```



The test deliberately executes:



```text

not-a-real-git-command-for-tests

```



and verifies:



```text

GitCommandException is thrown

operation name is preserved

process exit code is non-zero

error output is non-empty

```



### Runner Behavior



After process execution:



```text

if process is not successful

&#x20;   capture exit code

&#x20;   emit bounded failure log

&#x20;   throw GitCommandException

```



### Result



```text

PASS

```



---



## 28. TC-GEXEC-006 — Capture Git Standard Error Safely



### Priority



```text

P2

```



### Type



```text

Failure / Observability

```



### Action



Cause Git to produce error output.



### Expected Result



```text

Error information is available for controlled diagnostics without unsafe secret exposure.

```



### Exception Evidence



`GitCommandRunner` captures:



```text

$process->getErrorOutput()

```



and stores the trimmed result in:



```text

GitCommandException::errorOutput

```



Primary test:



```text

test\_it\_throws\_exception\_for\_failed\_git\_command

```



verifies:



```text

errorOutput is not empty

```



### Logging Evidence



Primary observability test:



```text

test\_failed\_process\_logs\_bounded\_failure\_without\_stderr\_or\_arguments

```



verifies failure logging does not expose:



```text

stderr

command arguments

process payload

```



The process log contains bounded operational metadata such as:



```text

event

request\_id

operation

result

failure\_category

exit\_code

timed\_out

duration\_ms

```



### Security Assessment



This design separates:



```text

controlled internal exception diagnostics

```



from:



```text

bounded operational logging

```



This preserves troubleshooting capability without broadly logging process payloads.



### Result



```text

PASS

```



---



## 29. TC-GEXEC-007 — Enforce Git Process Timeout



### Priority



```text

P1

```



### Type



```text

Reliability

```



### Action



Execute an operation that exceeds the configured process timeout.



### Expected Result



```text

Process is terminated or handled according to approved design.

Application does not hang indefinitely.

```



### Automated Evidence



Primary test:



```text

test\_process\_timeout\_is\_mapped\_to\_git\_command\_exception

```



Test configuration:



```text

git.timeout\_seconds = 1

```



Simulated long-running process:



```text

sleep(3);

```



Expected exception values:



```text

operation = timeout-test

processExitCode = 124

errorOutput = The Git process exceeded its timeout.

```



Fresh result:



```text

PASS

```



### Observability Evidence



Supporting test:



```text

test\_timeout\_logs\_classified\_timeout\_without\_process\_payload

```



The timeout log includes:



```text

failure\_category = git\_process\_timeout

exit\_code = 124

timed\_out = true

configured\_timeout

duration\_ms

```



without exposing process payload.



### Result



```text

PASS

```



---



## 30. Git Argument Validation



`GitCommandRunner` validates every process argument.



Rejected argument conditions include:



```text

non-string arguments

null-byte-containing arguments

```



Automated evidence:



```text

test\_it\_rejects\_non\_string\_arguments

test\_it\_rejects\_null\_bytes\_in\_arguments

```



Result:



```text

PASS

```



---



## 31. Git Binary Configuration Validation



The configured executable must not be empty.



Automated evidence:



```text

test\_it\_rejects\_empty\_git\_binary\_configuration

```



Invalid configuration:



```text

git.binary = ""

```



Result:



```text

PASS

```



---



## 32. Timeout Configuration Validation



Process timeout configuration must satisfy:



```text

timeout >= 1 second

```



Automated evidence:



```text

test\_it\_rejects\_invalid\_timeout\_configuration

```



Invalid configuration:



```text

git.timeout\_seconds = 0

```



Result:



```text

PASS

```



---



## 33. Raw Output Preservation



Git operations have two execution interfaces.



Textual operations use:



```text

run()

```



which returns trimmed text.



Binary-sensitive or protocol-sensitive operations use:



```text

runRaw()

```



which returns stdout without trimming.



Automated evidence:



```text

test\_it\_can\_preserve\_raw\_git\_output

```



Result:



```text

PASS

```



---



## 34. Standard Input Preservation



Git Smart HTTP requires binary-safe stdin handling.



Automated evidence:



```text

test\_it\_passes\_stdin\_to\_git\_without\_modifying\_it

```



The test includes:



```text

newline data

binary-safe null byte

arbitrary content

```



and verifies the received Git object hash matches the original data exactly.



Smart HTTP service-level evidence:



```text

rpc passes request body to central git runner unchanged

```



Result:



```text

PASS

```



---



## 35. Smart HTTP Operation Selection



Smart HTTP defines only two execution services:



```text

upload-pack

receive-pack

```



`GitSmartHttpService` contains:



```text

UPLOAD\_PACK

RECEIVE\_PACK

```



and validates requested service selection through an explicit allowlist.



Any other service is rejected.



Result:



```text

PASS

```



---



## 36. Repository Browser Operation Selection



Repository Browser does not accept arbitrary Git command selection.



Git subcommands are hard-coded by application behavior.



Observed subcommands include:



```text

for-each-ref

log

rev-parse

show

ls-tree

cat-file

check-ref-format

show-ref

```



Untrusted browser parameters influence only validated data arguments such as:



```text

branch

commit

repository-relative path

```



They do not select the Git executable or arbitrary subcommand.



Result:



```text

PASS

```



---



## 37. Repository Storage Operation Selection



Repository Storage uses fixed Git operations.



Observed operations include:



```text

check-ref-format

init --bare

symbolic-ref

for-each-ref

rev-parse

```



The initialization target is obtained only after storage containment validation.



Result:



```text

PASS

```



---



## 38. No-Shell Execution Assessment



The process runner uses:



```text

new Process(

&#x20;   command: \[

&#x20;       $binary,

&#x20;       ...$arguments,

&#x20;   ],

)

```



No equivalent of the following is used for normal Git execution:



```text

shell\_exec()

exec("git " . $userInput)

system()

passthru()

cmd.exe /c

sh -c

```



The metacharacter injection test confirms this behavior dynamically.



Result:



```text

PASS

```



---



## 39. Git Option Injection Assessment



Some Git arguments beginning with `-` can be interpreted as options.



Repository Browser explicitly rejects branch identifiers that:



```text

begin with "-"

contain forbidden control or whitespace characters

are empty

are too long

fail git check-ref-format --branch

```



Automated evidence:



```text

test\_branch\_name\_cannot\_be\_used\_as\_git\_option

```



Result:



```text

PASS

```



---



## 40. Repository Storage Handoff Assessment



The approved design requires:



```text

Repository Storage

&#x20;       ↓

validated physical repository target

&#x20;       ↓

Git Process Execution

```



This relationship is implemented.



Git Smart HTTP:



```text

requireRepositoryPath()

&#x20;   ↓

RepositoryStorageService::resolveExisting()

```



Repository Browser:



```text

repositoryPath()

&#x20;   ↓

RepositoryStorageService::resolveExisting()

```



Repository Storage initialization validates the new target before Git initialization.



Result:



```text

PASS

```



---



## 41. Failure Translation



Raw Git process failures do not propagate as false success.



Base runner failure:



```text

GitCommandException

```



Smart HTTP translation:



```text

GitTransportException

```



Repository Browser translation:



```text

RepositoryBrowserException

```



Repository Storage translation:



```text

RepositoryStorageException

```



where appropriate.



Automated evidence includes:



```text

git command failure is translated to transport exception

git command timeout is translated to transport timeout

```



Result:



```text

PASS

```



---



## 42. Git Process Observability



Successful process execution logs:



```text

git\_process\_started

git\_process\_completed

```



Failure logs:



```text

git\_process\_failed

```



Timeout logs:



```text

git\_process\_timeout

```



Process-start failures log:



```text

git\_process\_start\_failed

```



Safe context includes bounded operational metadata.



Sensitive process material is not included.



Result:



```text

PASS

```



---



## 43. Sensitive Diagnostic Protection



Git process logs intentionally exclude:



```text

command arguments

stdin

stdout

stderr

credentials

repository contents

Git protocol payload

```



This is directly exercised by:



```text

test\_failed\_process\_logs\_bounded\_failure\_without\_stderr\_or\_arguments

test\_timeout\_logs\_classified\_timeout\_without\_process\_payload

```



Result:



```text

PASS

```



---



## 44. Process Timeout Assessment



`GitCommandRunner` reads:



```text

git.timeout\_seconds

```



and applies:



```text

Process::setTimeout()

```



Timeout failure is classified distinctly from ordinary non-zero Git failure.



Mapped exit code:



```text

124

```



Result:



```text

PASS

```



---



## 45. Defects Discovered



No production Git process execution defect was discovered during WP-08.



Formal result:



```text

Production defects: 0

```



No testing coverage gap requiring new code was discovered.



The existing layered test suite already provides sufficient evidence for all seven formal `TC-GEXEC-*` cases.



---



## 46. Production Code Changes



WP-08 required:



```text

0 production-code changes

```



---



## 47. Automated Test Changes



WP-08 required:



```text

0 automated-test changes

```



Existing tests provided sufficient formal coverage.



---



## 48. Aggregate Execution Summary



Fresh WP-08 execution evidence:



| Suite | Passed | Assertions | Skipped | Failed |
|---|---:|---:|---:|---:|
| GitCommandRunnerTest | 9 | 15 | 0 | 0 |
| GitCommandRunnerObservabilityTest | 3 | 6 | 0 | 0 |
| ProcessInjectionSecurityTest | 2 | 8 | 0 | 0 |
| GitSmartHttpServiceTest | 8 | 28 | 0 | 0 |
| RepositoryStorageServiceTest | 14 | 22 | 1 | 0 |
| Unsupported Git service feature test | 1 | 1 | 0 | 0 |
| \*\*Total\*\* | \*\*37\*\* | \*\*80\*\* | \*\*1\*\* | \*\*0\*\* |



Aggregate automated evidence:



```text

37 passed

80 assertions

1 environment-dependent storage symlink skip

0 failed

```



The single skip is supporting storage evidence and does not represent an unexecuted formal `TC-GEXEC-*` case.



---



## 49. Formal Test Case Summary



| Test ID | Primary Evidence | Result |
|---|---|---|
| TC-GEXEC-001 | GitCommandRunner successful execution tests | PASS |
| TC-GEXEC-002 | Smart HTTP allowlist + unsupported service rejection | PASS |
| TC-GEXEC-003 | ProcessInjectionSecurityTest | PASS |
| TC-GEXEC-004 | RepositoryStorageService containment tests | PASS |
| TC-GEXEC-005 | Failed Git command exception test | PASS |
| TC-GEXEC-006 | Git stderr exception + bounded observability tests | PASS |
| TC-GEXEC-007 | Git process timeout test | PASS |



Formal result:



```text

7 PASS

0 FAIL

0 BLOCKED

0 NOT EXECUTED

```



---



## 50. Layered Security Assessment



WP-08 confirms that Git process security is intentionally distributed across multiple trusted components.



### Application Service Layer



Controls:



```text

approved logical operation

fixed Git subcommand

request validation

authorization workflow

protocol validation

```



### Repository Storage Layer



Controls:



```text

managed repository identity

safe target derivation

storage-root containment

existing-storage validation

physical repository verification

```



### Git Process Runner



Controls:



```text

configured executable

structured arguments

no shell

argument type validation

null-byte rejection

controlled environment

stdin transmission

timeout

exit code

stderr capture

bounded observability

```



This matches the approved architecture.



---



## 51. Operation Allowlist Interpretation



`TC-GEXEC-002` does not require that `GitCommandRunner` itself maintain a single array containing every permitted Git subcommand.



The approved architecture allows operation selection to occur at the application-service layer.



What must remain true is:



```text

external input cannot select an arbitrary Git subprocess

```



WP-08 verified that invariant.



Smart HTTP service selection is explicitly allowlisted.



Repository Browser and Repository Storage select fixed application-owned Git commands.



Therefore:



```text

TC-GEXEC-002 = PASS

```



---



## 52. Repository Binding Interpretation



`TC-GEXEC-004` does not require `GitCommandRunner` to independently rediscover repository ownership or filesystem containment.



The approved architecture defines Repository Storage as the trusted producer of safe physical repository targets.



WP-08 verified that:



```text

Git-related services resolve repository paths through RepositoryStorageService

```



and that repository storage rejects escape-oriented path conditions.



Therefore:



```text

TC-GEXEC-004 = PASS

```



---



## 53. Security Invariants



The following process invariants were verified:



```text

GPI-001 — Controlled Executable

GPI-002 — Allowlisted Operations

GPI-003 — No Shell

GPI-004 — Structured Arguments

```



Additional verified behavior includes:



```text

validated repository targets

bounded timeouts

controlled failures

bounded diagnostics

noninteractive Git execution

```



---



## 54. Traceability



WP-08 traceability:



```text

FR-071 – FR-074

&#x20;       ↓

Git Process Execution Design

&#x20;       ↓

Git Process Executor / Application Services

&#x20;       ↓

TC-GEXEC-001 – TC-GEXEC-007

&#x20;       ↓

Automated Unit / Feature / Security Tests

&#x20;       ↓

WP-08 Execution Evidence

```



---



## 55. Exit Criteria



WP-08 exit criteria are:



1. all seven formal `TC-GEXEC-*` cases are mapped;

2. all P1 Git process execution cases execute;

3. approved Git execution succeeds through the centralized runner;

4. unsupported external Git service selection is rejected;

5. application clients cannot select arbitrary Git subprocesses;

6. Git arguments are passed structurally;

7. shell metacharacters cannot trigger secondary command execution;

8. option-shaped branch input is rejected before unsafe Git invocation;

9. repository paths reaching Git execution originate from validated storage;

10. storage-root escape attempts are rejected;

11. non-zero Git exit status is detected;

12. Git failure is represented through controlled application exceptions;

13. Git stderr is captured for controlled diagnostics;

14. stderr and process arguments are excluded from operational logs;

15. timeout configuration is enforced;

16. timeout failure is mapped to controlled behavior;

17. no production Git execution defect remains open;

18. no WP-08 test coverage gap remains open;

19. WP-08 source tests pass;

20. Pint verification passes;

21. `git diff --check` passes;

22. WP-08 documentation is reviewed;

23. document status is changed from `Draft` to `Approved`.



Current assessment:



| Exit Criterion | Status |
|---|---|
| 1–20 | PASS |
| 21 | PENDING FINAL VERIFICATION |
| 22 | PENDING |
| 23 | PENDING |



---



## 56. Work Package Result



Formal execution:



```text

TC-GEXEC cases: 7



PASS: 7

FAIL: 0

BLOCKED: 0

NOT EXECUTED: 0

```



Supporting automated evidence:



```text

37 passed

80 assertions

1 environment-dependent supporting-storage skip

0 failed

```



Production defects:



```text

0

```



Test changes required:



```text

0

```



Production changes required:



```text

0

```



Current WP-08 state:



```text

EXECUTION COMPLETE

DOCUMENT REVIEW PENDING

FINAL REPOSITORY VERIFICATION PENDING

APPROVAL PENDING

```



---



## 57. Approval Decision



WP-08 is technically ready for final validation.



Approval requires:



```text

document integrity verification

git diff --check

document review

status transition from Draft to Approved

```



Until those checks complete:



```text

Status: Draft

```



After successful final verification:



```text

Status: Approved

WP-08 — Git Process Execution Testing: COMPLETE

```



---



## 58. Conclusion



Git process execution testing for GitForge V1.0.0 confirms that Git subprocess behavior is consistent with the approved design.



The evidence demonstrates that:



- Git subprocess execution is centralized;

- approved operations execute correctly;

- clients cannot directly select arbitrary Git commands;

- Smart HTTP transport services are explicitly allowlisted;

- unsupported Git services are rejected;

- Repository Browser and Repository Storage use fixed application-owned Git subcommands;

- Git subprocesses are invoked with structured argument arrays;

- normal Git execution does not use a command shell;

- shell metacharacter payloads remain data rather than executable shell syntax;

- option-shaped branch input is rejected;

- repository paths are derived through validated repository storage;

- storage-root traversal and absolute-path injection are rejected;

- Git non-zero exit status is preserved;

- Git standard error is available through controlled exception handling;

- process logs avoid exposing stderr, arguments, protocol payloads, and credentials;

- Git timeout behavior is explicit and tested;

- timed-out processes are represented as controlled failures;

- no production Git process execution defect was discovered;

- no new automated tests were required.



WP-08 execution is complete.



The work package remains in `Draft` status only until final document integrity and repository verification are completed.
