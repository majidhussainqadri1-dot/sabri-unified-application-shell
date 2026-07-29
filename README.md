# Sabri Unified Application Shell

Sabri Unified Application Shell is an independent WordPress plugin that adds a secure, responsive public application shell for the Sabri Social Homeopathy Platform.

Version: 1.0.1
Plugin slug: `sabri-unified-application-shell`
Text domain: `sabri-unified-application-shell`
Author: Dr. Allama Majid Hussain Sabri

## What It Provides

- Global header with brand, search, role-aware Create, Messages, one Notifications output, Help, language integration only when detected, and profile/login controls.
- Horizontal primary navigation with configurable labels, order, page IDs, shortcodes, slugs, and URL overrides.
- Persistent left sidebar with grouped navigation, user/visitor cards, active state, remembered scroll position, and footer mappings.
- Conditional right sidebar that renders only in three-column contexts.
- Mobile bottom navigation, accessible drawers, focus trapping, Escape close, outside click close, focus restoration, and body scroll lock.
- `[sabri_shell_home_feed]` shortcode plus optional static-front-page chronological Latest feed insertion.
- Admin settings under **Sabri Shell** with Overview, Layout, Header, Navigation, Left Sidebar, Right Sidebar, Mobile, Integrations, Appearance, System Check, Repair, and Safe Mode tabs.
- Safe Mode, Emergency Disable/Re-enable, activation snapshot, rollback, Complete Repair, and CI release packaging.

## Important Limitations

- messaging backend is not created by this plugin.
- real calls are not created.
- end-to-end encryption is not claimed.
- live streaming is not created.
- AI recommendations are not claimed.
- full compatibility with every WordPress theme is not claimed.
- Hostinger staging testing is required before production activation.

The shell integrates only with existing public functions, shortcodes, roles, post types, pages, and validated URLs. It does not depend on, revive, or copy the cancelled `sabri-global-ui` plugin.

## Installation

1. Upload `sabri-unified-application-shell` to `wp-content/plugins/`.
2. Activate **Sabri Unified Application Shell** on a staging site first.
3. Open **Sabri Shell** in wp-admin.
4. Run **System Check** and review unresolved pages and missing integrations.
5. Configure page mappings, clinic mapping, roles, appearance, and mobile behavior.
6. Test the public site at the required viewport widths before production activation.

## Initial Configuration

Open **Sabri Shell > Layout** and set:

- maximum shell width;
- left and right sidebar widths;
- gap;
- sticky header preference;
- Worldwide Clinic page ID;
- doctor/clinic post type;
- excluded pages;
- per-page layout overrides;
- optional validated theme content selector;
- optional validated selectors to hide duplicate theme header/footer elements.

Per-page overrides use one entry per line:

```text
42:three
99:two
100:minimal
```

Supported override values are `default`, `three`, `two`, and `minimal`.

## Page Mapping

Navigation resolution uses this precedence:

1. Configured published Page ID.
2. Published page containing configured or detected shortcode.
3. Existing post-type archive.
4. Built-in slug candidate match.
5. Validated configured URL override.

Unresolved destinations are hidden rather than rendered as dead `#` links.

## Worldwide Clinic Mapping

Set the Worldwide Clinic page ID in **Layout**. That page receives three-column layout. Set the doctor/clinic post type to the existing public post type used by the real clinic or doctor system. Single posts of that post type also receive three-column layout.

## Home Feed

Use:

```text
[sabri_shell_home_feed]
```

The feed is chronological and labeled **Latest**. On a static front page, the plugin can append the feed after existing page content when automatic insertion is enabled. It does not append when the shortcode already exists and does not duplicate the normal WordPress posts page loop.

## Safe Mode

Administrators with `manage_options` can add this query string to a public URL:

```text
?sabri_shell_safe=1
```

Developers can also define:

```php
define( 'SABRI_SHELL_DISABLE', true );
```

Both methods suppress the public shell without deleting content or companion-plugin data.

## Emergency Disable and Re-enable

Use **Sabri Shell > Safe Mode** to toggle Emergency Disable or Re-enable. This only changes shell rendering. It does not remove posts, pages, users, media, comments, messages, appointments, marketplace data, clinic data, or companion-plugin tables.

## Rollback

The plugin captures an activation snapshot before defaults or migrations mutate settings. Rollback restores only shell-owned settings and shell navigation/theme visibility configuration. It does not delete or modify WordPress content or companion-plugin data.

See [ROLLBACK.md](ROLLBACK.md).

## Complete Repair

Complete Repair may merge missing defaults, migrate old shell settings, rebuild navigation mappings, clear shell navigation cache, schedule one rewrite-rule flush, clear shell-only transients, revalidate schema, and refresh integration detection.

