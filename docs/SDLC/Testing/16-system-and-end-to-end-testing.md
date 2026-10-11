# System and End-to-End Testing



| Field | Value |
|---|---|
| Project | GitForge — Web Git Hosting Platform |
| Release | V1.0.0 |
| Milestone | M5 — Testing |
| SDLC phase | Testing |
| Work package | WP-16 — System and End-to-End Testing |
| Status | Approved |
| Last updated | 2026-08-22 |



---



## 1. Purpose



This document records the system-level and end-to-end verification results for GitForge V1.0.0.



The objective of WP-16 is to verify that complete integrated workflows operate correctly across:



- authentication;

- browser sessions;

- repository metadata;

- managed Git repository storage;

- personal access tokens;

- Git Smart HTTP read operations;

- Git Smart HTTP write operations;

- repository browser APIs;

- authorization boundaries;

- token revocation.



The tests intentionally cross component boundaries and verify complete user and Git workflows rather than isolated endpoints or services.



---



## 2. Scope



WP-16 covers the formal system and end-to-end test cases:



- `TC-E2E-001`

- `TC-E2E-002`

- `TC-E2E-003`

- `TC-E2E-004`

- `TC-E2E-005`

- `TC-E2E-006`

- `TC-E2E-007`



The dedicated WP-16 suite is:



`backend/tests/Feature/System/SystemEndToEndTest.php`



The suite uses:



- the Laravel application;

- a temporary file-backed SQLite database;

- temporary managed Git repository storage;

- temporary Git workspaces;

- a dynamically allocated localhost port;

- a temporary Laravel HTTP server;

- the real system Git client;

- real Git Smart HTTP clone, fetch, and push operations;

- real V1 API requests;

- real application authorization and PAT verification.



---



## 3. Formal Test Case Results



| Test Case | Description | Priority | Result |
|---|---|---:|---|
| TC-E2E-001 | Registration to Repository Creation Flow | P1 | PASS |
| TC-E2E-002 | Public Repository Create, Push, Browse, Clone Flow | P1 | PASS |
| TC-E2E-003 | Private Repository Owner Workflow | P1 | PASS |
| TC-E2E-004 | Private Repository Unauthorized User Workflow | P1 | PASS |
| TC-E2E-005 | PAT Revocation End-to-End | P1 | PASS |
| TC-E2E-006 | Push-to-Browser Consistency | P1 | PASS |
| TC-E2E-007 | Logout Terminates Browser Session Access | P2 | PASS |



All seven formal WP-16 cases passed.



---



## 4. Test Environment



The WP-16 suite creates an isolated environment for every test.



The environment includes:



- a unique temporary root directory;

- a file-backed SQLite test database;

- a dedicated repository-storage root;

- isolated local Git workspaces;

- a dynamically allocated HTTP port;

- a temporary `php artisan serve` process;

- the system Git executable.



The PHPUnit application and temporary Laravel HTTP server share:



- the same SQLite database file;

- the same managed repository-storage root.



This allows API operations performed in PHPUnit to create repository metadata and PAT state that are immediately visible to the separate Laravel process serving real Git Smart HTTP requests.



The temporary server uses:



- `APP_ENV=testing`;

- `APP_DEBUG=false`;

- SQLite;

- array cache;

- array session storage;

- synchronous queue execution;

- the system Git binary;

- isolated GitForge repository storage.



Git processes are executed with:



`GIT_TERMINAL_PROMPT=0`



This prevents interactive credential prompts from masking authentication failures.



---



## 5. TC-E2E-001 — Registration to Repository Creation Flow



Formal procedure:



`Register user → authenticate → create repository → retrieve repository`



Dedicated test:



`test_registration_to_repository_creation_flow`



The workflow performs:



1. user registration through the V1 API;

2. verification that registration establishes the browser session;

3. authenticated profile retrieval;

4. private repository creation through the V1 API;

5. repository metadata verification;

6. managed Git storage verification;

7. retrieval of the same repository through the repository API.



Verification confirms that:



- registration succeeds;

- the user record is persisted;

- the browser session is authenticated;

- protected profile access succeeds;

- repository creation succeeds;

- repository ownership is correct;

- normalized repository metadata is persisted;

- repository visibility is private;

- managed bare Git storage exists;

- the repository can be retrieved by the authenticated owner;

- API metadata matches persisted metadata.



Result:



    PASS



---



## 6. TC-E2E-002 — Public Repository Create, Push, Browse, Clone Flow



Formal procedure:



`Create public repository → create PAT → clone/push initial commit → browse repository → clone anonymously`



Dedicated test:



`test_public_repository_create_push_browse_clone_flow`



The workflow performs:



1. authenticated public repository creation through the API;

2. PAT creation through the API;

3. real anonymous clone of the empty public repository;

4. local README creation and commit;

5. authenticated Git Smart HTTP push using the PAT;

6. direct verification of the remote `main` ref;

7. repository commit-history verification through the browser API;

8. a second anonymous real Git clone;

9. verification of cloned commit identity and file content.



Verification confirms that:



- public repository metadata and storage are created consistently;

- PAT creation succeeds;

