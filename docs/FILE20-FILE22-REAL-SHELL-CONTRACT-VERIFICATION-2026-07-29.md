# File 20 and File 22 Real Shell Contract Verification — 2026-07-29

## Purpose

This verification closes the remaining automated gap between the corrected File 20 v1.0.1 Create producer and File 22's actual Shell Bridge. It does not use a local imitation of the File 22 bridge or registry.

## Exact source pins

File 20 reviewed head:

`efefaae8ee9876eaf0b3c5524c65abb2a4cc3555`

File 22 Phase 22E reviewed source:

`9aed674344c33b8756b65e7bc58c223ac6ffc4ae`

The workflow checks both Git heads before executing the contract.

## Actual File 22 source loaded

The test loads File 22's real:

- Adapter interface;
- Workflow Adapter interface;
- Membership Core Permission Resolver;
- adapter Registry;
- Shell Bridge.

WordPress, Membership Core account data, File 22 page readiness, and File 22 Safe Mode are controlled test collaborators. File 20's Defaults, Settings, Safe Mode, and Create Visibility classes are the actual source from the reviewed File 20 head.

## Verified positive contract

For an authenticated verified-doctor subject with:

- a normalized `sabri_verified_doctor` role;
- native `edit_posts`;
- central `sabri_feed_create_posts`;
- an available registered adapter;
- File 22 Safe Mode off;
- File 22 Create page ready;
- File 20 Emergency Disable off;

File 22's actual Registry and Shell Bridge authorize the File 20 request-time presentation result. File 20 adds the current role only to the in-memory request settings, preserves explicit mobile `create`, reports the current Create gateway as visible, and receives File 22's universal Create URL.

## Verified fail-closed cases

The real bridge contract denies File 20 Create when any of the following is true:

- File 22 Safe Mode is active;
- File 22 Create page is not ready;
- Membership Core status is suspended;
- central adapter capability is missing;
- File 20 native `edit_posts` is missing;
- File 20 Emergency Disable is active.

The missing-`edit_posts` case also proves that File 20 stops before invoking File 22's visibility filter.

## Automated evidence

Workflow:

`File 20 File 22 Real Shell Contract`

Run number: `1`

Run ID: `30439591037`

Exact File 20 head: `efefaae8ee9876eaf0b3c5524c65abb2a4cc3555`

Results:

- PHP 8.1: success;
- PHP 8.3: success.

Both jobs passed exact checkout verification, PHP syntax for all loaded File 20/File 22 contract files, and the real cross-repository behavior test.

All companion File 20 workflows also passed on the same head:

- Build and Test Plugin — run 68 / ID `30439585108`;
- File 20 and File 21 Slot Contracts — run 35 / ID `30439585946`;
- File 20 Minimal Smoke — run 29 / ID `30439585095`.

Candidate artifact from that head:

- artifact ID: `8718855433`;
- digest: `sha256:d5f86a52dee243663ec07230a31a96128fbbf139ce0551aaeb4b639be3c9f565`.

## Scope limitation

This is stronger than isolated stubs because the File 22 Shell Bridge, Permission Resolver, and Registry are actual pinned source. It is still not a substitute for WordPress/Hostinger staging with the production Files 00, 20, 21, and 22 packages, real plugin load order, real options, real roles, cache layers, theme rendering, and browser interaction.

## Conclusion

The automated File 20/File 22 Shell authorization and URL handoff contract is verified against the exact reviewed source pins. No additional known cross-repository source-contract blocker remains before controlled multi-plugin staging.
