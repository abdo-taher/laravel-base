# ExtensionRegistry

## Ownership

- **Owner:** Base Platform
- **Classification:** base-owned
- **Category:** Foundation

## Purpose

Provides the framework-free contracts and in-memory registration foundation for explicitly declared contributors, decorators, strategies, and metadata extensions.

Extension points declare their kind, accepted Public contract, and whether multiple contributions are permitted. Enabled extension definitions are validated atomically before their contributions become visible. Disabled definitions, unknown points, contract mismatches, duplicate identifiers, and single-contribution conflicts fail closed.

## Resolution

Point lookup uses the exact registered name. Contribution collections are deterministic: higher priority first, followed by extension ID and contribution ID in lexical order.

The `ExtensionMetadata` attribute is passive declaration metadata only. B2.4 does not provide reflection scanning, extension discovery, decorator execution, strategy selection, runtime boot orchestration, or persistence.

## Public Boundary

Public contracts, definitions, exceptions, and attribute metadata live under `Public/`. They contain no Laravel types. Laravel coupling is limited to the package service provider, which binds the Public registry contract to the in-memory implementation.

## Capability

- `extension.registry` version `1.0.0`

## Dependencies and Data

The package has no package dependency and owns no tables.

## Testing

```bash
composer test -- --filter=ExtensionRegistry
```
