=== Sabri Unified Application Shell ===
Contributors: majidhussainqadri1-dot
Tags: application shell, navigation, layout, accessibility, homeopathy
Requires at least: 6.0
Tested up to: 6.6
Requires PHP: 7.4
Stable tag: 1.0.1
License: GPLv2 or later
Text Domain: sabri-unified-application-shell

Secure responsive public application shell for the Sabri Social Homeopathy Platform.

== Description ==

Sabri Unified Application Shell adds a global header, primary navigation, left sidebar, conditional right sidebar, mobile bottom navigation, accessible drawers, admin settings, System Check, Complete Repair, Safe Mode, Emergency Disable/Re-enable, activation snapshot, rollback, and release packaging support.

Version 1.0.1 adds the official File 21 Home and News placement slots and the versioned File 22 Create producer contract. Desktop and mobile Create use one fail-closed authorization decision. Logged-out, Safe Mode, constant disable, and Emergency Disable safeguards cannot be overridden. File 20 grants no publishing capability.

This is an independent plugin. It does not depend on the cancelled sabri-global-ui plugin and does not create duplicate messaging, notification, appointment, profile, marketplace, or publishing databases.

Important limitations:

* messaging backend is not created by this plugin.
* real calls are not created.
* end-to-end encryption is not claimed.
* live streaming is not created.
* AI recommendations are not claimed.
* full compatibility with every WordPress theme is not claimed.
* Hostinger staging testing is required before production activation.

== Installation ==

1. Upload the plugin folder to `wp-content/plugins/`.
2. Activate on staging first.
3. Open Sabri Shell in wp-admin.
4. Run System Check.
5. Configure Layout, Navigation, Integrations, Appearance, and Mobile.
6. Complete manual staging acceptance before production.

== Shortcode ==

Use `[sabri_shell_home_feed]` to render the chronological Latest feed.

== Changelog ==

= 1.0.1 =
Added File 21 Home/News placement slots and the fail-closed File 22 Create producer contract with desktop/mobile authorization parity and no request-time settings writes.

= 1.0.0 =
Initial independent plugin release.