- the PAT authorizes repository write access for the owner;

- a real Git client can push the initial commit;

- managed bare storage advances to the pushed commit;

- the repository browser exposes the same commit;

- anonymous read access succeeds for the public repository;

- the final anonymous clone contains the same pushed commit;

- file content remains consistent across Git write, browser read, and Git clone operations.



Result:



    PASS



---



## 7. TC-E2E-003 — Private Repository Owner Workflow



Formal procedure:



`Create private repository → create valid PAT → push → browse while authenticated → clone with PAT`



Dedicated test:



`test_private_repository_owner_workflow`



The workflow performs:



1. private repository creation through the API;

2. PAT creation through the API;

3. authenticated real Git clone using the owner PAT;

4. local commit creation;

5. authenticated Git Smart HTTP push;

6. authenticated repository metadata retrieval;

7. authenticated repository-browser blob retrieval;

8. a second authenticated clone using the same valid PAT;

9. verification of commit and file consistency.



Verification confirms that the repository owner can perform all supported authorized operations against a private repository.



Result:



    PASS



---



## 8. TC-E2E-004 — Private Repository Unauthorized User Workflow



Formal procedure:



`Second user attempts metadata read, browser access, clone, and push against private repository`



Dedicated test:



`test_private_repository_unauthorized_user_workflow`



The workflow first creates and seeds a private repository using its legitimate owner.



A second user then:



1. creates their own valid PAT;

2. attempts repository metadata access;

3. attempts repository-browser commit access;

4. attempts real Git clone using their valid PAT;

5. attempts real Git push using their valid PAT.



Verification confirms that:



- private repository metadata is concealed from the non-owner;

- repository-browser access is concealed from the non-owner;

- authenticated Git clone is rejected even though the PAT itself is valid;

- no usable unauthorized clone is created;

- authenticated Git push is rejected;

- repository refs remain unchanged after the unauthorized push;

- the legitimate repository state remains intact.



The test therefore verifies authorization based on both identity and repository ownership.



Result:



    PASS



---



## 9. TC-E2E-005 — PAT Revocation End-to-End



Formal procedure:



`Create token → verify Git access → revoke token → repeat Git access`



Dedicated test:



`test_pat_revocation_end_to_end`



The workflow performs:



1. private repository creation;

2. PAT creation through the API;

3. authenticated clone using the new PAT;

4. local commit creation;

5. authenticated push using the PAT;

6. verification that Git access succeeds before revocation;

7. PAT revocation through the V1 API;

8. persistence verification of `revoked_at`;

9. authenticated Git fetch using the same revoked PAT;

10. authenticated Git clone using the same revoked PAT.



Verification confirms that:



- the PAT provides Git access before revocation;

- revocation is performed through the supported API;

- the persisted token is marked revoked;

- the same credential immediately loses Git access;

- fetch fails after revocation;

- clone fails after revocation;

- no usable clone is created with the revoked token.



Result:



    PASS



---



## 10. TC-E2E-006 — Push-to-Browser Consistency



Formal procedure:



`Push new commit → query commit history → open commit → browse changed file`



Dedicated test:



`test_push_to_browser_consistency`



The workflow performs:



1. public repository creation;

2. PAT creation;

3. real Git clone;

4. README modification;

5. local commit creation;

6. authenticated push;

7. direct remote ref verification;

8. commit-history API request;

9. commit-detail API request;

10. repository-tree API request;

11. repository-blob API request.



Verification confirms that the same pushed commit is visible consistently through:



- managed bare Git storage;

- repository commit history;

- commit detail;

- repository tree;

- repository blob content.



The verified values include:



- commit hash;

- commit subject/message;

- branch;

- file name;

- Git object type;

- file path;

- text encoding;

- file content.



Result:



    PASS



---



## 11. TC-E2E-007 — Logout Terminates Browser Session Access



Formal procedure:



`Login → access protected page → logout → revisit protected page/API`



Dedicated test:



`test_logout_terminates_browser_session_access`



The workflow performs:



1. user login through the V1 API;

2. authenticated-session verification;

3. protected `/auth/me` access;

4. logout through the V1 API;

5. guest-session verification;

6. repeated `/auth/me` access;

7. attempted protected repository creation after logout.



Verification confirms that:



- login establishes browser authentication;

- protected access succeeds before logout;

- logout succeeds;

- the browser session becomes unauthenticated;

- protected profile access returns unauthorized after logout;

- protected repository creation returns unauthorized after logout;

- no repository is persisted after the unauthorized attempt.



Result:



    PASS



---



## 12. Real Git Client Coverage



The WP-16 suite does not simulate Git transport using controller-only requests.



It executes the installed Git client through Symfony Process.



Representative operations include:



    git clone

    git push

    git fetch

    git rev-parse

    git show

    git show-ref

    git init

    git remote add

    git add

    git commit



Git Smart HTTP authentication uses an HTTP Basic Authorization header containing:



- the GitForge username;

- the generated GitForge PAT.



The real Git client therefore exercises the same HTTP Git transport endpoints used by an external Git client.



---



## 13. Repository Integrity Verification



