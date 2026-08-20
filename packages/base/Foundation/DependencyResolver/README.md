# DependencyResolver

## Ownership

- **Owner:** Base Platform
- **Classification:** base-owned
- **Category:** Foundation

## Purpose

Constructs the module dependency graph, detects cycles, and produces a safe deterministic load order.

DependencyResolver is the dependency analysis layer of the Base runtime. It is responsible for:

- receiving the set of declared module manifests from ModuleManager
- constructing a directed dependency graph from manifest declarations
- detecting circular dependencies and reporting them as hard errors
- performing a topological sort to determine a safe provider registration order
- validating that all required dependencies have compatible available versions
- identifying optional dependency availability and communicating it to consumers

## Rules

- Circular dependencies are always rejected.
- A missing required dependency is a boot failure.
- A missing optional dependency is reported but does not block boot.
- Version constraints follow semantic versioning.

## Public Contracts

No public contracts are defined yet. This is a skeleton package.

Future public contracts will be added under `Public/Contracts/`.

## Capabilities Provided

None declared yet.

## Dependencies

None declared yet.

## Data Ownership

No tables owned. DependencyResolver owns no persistent state.

## Configuration

No configuration yet.

## Lifecycle

DependencyResolver is not a user-facing feature module. It cannot be disabled independently.

## Testing

```bash
composer test -- --filter=DependencyResolver
```

## Status

Skeleton only. Runtime implementation is deferred to future B2 tasks.
