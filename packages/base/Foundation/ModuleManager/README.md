# ModuleManager

## Ownership

- **Owner:** Base Platform
- **Classification:** base-owned
- **Category:** Foundation

## Purpose

Discovers, validates, and manages the lifecycle of Base packages and project modules at runtime.

ModuleManager is the central orchestrator of the Base runtime. It is responsible for:

- discovering packages and modules by scanning manifest files
- validating manifest identity, version, and dependency declarations
- delegating dependency graph construction to DependencyResolver
- delegating capability resolution to CapabilityRegistry
- delegating extension registration to ExtensionRegistry
- producing the final ordered load sequence for service provider registration

## Public Contracts

No public contracts are defined yet. This is a skeleton package.

Future public contracts will be added under `Public/Contracts/`.

## Capabilities Provided

None declared yet.

## Dependencies

None declared yet.

## Data Ownership

No tables owned. ModuleManager owns no persistent state.

## Configuration

No configuration yet.

## Lifecycle

ModuleManager is not a user-facing feature module. It cannot be disabled independently.

## Testing

```bash
composer test -- --filter=ModuleManager
```

## Status

Skeleton only. Runtime implementation is deferred to future B2 tasks.
