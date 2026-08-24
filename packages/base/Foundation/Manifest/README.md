# Manifest

## Ownership

- **Owner:** Base Platform
- **Classification:** base-owned
- **Category:** Foundation

## Purpose

Reads, validates, and hydrates `module.json` files into immutable runtime values.

The package is responsible for:

- reading JSON manifest files;
- validating manifest identity and structural rules;
- hydrating typed readonly value objects;
- reporting file, JSON, and validation failures through Public exceptions.

## Public Contracts

- `Public\\Contracts\\ManifestReader` reads and validates a manifest file.
- `Public\\ValueObjects\\Manifest` exposes immutable manifest metadata.
- `Public\\ValueObjects\\ManifestDependency` exposes an immutable dependency declaration.
- `Public\\ValueObjects\\ManifestCapability` exposes an immutable provided capability.
- `Public\\Exceptions\\ManifestReadFailure` reports file and JSON failures.
- `Public\\Exceptions\\InvalidManifest` reports accumulated structural errors.

Public contracts contain no Laravel dependencies.

## Capabilities Provided

- `manifest.read` version `1.0.0`.

## Dependencies

None. The runtime uses PHP JSON and filesystem functions.

## Data Ownership

No tables owned. Manifest owns no persistent state.

## Validation

The reader requires valid `name`, `category`, `version`, `namespace`, and
`ownership` fields. It validates required/optional dependency lists and the
`provides` capability list before constructing value objects.

## Configuration

No configuration.

## Lifecycle

Manifest is foundational runtime infrastructure and cannot be disabled independently.

## Testing

```bash
composer test -- --filter=Manifest
```

## Status

B2.1 manifest loading and structural validation runtime implemented.
