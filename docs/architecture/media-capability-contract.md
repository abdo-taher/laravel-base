# Media Capability Contract

This document strictly defines the `Platform.Media` architecture and public boundary behavior.

## Core Directives
1. **Raw File Ignorance**: The consuming Product layer MUST NOT receive raw uploaded files.
2. **Opaque References**: Clients upload to `Platform.Media`, receiving an opaque string reference (e.g., `{"reference": "med_xxxxx"}`). The business API accepts ONLY this reference. No internal DB IDs, storage paths, or upload tokens are ever exposed to the client.
3. **Internal Access Scope**: The host application derives a `MediaAccessScope` (e.g. from the session) and passes it to Media during upload and sync. Media verifies scopes match to prevent cross-user hijacking. Identity is NOT a required dependency.
4. **Slot Definition Ownership**: Product modules manually define constraints via `MediaSlotDefinition` (name, single/multiple). The request key matches the slot name perfectly.

## Slot Semantics & Typed Sync Inputs
All synchronization relies on a structured typed input boundary (e.g., `MediaSlotChanges`) abstracting arrays.
- **UNTOUCHED (Omitted Key)**: Absolutely unchanged.
- **CLEAR (`null` or `[]`)**: Detaches current media in Single or Multiple slots respectively.
- **SET (Reference / Array of References)**: Attaches references. Missing existing collection references are detached.
- **Order Preservation**: For collections, the array order explicitly determines the `order_column`.
- **Duplicate References**: Rejected.

## Lifecycle & Reference Reuse
- **States**: `TEMPORARY` -> `ATTACHED` -> `ORPHANED`. (And conceptually `DELETED`).
- **Reuse Rules**: One-owner, attach-once MVP. A `TEMPORARY` reference is attached exactly once. `ATTACHED` or `ORPHANED` references immediately fail re-attachment validation.

## Detach, Cleanup & Transaction Semantics
- **Detach Policy**: Detaching merely sets the row to `ORPHANED`, removing owner linkages. It does NOT synchronously delete the Files blob.
- **Transactions**: Media DB updates participate natively in the caller's DB transaction. Rollbacks leave the DB state as `TEMPORARY`, and the physical blob untouched.
- **Cleanup**: A host-scheduled cleanup task eventually permanently destroys blobs and DB rows for expired `TEMPORARY` and `ORPHANED` media.

## Persistence
- **Schema**: Single-owner MVP. A unified `media` table maintains `owner_type`, `owner_id`, and `upload_scope`.
- **Owner Reference**: Uses `MediaOwnerReference('logical_type', 'scalar_id')`. No Eloquent models cross the Public boundary.

## Dependencies
- **Required**: `files.storage`
- **Forbidden (MVP)**: `identity.principal`, `settings.runtime`
