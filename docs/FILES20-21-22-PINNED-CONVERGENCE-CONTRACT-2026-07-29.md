# Files 20, 21, and 22 Pinned Convergence Contract — 2026-07-29

## Purpose

This gate verifies that the reviewed File 20 Create producer, reviewed File 21 native social-publication workflow adapter, and reviewed File 22 workflow coordinator and Shell bridge remain mutually compatible on one explicitly pinned dependency set.

It is an automated source-contract convergence gate. It does not replace a real WordPress installation with Files 00, 20, 21, and 22 active together on Hostinger staging.

## Exact dependency set

- File 20: the exact pull-request head under test;
- File 21: `a9bf99ab0169dd56fe664f6a1540a5de6031d518`;
- File 22: `9aed674344c33b8756b65e7bc58c223ac6ffc4ae`.

The workflow verifies all three Git commit SHAs before executing any contract.

## What the gate executes

### File 20 → File 22 Shell contract

The gate runs File 20's actual `CreateVisibility` producer against selected actual File 22 Adapter interfaces, Permission Resolver, Registry, and Shell Bridge source. It checks authorized visibility, same-origin Create URL selection, Header/mobile parity, Membership status denial, missing capabilities, File 20 and File 22 Safe Mode, unavailable Create-page state, and Emergency Disable.

### File 21 → File 22 Workflow contract

The gate runs File 21's actual `UniversalComposerPublicationAdapter` against File 22's actual `Workflow_Coordinator`. It checks strict schema discovery, native draft creation, signed preview, draft-referenced idempotent publication, status normalization, canonical URL retrieval, and native ownership boundaries.

### File 21 workflow maintenance

The gate also executes bounded retention, recovery, and stale execution-lock maintenance contracts so convergence cannot pass while File 21's durable native workflow state is internally inconsistent.

## PHP matrix

The complete convergence gate runs on:

- PHP 8.1;
- PHP 8.3.

## Evidence boundary

This gate uses real selected source from all three repositories, but it still uses controlled WordPress and Membership collaborators inside the repository tests. File 22 readiness/Safe Mode collaborators and some runtime services remain controlled in the focused contracts.

Therefore the correct claim is:

> Pinned three-repository source-contract convergence.

The following are not established by this gate:

- real WordPress plugin load order;
- production database behavior;
- real Membership Core storage and document-expiry transitions;
- browser, cache, accessibility, mobile, or Urdu RTL behavior;
- backup restoration or rollback on Hostinger;
- production readiness.

## Release law

A green result may remove an automated dependency-convergence blocker only. It does not authorize merging File 20, File 21, or File 22, producing a production release, installing on live, or bypassing controlled staging and Founder acceptance.
