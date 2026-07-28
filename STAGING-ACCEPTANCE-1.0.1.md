# Staging Acceptance — File 20 version 1.0.1

- Apply the patch to the exact audited File 20 version 1.0.0 source.
- Confirm the package header and `SABRI_SHELL_VERSION` are both 1.0.1.
- Confirm logged-out users never see Create.
- Confirm Shell Safe Mode and emergency disable always hide Create.
- Confirm the legacy Administrator/Editor behavior is unchanged without File 22.
- Confirm File 22 can expose Create for an approved verified doctor who has an available adapter.
- Confirm suspended, rejected, patient, student, and unauthorized accounts do not receive Create.
- Confirm desktop header and mobile navigation use the same final visibility result.
- Confirm the filter receives exactly: legacy result, current user ID, and settings.
- Run File 20 PHP lint and existing release checks.
- Rebuild ZIP, checksum, and manifest before any staging installation.
