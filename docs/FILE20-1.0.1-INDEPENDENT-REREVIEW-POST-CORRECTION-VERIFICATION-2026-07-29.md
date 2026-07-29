# File 20 v1.0.1 Independent Re-review Post-Correction Verification — 2026-07-29

## Purpose

This verification follows the independent re-review and the recorded corrective implementation. It verifies the corrected source and automated evidence; it does not replace controlled Files 00/20/21/22 staging.

## Exact corrected runtime head

`07564e33332a478dc7ef81a7e5b3611083aa970f`

## Verified corrections

- `header.enabled = false` and `header.create = false` close both desktop and mobile Create before File 22 authorization executes.
- Logged-out, roleless, missing-subject, missing-`edit_posts`, File 20 Safe Mode, constant disable, and Emergency Disable states remain non-overridable.
- Authorized requests preserve the configured mobile `create`, `auto`, or `doctors` presentation; denied requests use Doctors for that request without persisting a settings change.
- The File 20 producer contract is exact version `1.0.1` and includes explicit owner and locally acquired function-ownership proof.
- Any preclaimed producer function or contract marker prevents File 20 from defining a mixed/partial producer. A dedicated collision regression proves the foreign producer is not overwritten and the missing companion function is not defined.
- File 21's corrective branch requires the exact File 20 `1.0.1` version, owner, ownership flag, producer functions, readiness, and current-user visibility before removing its native fallback.
- The final Create URL is checked at maximum WordPress filter priority and again by the read-only URL resolver. Only credential-free HTTPS URLs matching the site's host and effective port are accepted; unsafe values use only the validated native admin fallback.
- System Check reports contract version, owner/collision state, producer functions, readiness, centrally resolved current-user visibility, and final URL health.
- Build and test jobs explicitly check out and verify the pull-request branch head rather than GitHub's synthetic merge commit.
- Every shipped production PHP file is syntax-linted on PHP 7.4.
- The pinned File 20/File 22 workflow reruns for every File 20 source dependency that can change the contract and is described as hybrid evidence rather than complete integration.
- The staging checklist covers Files 00/20/21/22 load order, roles/status/documents, Header/mobile parity, dependency absence/incompatibility/Safe Mode/collision, File 21 fallback, caches, browsers, accessibility, Urdu RTL, backup, and rollback.
- Candidate ZIP entries are sorted, use fixed timestamps and normalized attributes, and are rebuilt twice in one workflow with byte-for-byte ZIP and checksum comparison.

## Exact-head File 20 automated evidence

All workflow families completed successfully on the unchanged corrected runtime head:

1. **Build and Test Plugin**
   - run number: `120`;
   - run ID: `30444651136`;
   - conclusion: `success`;
   - exact-head checkout: passed;
   - PHP/JavaScript syntax, complete static and behavioral suite, collision regression, deterministic double build, ZIP integrity, checksum, and artifact upload: passed.

2. **PHP 7.4 production package compatibility**
   - job within run `30444651136`;
   - exact-head checkout: passed;
   - bootstrap, uninstall, and every shipped PHP file under `includes/` and `admin/`: passed.

3. **File 20 and File 21 Slot Contracts**
   - run number: `61`;
   - run ID: `30444651187`;
   - conclusion: `success`.

4. **File 20 Minimal Smoke**
   - run number: `55`;
   - run ID: `30444651147`;
   - conclusion: `success`.

5. **File 20 File 22 Pinned Hybrid Shell Contract**
   - run number: `27`;
   - run ID: `30444651115`;
   - conclusion: `success`;
   - PHP 8.1 and PHP 8.3 matrix: passed.

## Candidate artifact evidence

- artifact ID: `8720894139`;
- artifact name: `sabri-unified-application-shell-1.0.1-candidate`;
- outer GitHub artifact digest: `sha256:be89be77ac4b98a7df5dc8e03079cf0fd3a0d63f1e1f14f039826c3d5d9989b7`;
- inner installable ZIP SHA-256: `397123a13089db0dcac4f0dacb104a874b80a195cedea02836e3b7e7b0a6da16`;
- embedded `.sha256` value matched the independently calculated installable-ZIP hash;
- ZIP CRC test: passed;
- test report contained no failed check.

The build workflow itself rebuilt the candidate twice and compared both the installable ZIP and `.sha256` file byte-for-byte.

## File 21 compatibility evidence

File 21 corrective head:

`a9bf99ab0169dd56fe664f6a1540a5de6031d518`

All eight File 21 workflow families passed after requiring File 20 contract `1.0.1` and its owner/ownership proof:

- Build and Test Home News Feed — run `1576`, ID `30443269432`;
- Test Packaged Home News Feed — run `1053`, ID `30443269607`;
- File 21 Corrective Release Tests — run `212`, ID `30443272406`;
- File 21 Comprehensive Harmonization Tests — run `202`, ID `30443271224`;
- Phase 4A Content Model Tests — run `529`, ID `30443269488`;
- Phase 4B Newsroom Tests — run `279`, ID `30443270271`;
- Phase 4C Public News Tests — run `243`, ID `30443269565`;
- File 21 File 22 Real Contract — run `27`, ID `30443269454`.

## Historical failures retained

The correction process did not ignore failed evidence. Fresh runs detected and led to correction of:

- a PHP interpolation parse error in a static assertion;
- stale static expectation for contract `1.0.0`;
- a remote-dependency scanner that included test/documentation fixture URLs;
- incomplete contract-marker collision ownership logic;
- admin System Check visibility being limited to the legacy role list.

Each finding was corrected before the successful exact-head evidence above.

## Scope conclusion

No additional known code-level blocker remains within the independently re-reviewed File 20 v1.0.1 Create producer, File 21 slot, package, and pinned-hybrid contract scope at the reviewed runtime head.

This conclusion does **not** authorize merge, staging promotion, production release, or live deployment. PR #4 must remain Draft and unmerged until File 22 Phase 22E and File 21 dependency order is approved, Files 00/20/21/22 pass controlled Hostinger staging, the complete role/status/document/fallback/browser/accessibility/RTL matrix passes, backup and rollback proof exists, and the Founder explicitly authorizes merge.
