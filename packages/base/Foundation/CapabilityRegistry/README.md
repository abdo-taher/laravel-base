# CapabilityRegistry

## Ownership

- **Owner:** Base Platform
- **Classification:** base-owned
- **Category:** Foundation

## Purpose

Registers and resolves runtime capabilities so that consumers depend on contracts rather than concrete implementations.

CapabilityRegistry is the capability resolution layer of the Base runtime. It is responsible for:

- accepting capability provider registrations from packages and modules
- maintaining a registry of available capabilities and their providers
- resolving capability requests to the correct provider using version constraints
- enforcing that security-critical capabilities fail closed when unavailable
- supporting multiple providers for the same capability (strategy selection)
- making capability availability observable for lifecycle validation

## Capability Contract

Consumers declare what they need:

```json
{ "capability": "notification.send", "version": "^1.0" }
```

Providers declare what they supply:

```json
{ "provides": [{ "capability": "notification.send", "version": "1.2.0" }] }
```

The registry resolves the binding. Consumers never depend on provider class names.

## Public Contracts

No public contracts are defined yet. This is a skeleton package.

Future public contracts will be added under `Public/Contracts/`.

## Capabilities Provided

None declared yet.

## Dependencies

None declared yet.

## Data Ownership

No tables owned. CapabilityRegistry owns no persistent state.

## Configuration

No configuration yet.

## Lifecycle

CapabilityRegistry is not a user-facing feature module. It cannot be disabled independently.

## Testing

```bash
composer test -- --filter=CapabilityRegistry
```

## Status

Skeleton only. Runtime implementation is deferred to future B2 tasks.
