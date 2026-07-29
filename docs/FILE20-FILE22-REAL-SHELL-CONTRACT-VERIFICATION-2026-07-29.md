# Historical / Superseded — File 20 and File 22 Shell Contract Verification — 2026-07-29

## Supersession notice

The earlier workflow did load actual selected File 22 source components, but the title and conclusion were broader than the evidence. This record is retained as historical evidence only.

The corrected evidence term is:

> **Pinned hybrid File 20/File 22 source contract**

## Actual source used

The workflow pins File 22 Phase 22E to:

`9aed674344c33b8756b65e7bc58c223ac6ffc4ae`

It loads File 22's actual:

- Adapter interface;
- Workflow Adapter interface;
- Membership Core Permission Resolver;
- Registry;
- Shell Bridge.

It loads File 20's actual:

- Defaults;
- Settings;
- Safe Mode;
- Create Visibility producer.

## Controlled collaborators and exclusions

The workflow does **not** load a complete WordPress installation, actual File 00 runtime, actual File 21 publication adapter, or actual File 22 Safe Mode and Page Resolver implementations. Those are controlled test collaborators, and the registered adapter is synthetic.

Therefore the workflow can verify selected authorization and URL-handoff contracts, but cannot prove:

- real plugin load order;
- real options and cache behavior;
- complete File 00 status/document enforcement;
- actual File 21 fallback and adapter ownership;
- browser/mobile rendering;
- accessibility or Urdu RTL behavior;
- backup and rollback;
- Hostinger staging readiness.

## Current correction

The workflow and tests are being corrected to:

- use the explicit pinned-hybrid name;
- rerun on every File 20 source dependency that can change the contract;
- cover `header.enabled = false` and `header.create = false` before File 22 filters execute;
- verify the exact File 20 branch head;
- retain the source pin and controlled-collaborator disclosure.

The independent re-review record is:

`docs/FILE20-1.0.1-INDEPENDENT-REREVIEW-CORRECTIONS-2026-07-29.md`

A new final post-correction verification will record the fresh exact-head run IDs and artifact evidence. This historical record does not authorize merge, staging promotion, release, or deployment.
