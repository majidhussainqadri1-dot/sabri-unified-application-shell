# Staging Acceptance

Hostinger staging testing is required before production activation.

## Required Manual Checks

- Activate the plugin on staging only.
- Confirm existing posts, pages, media, users, comments, URLs, and shortcodes still work.
- Verify no companion-plugin data is created, deleted, or modified.
- Run **Sabri Shell > System Check**.
- Map Home, News, Founder, Learn, Encyclopedia, Doctors, Worldwide Clinic, Video Wall, Reels, PDF Library, Radar, AI, Network, and Marketplace.
- Confirm unresolved destinations are hidden rather than rendered as dead links.
- Confirm Home, Worldwide Clinic directory, and single doctor/clinic pages use three-column layout.
- Confirm ordinary pages, posts, archives, categories, tags, search results, and public custom post types use two-column layout.
- Confirm minimal mode on login, signup, password reset, REST, AJAX, cron, XML-RPC, feeds, robots, sitemaps, embeds, previews, print mode, Safe Mode, and maintenance endpoints.
- Confirm the Right Sidebar is not present in the DOM on two-column pages.
- Confirm Notifications appears in exactly one output location.
- Confirm Create appears only for users with `edit_posts` and allowed roles.
- Confirm the shell works at 320, 360, 390, 480, 768, 900, 1024, 1100, 1280, 1366, 1440, 1600, and 1920 px.
- Confirm no whole-page horizontal scrolling occurs.
- Confirm keyboard navigation, focus visibility, Escape drawer close, outside click close, and focus restoration.
- Confirm Home feed is not duplicated on the posts page.
- Confirm rollback restores only shell-owned settings.

## Not Claimed By Automated Tests

- Live production testing.
- Live Hostinger database testing.
- Cross-browser testing.
- End-to-end messaging.
- Real calls.
- Live streaming.
- AI recommendation quality.
- Full compatibility with every WordPress theme.
