# Staging Acceptance — File 20 version 1.0.1

## Source and package

- Import the complete audited File 20 version 1.0.0 source before review completion.
- Confirm the plugin header and `SABRI_SHELL_VERSION` are both `1.0.1`.
- Confirm `SABRI_SHELL_CREATE_CONTRACT_VERSION` is `1.0.0`.
- Confirm `sabri_shell_create_contract_available()` is present and returns false in Safe Mode.
- Confirm `sabri_shell_create_visible_for_current_user()` uses the same final Renderer decision as desktop and mobile output.
- Run PHP lint and all existing File 20 release checks.
- Rebuild ZIP, checksum, manifest, and source inventory before any staging installation.

## Authorization and regression

- Logged-out users never see Create.
- Shell Safe Mode and emergency disable always hide Create and cannot be overridden by File 22.
- The legacy Administrator/Editor behavior remains unchanged without File 22.
- File 22 can expose Create for an approved authorized doctor who has an available native adapter.
- Suspended, rejected, expired-document, patient, student, and unauthorized accounts do not receive Create.
- Desktop header and mobile navigation use the same final visibility decision.
- Both mobile modes, `create` and `auto`, require the final permission decision; explicit `create` must not bypass authorization.
- The producer filter receives exactly the legacy result, current user ID, and settings.
- A File 20 version string without the exact contract constant and producer functions is treated as incompatible by File 21.

## Cross-plugin behavior

- File 22 Create page becomes the Shell destination only when it is published, healthy, and not in Safe Mode.
- File 21 retains `/create-post/` fallback until File 20 confirms current-user Create visibility.
- A duplicate or foreign `social_publication` adapter does not cause File 21 fallback removal.
- Deactivating File 22 restores File 21 fallback without changing posts, drafts, media, or permissions.
- Deactivating File 20 leaves File 21 native creation reachable through its fallback.

## Completion boundary

This checklist is not satisfied by a patch file or partial source import. Completion requires the exact full source tree, automated checks, package evidence, controlled staging role matrix, backup, and rollback proof.
