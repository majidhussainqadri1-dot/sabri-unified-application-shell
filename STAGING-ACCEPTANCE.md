# Staging Acceptance

Hostinger staging testing is mandatory before any merge, production package authorization, or live activation.

## Pre-installation evidence

- Confirm the active URL is the Hostinger staging environment, not production.
- Capture a verified files backup and database backup.
- Record current active plugins, theme, WordPress/PHP versions, permalink structure, front-page settings, and cache state.
- Record the exact package names, SHA-256 values, source commits, and installation order for Files 00, 20, 21, and 22.
- Confirm File 22 Phase 22E and File 21 workflow dependencies match the reviewed source pins.

## Installation and load-order matrix

Test clean activation and reactivation with:

1. File 00 → File 20 → File 21 → File 22;
2. File 00 → File 22 → File 20 → File 21;
3. File 00 → File 21 → File 20 → File 22;
4. each dependency temporarily absent, disabled, incompatible, or in Safe Mode;
5. a deliberate duplicate-adapter-key collision;
6. File 21 fallback restoration after File 20 or File 22 deactivation.

No activation sequence may produce a fatal error, recursion, unauthorized Create surface, duplicate CTA, duplicate content record, settings write on public GET, or stale Create URL.

## Role, status, and document matrix

For every desktop and mobile mode, verify:

- Founder;
- Administrator;
- verified doctor;
- policy-permitted unverified doctor;
- patient;
- student;
- suspended account;
- rejected account;
- expired-document account;
- roleless authenticated account;
- logged-out visitor.

For each subject record the result for Header Create, mobile Create/Doctors item, File 22 gateway, File 21 fallback, direct native route, and canonical URL.

## Create parity and settings matrix

Test every combination of:

- `header.enabled`: on/off;
- `header.create`: on/off;
- mobile `create_or_doctors`: `create`, `auto`, `doctors`;
- File 20 Safe Mode: on/off;
- `SABRI_SHELL_DISABLE`: on/off;
- Emergency Disable: on/off;
- File 22 Safe Mode: on/off;
- File 22 Create page: ready/missing;
- adapter: available/unavailable/collision/incompatible.

Required outcome:

- Header or Header Create disabled closes both desktop and mobile Create before File 22 authorization runs.
- Denied users see Doctors or no actionable Create control; they never receive a stale Create URL.
- Authorized users preserve the configured mobile presentation mode.
- The final Create URL is HTTPS, same-origin, credential-free, and resolves to the approved File 22 page or the validated native fallback.
- File 21 fallback returns when the universal gateway is incomplete or rolled back.

## System Check

Open **Sabri Shell → System Check** and verify:

- Create contract version `1.0.1`;
- Create contract owner `sabri-unified-application-shell`;
- producer functions are owned and available;
- no function/constant collision is reported;
- current-user visibility is truthful;
- the final Create URL passes same-origin HTTPS validation;
- File 22 readiness and File 21 fallback behavior match the current installation state;
- no patient, identity, clinical, post-body, raw reference, or raw idempotency data appears in diagnostics.

## Public rendering and data safety

- Confirm existing posts, pages, media, users, comments, URLs, and shortcodes still work.
- Verify no companion-plugin data is created, deleted, duplicated, or modified by File 20.
- Map Home, News, Founder, Learn, Encyclopedia, Doctors, Worldwide Clinic, Video Wall, Reels, PDF Library, Radar, AI, Network, and Marketplace.
- Confirm unresolved destinations are hidden rather than rendered as dead links.
- Confirm Home, Worldwide Clinic directory, and single doctor/clinic pages use three-column layout.
- Confirm ordinary pages, posts, archives, categories, tags, search results, and public custom post types use two-column layout.
- Confirm minimal mode on login, signup, password reset, REST, AJAX, cron, XML-RPC, feeds, robots, sitemaps, embeds, previews, print mode, Safe Mode, and maintenance endpoints.
- Confirm the Right Sidebar is absent from the DOM on two-column pages.
- Confirm Notifications appears in exactly one output location.
- Confirm the Home/News feed and Create CTA are never duplicated.

## Browser, responsive, and accessibility acceptance

Test current stable Chrome, Firefox, Edge, and Safari-compatible rendering where available, plus real Android and iOS-sized viewports.

Widths:

`320, 360, 390, 480, 768, 900, 1024, 1100, 1280, 1366, 1440, 1600, 1920 px`

Required checks:

- no whole-page horizontal scrolling;
- complete keyboard operation;
- visible focus;
- skip-link operation;
- Escape and outside-click drawer close;
- focus restoration;
- screen-reader names, states, and landmark order;
- 200% and 400% zoom;
- reduced motion;
- forced colors/high contrast;
- Urdu RTL layout and mixed Urdu/American-English content;
- touch targets and mobile bottom navigation parity.

## Cache and persistence checks

- Repeat authorization changes with LiteSpeed/page/object caches enabled and disabled.
- Confirm no user-specific Create decision is served from a shared cache.
- Confirm public requests do not persist role additions, mobile-mode rewrites, or authorization results.
- Confirm logout immediately removes Create from desktop and mobile.

## Backup and rollback proof

- Deactivate File 22 and verify File 21 fallback restoration.
- Deactivate File 20 and verify native modules remain usable.
- Run File 20 rollback and prove only shell-owned settings are restored.
- Restore the staging files/database backup and compare active plugins, options, content counts, users, media, comments, and companion-plugin data.
- Record rollback duration, errors, cache-clearing steps, and final System Check.

## Acceptance record

The evidence package must include screenshots, role/status matrix, exact package hashes, System Check export, browser/accessibility observations, backup identifiers, rollback results, defects, corrections, and Founder decision.

## Not claimed by automated tests

- real Hostinger/WordPress plugin load order;
- production database behavior;
- real browser and assistive-technology acceptance;
- cache-layer isolation;
- complete Files 00/20/21/22 interoperability;
- production readiness.
