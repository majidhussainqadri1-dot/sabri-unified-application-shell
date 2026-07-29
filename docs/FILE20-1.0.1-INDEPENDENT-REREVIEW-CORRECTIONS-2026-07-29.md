# File 20 v1.0.1 Independent Re-review Corrections — 2026-07-29

## Decision before correction

The exact-head automated checks were green, but the independent re-review correctly returned **BLOCKED / corrections required**. Green tests did not override the identified runtime, provenance, compatibility, diagnostics, staging, evidence-scope, URL-integrity, contract-ownership, and reproducibility defects.

## Corrected findings

### 1. Header-disabled mobile Create parity

`header.enabled` and `header.create` are now non-overridable inputs to the same final request-time authorization used by desktop and mobile. Either switch closes mobile `create` and `auto` before File 22's authorization filter executes.

### 2. Exact-head build provenance

The main build workflow now explicitly checks out `${{ github.event.pull_request.head.sha || github.sha }}` and verifies `git rev-parse HEAD` before lint, tests, packaging, and artifact upload. It no longer relies on GitHub's synthetic pull-request merge ref while claiming branch-head provenance.

### 3. Complete shipped-source PHP 7.4 gate

A dedicated PHP 7.4 job now lints the plugin bootstrap, uninstall file, and every production PHP file under `includes/` and `admin/`. PHP 8-only test collaborators remain outside the shipped package compatibility claim.

### 4. Cross-repository workflow triggers

The pinned File 20/File 22 contract workflow now reruns when the bootstrap, Create Visibility, Defaults, Plugin, Renderer, Safe Mode, Settings, contract test, or workflow definition changes.

### 5. Create diagnostics

System Check now reports contract version, contract owner, function ownership/collision state, producer functions, readiness, current-user visibility, and final Create URL health without exposing protected user or content data.

### 6. Staging acceptance

The packaged staging checklist now requires Files 00/20/21/22 load-order, role/status/document, Header/mobile settings, dependency absence/incompatibility/Safe Mode/collision, File 21 fallback, cache, browser, keyboard, screen-reader, zoom, forced-colors, reduced-motion, Urdu RTL, backup, and rollback evidence.

### 7. Evidence scope

The File 20/File 22 workflow is now described as a **pinned hybrid source contract**. It uses actual selected File 22 interfaces, Permission Resolver, Registry, and Shell Bridge, but controlled WordPress/Membership collaborators, controlled File 22 readiness/Safe Mode collaborators, and a synthetic adapter. It is not represented as complete Files 00/20/21/22 staging.

### 8. Create URL integrity

A final priority-1000 File 20 URL filter accepts only credential-free HTTPS URLs whose host and effective port match the current site. Unsafe integration output is rejected and replaced only with the validated native WordPress admin Create fallback.

### 9. Contract collision integrity

The producer contract is now version `1.0.1` and includes:

- `SABRI_SHELL_CREATE_CONTRACT_OWNER`;
- `SABRI_SHELL_CREATE_FUNCTIONS_OWNED`.

A pre-existing producer function or owner marker closes File 20's contract instead of silently creating a mixed implementation. File 21's corrective branch now requires the exact version, owner, and ownership proof before removing its fallback.

### 10. Reproducible package

The build now sorts entries, uses file-content insertion, fixes every ZIP entry timestamp through `SOURCE_DATE_EPOCH`, stores entries deterministically, normalizes Unix file attributes, and builds twice in one workflow before byte-for-byte comparison. The uploaded `.sha256` identifies the inner installable plugin ZIP.

## Required verification

These corrections are not complete merely because they were committed. Fresh exact-head CI, PHP 7.4 production lint, pinned-hybrid contract execution, deterministic double-build proof, artifact inspection, File 21 regression verification, and a separate post-correction review record are required before the code-level blocker can be closed.

No merge, staging promotion, production package authorization, or live deployment is authorized by this record.
