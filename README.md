# Sabri Unified Application Shell

Independent WordPress application shell for the Sabri Social Homeopathy Platform.

## File 22 integration correction

The current production package is version 1.0.0. File 22 requires an official Create-visibility extension point because the legacy shell restricts Create to its configured role list.

This branch contains the reviewable 1.0.1 source patch that:

- keeps login and Safe Mode non-overridable;
- preserves the existing `edit_posts` plus allowed-role result as the fallback;
- exposes `sabri_shell_can_show_create` with the fallback result, current user ID, and shell settings;
- lets File 22 replace the legacy presentation result only after Membership Core and adapter authorization;
- bumps the package version to 1.0.1.

The patch must be applied to the audited File 20 version 1.0.0 source package, rebuilt reproducibly, and tested on staging before merge or deployment. This repository branch does not claim that the complete historical File 20 source has already been imported into GitHub.
