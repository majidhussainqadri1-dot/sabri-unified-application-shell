# Changelog

## 1.0.1 — Complete Source Create Producer Candidate

- Consolidated the complete File 20 source line with the official File 21 Home and News placement slots.
- Added `SABRI_SHELL_CREATE_CONTRACT_VERSION` `1.0.0` and read-only Create producer functions for Files 21 and 22.
- Added a request-time, non-persistent compatibility bridge so File 22 can replace the legacy role-list presentation decision only after its central permission and adapter checks.
- Kept logged-out, Safe Mode, constant disable, and Emergency Disable decisions non-overridable.
- Neutralized the historical explicit mobile Create bypass by converting denied public-request settings to Doctors and authorized settings to the shared automatic decision.
- Preserved `edit_posts` as a required native capability; File 20 does not grant publishing permissions.
- Added focused tests for login, Safe Mode, central authorization, narrowing, mobile parity, recursion, malformed settings, and no-write behavior.
- Kept all runtime changes Draft, unmerged, and undeployed pending independent review and controlled staging.

## 1.0.0

- Initial independent Sabri Unified Application Shell WordPress plugin.
- Added public shell header, navigation, sidebars, mobile drawers, and mobile bottom navigation.
- Added chronological `[sabri_shell_home_feed]` shortcode and static front-page insertion guard.
- Added admin settings tabs, system check, repair, safe mode, emergency disable/re-enable, activation snapshot, and rollback.
- Added automated test harness, local PowerShell static checks, GitHub Actions, and release packaging.
