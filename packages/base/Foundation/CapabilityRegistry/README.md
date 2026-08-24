# CapabilityRegistry

## Ownership

- **Owner:** Base Platform
- **Classification:** base-owned
- **Category:** Foundation

## Purpose

Registers explicit provider definitions and resolves capability contracts without coupling consumers to provider implementations.

## Public Contracts

- `Public\\Contracts\\CapabilityContract` marks capability implementations.
- `Public\\Contracts\\CapabilityProviderContract` supplies a capability contract.
- `Public\\Contracts\\CapabilityResolver` registers definitions and resolves requests.
- `Public\\ValueObjects\\CapabilityName` validates capability identifiers.
- `Public\\ValueObjects\\CapabilityVersion` validates and compares semantic versions.
- `Public\\ValueObjects\\CapabilityProviderDefinition` carries provider metadata and strategy fields.
- `Public\\ValueObjects\\CapabilityResolutionResult` describes resolved or optional-absent results.
- Public exceptions report invalid definitions and fail-closed resolution failures.

Public contracts contain no Laravel dependencies.

## Registration

Registration is metadata-driven and records capability name, semantic version, provider contract, metadata, priority, and optional strategy key. The registry contains no hard-coded providers.

## Resolution

- A single compatible provider resolves.
- Missing required capabilities fail closed.
- Missing optional capabilities return an unresolved result.
- Existing incompatible providers are rejected.
- Multiple compatible providers are rejected unless an explicit strategy key uniquely selects one.

## Version Foundation

B2.3 supports exact versions and caret constraints. Full Composer-compatible semantic version solving is deferred.

## Strategy Foundation

Provider priority and strategy keys are retained in immutable definitions. Explicit strategy filtering is supported. Priority does not silently select among ambiguous providers until a complete deterministic strategy engine is approved.

## Capabilities Provided

- `capability.resolve` version `1.0.0`.

## Dependencies

None.

## Data Ownership

No tables or persistent state. Registrations live only in the in-memory registry instance.

## Testing

```bash
composer test -- --filter=CapabilityRegistry
```

## Status

B2.3 registration, version-aware resolution, optional absence, and strategy foundation implemented.
