# ExtensionRegistry

## Ownership

- **Owner:** Base Platform
- **Classification:** base-owned
- **Category:** Foundation

## Purpose

Discovers and manages all extension points — contributors, decorators, strategies, and relation contributors — without modifying Base package source files.

ExtensionRegistry is the extension management layer of the Base runtime. It is responsible for:

- discovering extensions via package metadata and PHP attributes
- registering contributors for permission, settings, navigation, and relation extension points
- registering decorators that add project-owned behavior around Base capabilities
- registering strategy implementations for multi-provider capabilities
- ensuring that extensions targeting absent optional modules are safely skipped
- providing a deterministic extension graph for the same project definition

## Extension Model

Base packages expose extension contracts. Projects provide implementations. The registry never depends on project-specific code.

```
Base Package
    ↓
Public Extension Contract
    ↓
Project Extension
```

## Public Contracts

No public contracts are defined yet. This is a skeleton package.

Future public contracts will be added under `Public/Contracts/`.

## Capabilities Provided

None declared yet.

## Dependencies

None declared yet.

## Data Ownership

No tables owned. ExtensionRegistry owns no persistent state.

## Configuration

No configuration yet.

## Lifecycle

ExtensionRegistry is not a user-facing feature module. It cannot be disabled independently.

## Testing

```bash
composer test -- --filter=ExtensionRegistry
```

## Status

Skeleton only. Runtime implementation is deferred to future B2 tasks.
