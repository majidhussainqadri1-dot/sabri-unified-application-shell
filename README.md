# Sabri Unified Application Shell

Sabri Unified Application Shell is an independent WordPress plugin that adds a secure, responsive public application shell for the Sabri Social Homeopathy Platform.

Version: 1.0.0
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

Admin settings require `manage_options`. The public Create button appears only when the logged-in user has `edit_posts` and belongs to a configured allowed role. Default allowed roles are `administrator` and `editor`. The shell never grants publishing capabilities.

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

GitHub Actions runs PHP lint, WordPress stub/bootstrap tests, JavaScript syntax checks, CSS sanity checks, static scans, release ZIP validation, SHA-256 generation, and artifact upload.
