# Manifest

## Ownership

- **Owner:** Base Platform
- **Classification:** base-owned
- **Category:** Foundation

## Purpose

Parses, validates, and hydrates `module.json` manifest files into structured runtime objects.

Manifest is the parsing and validation layer for all module metadata. It is responsible for:

- reading `module.json` files from package and module directories
- validating manifest structure against the manifest contract schema
- hydrating raw JSON into typed PHP value objects
- detecting schema violations and providing actionable error messages
- enforcing version format rules (semantic versioning)
- providing the manifest data contract consumed by ModuleManager

## Public Contracts

No public contracts are defined yet. This is a skeleton package.

Future public contracts will be added under `Public/Contracts/`.

## Capabilities Provided

None declared yet.

## Dependencies

None declared yet.

## Data Ownership

No tables owned. Manifest owns no persistent state.

## Configuration

No configuration yet.

## Lifecycle

Manifest is not a user-facing feature module. It cannot be disabled independently.

## Testing

```bash
composer test -- --filter=Manifest
```

## Status

Skeleton only. Runtime implementation is deferred to future B2 tasks.