It must never delete or change posts, pages, users, media, comments, or companion-plugin data.

## Companion Integrations

The Integrations tab detects:

- Notifications;
- Network;
- Messages;
- Marketplace;
- Appointments;
- doctor roles;
- verified doctor roles;
- clinic post types;
- configured functions;
- configured shortcodes and URLs through navigation mappings.

The plugin links to real detected systems only.

## Permissions

Admin settings require `manage_options`. File 20 never grants publishing capabilities.

Version 1.0.1 keeps Header enabled/Create enabled, login, Safe Mode, constant disable, Emergency Disable, a positive authenticated subject, at least one normalized role, and native `edit_posts` non-overridable. The legacy fallback additionally requires a configured allowed role. File 22 may replace only that legacy role-list presentation result through `sabri_shell_can_show_create` after central Membership Core, capability, adapter, and availability checks.

Desktop and mobile Create use the same request-time authorization. Disabling the Header or Header Create closes both surfaces before File 22 authorization runs. A denied explicit mobile `create` setting is changed to Doctors for that request. An authorized request preserves the administrator-configured `create`, `auto`, or `doctors` mobile preference. These compatibility changes are never persisted to the database.

The Create contract is version `1.0.1` and includes an owner proof. A pre-existing producer function or owner constant closes the contract and is reported by System Check. The final filtered Create URL must be credential-free, HTTPS, and same-origin; any malformed, downgraded, or external destination is rejected and replaced only with the validated native admin fallback.

Read-only producer functions:

```php
sabri_shell_create_contract_available();
sabri_shell_create_visible_for_current_user();
```

File 21 preserves its native fallback unless both functions confirm the complete current-user gateway and the contract owner/version proof matches.

## Pinned Files 20, 21, and 22 Convergence

A dedicated GitHub Actions gate tests one explicit dependency set together:

- the exact File 20 pull-request head;
- File 21 corrective head `a9bf99ab0169dd56fe664f6a1540a5de6031d518`;
- File 22 Phase 22E head `9aed674344c33b8756b65e7bc58c223ac6ffc4ae`.

On PHP 8.1 and 8.3 it executes File 20's actual Create producer against selected actual File 22 Shell components, File 21's actual native workflow adapter against File 22's actual Workflow Coordinator, and File 21's bounded workflow-maintenance contracts.

This is pinned three-repository source-contract convergence. Controlled WordPress, Membership, and selected readiness collaborators remain in the focused tests, so it is not complete Files 00/20/21/22 staging and does not authorize merge or deployment.

See [docs/FILES20-21-22-PINNED-CONVERGENCE-CONTRACT-2026-07-29.md](docs/FILES20-21-22-PINNED-CONVERGENCE-CONTRACT-2026-07-29.md).

## Accessibility

The implementation targets WCAG 2.2 AA as a design objective. It includes semantic landmarks, a skip link, visible focus, accessible drawers, focus trapping, Escape close, outside click close, focus restoration, logical CSS properties for RTL readiness, and minimum 44px mobile touch targets.

## Performance

The plugin uses scoped CSS, vanilla JavaScript, no external CDN, no remote fonts, no bundled font binaries, and no unsafe remote scripts. Assets load only on public shell requests. Navigation resolution is cached and invalidated when settings, pages, post types, permalinks, or Repair change.

## Privacy

The right sidebar uses only public content and explicitly public profile fields such as `sabri_public_phone` and `sabri_public_whatsapp`. It never displays CNIC, passport, private email, private phone, patient data, medical records, fabricated reviews, fake counts, or fake online status.

## Known Limitations

- Theme compatibility depends on how the active theme structures content.
- Right sidebar modules require real content or real integrations.
- Live Hostinger, live database, and cross-browser testing must be completed manually.
- The pinned Files 20/21/22 convergence gate uses actual selected source from all three repositories, but controlled WordPress, Membership, File 22 readiness/Safe Mode, and other focused test collaborators remain; it is not complete Files 00/20/21/22 staging.
- The plugin does not create duplicate backend databases for companion systems.

## Tests and Release

Local static checks:

```powershell
.\tools\run-local-static-checks.ps1
```

Local release build:

```powershell
.\tools\build-release.ps1
```

GitHub Actions checks out and verifies the exact source head, runs complete PHP 8.2 lint and tests, lints every shipped production PHP file on PHP 7.4, runs JavaScript/static checks, File 21 slot contracts, focused Create authorization tests, the pinned File 20/File 22 Shell contract, the pinned Files 20/21/22 convergence contract, deterministic two-build ZIP comparison, SHA-256 generation, and artifact upload.
