# File 20 v1.0.1 Create Producer Post-Implementation Review — 2026-07-29

## Review boundary

This is a separate post-implementation review of Draft PR #4 after the complete File 20 source, File 21 placement slots, and File 22 Create producer were consolidated. It reviews authorization, desktop/mobile parity, Safe Mode, request-time settings behavior, versioning, packaging, regression coverage, rollback boundaries, and cross-plugin assumptions.

Reviewed runtime head:

`af5a2aa89ec1ddd5357502e611a4aa70392219af`

The PR remains Draft, open, mergeable, unmerged, and undeployed.

## Initial review decision

`REQUEST CHANGES` was correct during implementation. The first consolidated line contained both code-level and test-evidence defects. They were corrected before the runtime head above passed exact-head automation.

## Findings and corrections

### 1. Historical explicit mobile Create could bypass the desktop authorization method

The complete File 20 v1.0.0 Renderer treated mobile mode `create` as an unconditional presentation choice, while `auto` called the role-aware Create method.

**Correction:** the v1.0.1 request-time bridge rewrites a denied current request to mobile `doctors`. An authorized request preserves the configured `create`, `auto`, or `doctors` preference. The database option is not changed.

### 2. File 22 authorization could diverge from native File 20 capability parity

The first bridge allowed a positive `sabri_shell_can_show_create` filter result to replace the role-list decision without independently preserving the historical native `edit_posts` requirement.

**Correction:** login, Safe Mode, a positive authenticated subject, at least one normalized current role, and native `edit_posts` are now non-overridable. File 22 may replace only the configured role-list presentation result after these safeguards pass.

### 3. Roleless capability-bearing subjects were not explicitly denied

A malformed or unusual account could theoretically have a positive user ID and capability result with no normalized role. That state could not be represented safely in the historical role-list Renderer.

**Correction:** roleless subjects fail closed before the File 22 extension filter runs. A dedicated roleless-subject regression proves that explicit mobile Create is removed and the producer reports no visible gateway.

### 4. Authorized mobile preference was overwritten

The first correction forced every authorized request to mobile `auto`, which could override an administrator-selected `doctors` preference.

**Correction:** authorized requests preserve the validated configured value: `create`, `auto`, or `doctors`. Only denied requests are forced to `doctors` for that request.

### 5. Safe Mode and Settings could recurse through the option filter

The producer must read Safe Mode, while Emergency Disable is stored in the same settings option being filtered.

**Correction:** a request-local recursion guard returns the raw in-flight value during nested resolution. Emergency Disable remains controlling, and no recovery or settings write occurs from the public request.

### 6. First CI assertion described an obsolete implementation string

The regression runner expected a literal assignment to mobile `auto`, even after the corrected implementation preserved the selected mobile mode.

**Correction:** the static contract now verifies the validated `$mobile_mode` path and the denied `doctors` path instead of an obsolete source string.

### 7. Security scan treated test closure variables as PHP `assert()`

Test files use local closures named `$assert`. The first regular expression matched `$assert(` as the dangerous language construct `assert(`.

**Correction:** the scan now uses a negative lookbehind for `$`, preserving detection of real `assert()` calls without falsely rejecting test closures. The scan was not removed or globally weakened.

### 8. Development-only patch machinery became obsolete

A temporary guarded patch and one-time workflow were used while the connector could not initially apply the complete source correction directly.

**Correction:** after the real source files were updated, both the temporary patch and its write-enabled workflow were deleted. The release allowlist excludes tests, tools, workflows, patches, and other development-only paths.

### 9. Version, documentation, CI, and package identity were initially split

The complete source began as v1.0.0 while separate branches described proposed v1.0.1 contracts.

**Correction:** plugin header, runtime constant, readme, changelog, build tool, test report, candidate ZIP, checksum, and CI artifact now use v1.0.1. The Create producer contract is independently versioned as `1.0.0`.

## Security and privacy conclusions

- File 20 grants no WordPress role or capability.
- Logged-out, Safe Mode, constant disable, Emergency Disable, missing subject, roleless subject, and missing `edit_posts` decisions fail closed before File 22 can authorize presentation.
- File 22 receives only the current user ID and File 20 settings needed for presentation compatibility; no patient, post, message, identity-document, or clinical payload is introduced.
- The compatibility bridge changes only the in-memory option value for the current public request.
- No public request calls `update_option()` or `delete_option()` through the Create producer.
- wp-admin settings reads are not made user-specific by this bridge.
- File 21 retains its native fallback unless the exact contract and current-user visibility functions both succeed.

## Architecture conclusion

The request-time option bridge is a bounded compatibility layer for the historical Renderer. It avoids duplicating the complete Renderer and avoids a risky full-file rewrite in this corrective line. A future major File 20 release may move the final authorization call directly into Renderer, but that is not required for the reviewed v1.0.1 contract.

## Automated evidence at the reviewed runtime head

All three workflow families completed successfully on the unchanged runtime head:

1. `Build and Test Plugin` — run 60, ID `30438746027`;
2. `File 20 and File 21 Slot Contracts` — run 31, ID `30438749003`, PHP 7.4/8.1/8.3 matrix;
3. `File 20 Minimal Smoke` — run 25, ID `30438745755`.

The build workflow passed complete PHP syntax, JavaScript syntax, the v1.0.1 regression runner, File 21 slot contracts, focused Create producer tests, roleless-subject regression, JSON checks, candidate build, filename checks, ZIP CRC/path/top-level validation, and artifact upload.

Candidate artifact:

- name: `sabri-unified-application-shell-1.0.1-candidate`;
- artifact ID: `8718511223`;
- archive digest: `sha256:5ef1773ceeebe5cbbbe1ff63d626043ed8ec75e0439de276ff4eaaf2ff8de7a1`;
- source head: `af5a2aa89ec1ddd5357502e611a4aa70392219af`.

## Remaining acceptance blockers

This review does not authorize merge, staging promotion, production packaging, or live deployment. The following remain required:

- controlled Files 00, 20, 21, and 22 installation together on Hostinger staging;
- File 22 Phase 22E dependency and merge order;
- Founder, Administrator, verified doctor, permitted unverified doctor, student, patient, suspended, rejected, expired-document, roleless, and logged-out matrix;
- desktop/mobile agreement for `create`, `auto`, and `doctors` preferences;
- File 22 absent, incompatible, Safe Mode, duplicate-adapter, and rollback fallback tests;
- browser, keyboard, screen-reader, 200%/400% zoom, reduced-motion, forced-colors, and Urdu RTL acceptance;
- verified files/database backup and rollback evidence;
- explicit Founder authorization.

## Review conclusion

No additional known code-level blocker remains within the reviewed File 20 v1.0.1 Create producer and File 21 slot scope at the exact runtime head. This conclusion is limited to source and automated contract scope and does not replace controlled multi-plugin staging acceptance.
