# Migration

Sabri Unified Application Shell is a completely new independent plugin.

It does not depend on `sabri-global-ui`, does not copy files from `sabri-global-ui`, does not revive `sabri-global-ui`, and does not modify companion plugins.

## From No Shell

1. Install and activate on staging.
2. Run **Sabri Shell > System Check**.
3. Configure Layout, Navigation, Integrations, Appearance, and Mobile.
4. Map the Worldwide Clinic page and doctor/clinic post type.
5. Verify public pages and mobile drawers.
6. Move to production only after staging acceptance.

## From Another Shell

1. Disable the other shell on staging.
2. Activate this plugin.
3. Use Layout settings to hide duplicate theme header/footer elements only when needed.
4. Confirm no duplicate Notifications output appears.
5. Verify that posts, pages, users, media, comments, and companion-plugin data remain unchanged.

No migration step creates messaging, notification, appointment, profile, marketplace, or publishing databases.
