# Historical / Superseded — File 20 v1.0.1 Create Producer Post-Correction Verification — 2026-07-29

## Supersession notice

This record verified the earlier head `4e88d65e7a350e7b02f4ea66bf6ed4dd75fc6b66`. It is retained for audit history but is **not current acceptance evidence**.

A later independent re-review found additional defects that this verification did not detect:

- Header-disabled or Header-Create-disabled mobile parity;
- synthetic pull-request merge checkout being described as exact branch-head evidence;
- incomplete full-package PHP 7.4 proof;
- incomplete cross-repository workflow path triggers;
- missing Create contract diagnostics;
- incomplete staging acceptance matrix;
- overbroad cross-repository evidence wording;
- missing same-origin HTTPS final URL enforcement;
- missing producer-function ownership proof;
- non-reproducible ZIP metadata.

The current correction record is:

`docs/FILE20-1.0.1-INDEPENDENT-REREVIEW-CORRECTIONS-2026-07-29.md`

A new exact-head post-correction verification must replace this historical record after all current workflows and artifact checks pass.

## Historical evidence only

The earlier verification established useful but incomplete facts:

- the initial File 20 v1.0.1 source candidate and File 21 slots existed;
- login, Safe Mode, constant disable, Emergency Disable, roleless subject, and missing `edit_posts` cases were partly covered;
- request-time Create compatibility performed no option or content write;
- the earlier workflow and candidate artifact completed successfully on their recorded heads.

Those facts do not authorize merge, staging promotion, package release, or deployment. The later independent review takes precedence.