WP-16 verifies repository integrity at multiple levels.



For successful workflows:



- local commit identity is resolved;

- remote `refs/heads/main` is resolved;

- pushed commit IDs are compared;

- cloned commit IDs are compared;

- cloned Git object content is compared with expected content;

- repository-browser content is compared with the same pushed content.



For unauthorized write attempts:



- the complete repository ref set is captured before the operation;

- the unauthorized push is attempted;

- the complete repository ref set is captured again;

- both snapshots must match exactly.



This verifies that rejected write operations do not alter repository refs.



---



## 14. Initial Test Harness Finding



The first WP-16 execution produced:



    Tests:    5 failed, 2 passed (118 assertions)

    Duration: 23.47s



The five failures shared the same Git client error:



    error: src refspec main does not match any



The affected workflows were the cases that performed an initial push into a newly created empty repository.



The failure was caused by the test helper command:



    git push origin main



A clone of an empty bare repository does not guarantee that the local workspace already contains a local branch named `main`.



Therefore the helper incorrectly assumed the existence of a local `main` ref.



This was a test-harness defect, not a GitForge production defect.



The helper was corrected to push the current local commit explicitly to the GitForge `main` branch:



    git push origin HEAD:refs/heads/main



No production code was modified.



After this correction, all seven formal workflows passed.



---



## 15. Functional Test Result



After correcting the E2E push helper, the dedicated suite produced:



    PASS  Tests\Feature\System\SystemEndToEndTest



    ✓ registration to repository creation flow

    ✓ public repository create push browse clone flow

    ✓ private repository owner workflow

    ✓ private repository unauthorized user workflow

    ✓ pat revocation end to end

    ✓ push to browser consistency

    ✓ logout terminates browser session access



Result:



    Tests:    7 passed (188 assertions)

    Failures: 0

    Duration: 43.25s



---



## 16. Code Style Verification



Initial Pint verification identified formatting issues in:



`backend/tests/Feature/System/SystemEndToEndTest.php`



The reported categories included:



- line ending normalization;

- unary operator spacing;

- not-operator spacing.



Pint corrected the file.



Final verification:



    vendor\bin\pint --test tests\Feature\System\SystemEndToEndTest.php



Result:



    PASS

    1 file



No remaining PHP style violation was reported.



---



## 17. Final Post-Formatting Regression Run



After Pint formatting, the complete WP-16 suite was executed again.



Final result:



    Tests:    7 passed (188 assertions)

    Failures: 0

    Duration: 44.29s



This confirms that code-style normalization did not alter test behavior.



---



## 18. Changes Introduced by WP-16



WP-16 adds one dedicated automated system-test suite:



`backend/tests/Feature/System/SystemEndToEndTest.php`



The suite adds direct formal evidence for:



- `TC-E2E-001`

- `TC-E2E-002`

- `TC-E2E-003`

- `TC-E2E-004`

- `TC-E2E-005`

- `TC-E2E-006`

- `TC-E2E-007`



No production application code was changed.



No existing Git Smart HTTP E2E test file required modification.



No V1.0.0 functional scope was expanded.



---



## 19. Defects and Observations



No blocking V1.0.0 production defect was identified during WP-16.



One test-harness issue was identified during the initial execution:



- the initial-push helper assumed a local `main` branch existed after cloning an empty repository.



The harness was corrected to push:



`HEAD:refs/heads/main`



This correction reflects the intended remote branch explicitly and does not modify production behavior.



All seven formal system/E2E cases subsequently passed.



---



## 20. Traceability



| Test Case | Dedicated Automated Evidence |
|---|---|
| TC-E2E-001 | `test_registration_to_repository_creation_flow` |
| TC-E2E-002 | `test_public_repository_create_push_browse_clone_flow` |
| TC-E2E-003 | `test_private_repository_owner_workflow` |
| TC-E2E-004 | `test_private_repository_unauthorized_user_workflow` |
| TC-E2E-005 | `test_pat_revocation_end_to_end` |
| TC-E2E-006 | `test_push_to_browser_consistency` |
| TC-E2E-007 | `test_logout_terminates_browser_session_access` |



All evidence is contained in:



`backend/tests/Feature/System/SystemEndToEndTest.php`



---



## 21. WP-16 Acceptance Assessment



WP-16 exit conditions are satisfied.



Evidence confirms that:



- a user can register and immediately operate through an authenticated browser session;

- repository metadata and managed Git storage are created consistently;

- public repositories support authenticated push and anonymous clone;

- private repository owners can complete authorized API, browser, clone, and push workflows;

- non-owners are denied or concealed across private metadata, browser, clone, and push operations;

- PAT access works before revocation and fails after revocation;

- pushed Git state is represented consistently by repository browser APIs;

- logout terminates browser-session access to protected resources;

- real system Git clients successfully exercise Git Smart HTTP;

- rejected unauthorized writes do not alter repository refs;

- all seven formal E2E cases pass;

- 188 assertions pass;

- no blocking V1.0.0 system-integration defect remains open.



Therefore:



> WP-16 — System and End-to-End Testing is complete and approved for GitForge V1.0.0.
