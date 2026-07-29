# Files 20, 21, and 22 Convergence Post-Implementation Review — 2026-07-29

## Scope

This review examined the new three-repository convergence workflow, its exact dependency pins, the File 20 and File 21 test entry points it executes, the selected File 22 source boundaries, the README and changelog claims, and the relationship between automated convergence and real Hostinger staging.

## Initial decision

`REQUEST CHANGES`

The convergence direction was valid and the first run was green, but automated success did not close the documentation and evidence-integrity issues below.

## Finding 1 — Shipped README linked to a non-shipped review file

The installable File 20 ZIP intentionally excludes the repository `docs/` directory. The first README update nevertheless used a clickable relative link to the convergence record under `docs/`.

That link would be broken inside the shipped plugin package and could mislead an administrator into expecting an installed document that is development-only.

### Correction

The README now labels the path as a development-only repository review record and explicitly states that it is not shipped in the installable ZIP.

## Finding 2 — Documentation changes were not bound to the convergence gate

The first workflow path filter covered runtime and test files but not README, changelog, or the convergence evidence records. The dependency pins or evidence-scope wording could therefore drift without rerunning the dedicated gate.

### Correction

The workflow now reruns when the convergence README/changelog/review/verification records change. It also verifies:

- the exact File 21 pin appears in README;
- the exact File 22 pin appears in README;
- the convergence record uses the bounded `Pinned three-repository source-contract convergence` claim;
- the record explicitly states that the gate does not replace a real WordPress installation.

## Findings not treated as defects

### Separate PHP processes

File 20 → File 22 and File 21 → File 22 contracts run in separate PHP processes. This does not prove real plugin load order or one shared WordPress request. The documentation accurately limits the result to source-contract convergence, so this is a declared evidence boundary rather than a hidden defect.

### Controlled collaborators

The underlying focused tests still use controlled WordPress, Membership, File 22 readiness/Safe Mode, and other collaborators. The documentation explicitly discloses this and forbids treating the result as complete Files 00/20/21/22 staging.

### Frozen external heads

File 21 and File 22 are intentionally pinned to reviewed immutable commit SHAs. A later dependency revision requires an explicit pin update and a fresh review; automatic tracking of moving branches would weaken reproducibility.

## Review conclusion after correction

The two identified convergence-gate defects were corrected. Fresh exact-head execution on PHP 8.1 and PHP 8.3, together with all existing File 20 workflow families, is required before the new gate can be accepted at code level.

No merge, staging promotion, production release, or live deployment is authorized by this review.
