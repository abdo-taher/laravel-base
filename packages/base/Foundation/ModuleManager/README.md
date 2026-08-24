# ModuleManager

## Ownership

- **Owner:** Base Platform
- **Classification:** base-owned
- **Category:** Foundation

## Purpose

ModuleManager is the orchestration layer for the Base runtime foundation.

Given one or more filesystem search paths it:

1. **Discovers** all `module.json` manifests under those paths (via `FilesystemModuleDiscovery`).
2. **Validates** each manifest on read (via `ManifestReader` — fails closed on invalid manifests).
3. **Rejects duplicate identities** — two manifests with the same `name` produce a `ModuleBootPlanFailed`.
4. **Resolves dependencies** topologically (via `DependencyResolver` — fails closed on missing required deps, cycles, and forbidden directions).
5. **Registers capabilities** declared in each manifest into the `CapabilityResolver`.
6. **Returns a `ModuleBootPlan`** with the deterministic initialization order.

## Public Contracts

| Contract | Purpose |
|---|---|
| `ModuleManager` | Single orchestration entry point. Call `boot(searchPaths)`. |
| `ModuleDiscovery` | Discovers `list<Manifest>` from filesystem paths. |
| `ModuleBootPlan` | Immutable result: ordered identifiers and per-module state. |

All public contracts are framework-free. No Laravel types cross the `Public\` boundary.

## Value Objects

| Value Object | Purpose |
|---|---|
| `ModuleIdentifier` | Immutable name + category. Equality by name. |
| `ModuleState` | Immutable identifier + state string. Foundation states: `discovered`, `ready`. |

## Exceptions

| Exception | When thrown |
|---|---|
| `ModuleDiscoveryFailed` | Unreadable search path or invalid manifest. |
| `ModuleBootPlanFailed` | Missing dependency, cycle, duplicate identity, or resolution ambiguity. |

Both exceptions fail closed — no partial plan is returned.

## Orchestration Flow

```
ModuleManager::boot(searchPaths)
    │
    ├─ ModuleDiscovery::discover(searchPaths)
    │       └─ ManifestReader::read(module.json)  ← fails closed on invalid
    │
    ├─ assert no duplicate module names           ← fails closed
    │
    ├─ DependencyResolver::resolve(manifests)     ← fails closed on cycle/missing
    │
    ├─ CapabilityResolver::register(...)          ← one entry per declared capability
    │
    └─ DefaultModuleBootPlan(orderedStates)
```

## Capabilities Provided

| Capability | Version |
|---|---|
| `module.manager` | 0.2.0 |
| `module.discovery` | 0.2.0 |

## Dependencies

| Capability | Required |
|---|---|
| `manifest.read` | yes |
| `dependency.resolve` | yes |
| `capability.resolve` | yes |

## Limitations (B2.5)

- **Extension point registration** from manifest metadata is deferred until the `Manifest` value object carries `extension_points` data.
- **Full lifecycle management** (enable/disable, drift detection) is deferred.
- **No hard-coded module list** — discovery is entirely path-driven.
- **No automatic code execution** from discovered paths — only `module.json` files are read.
- **No database** and no migrations.

## Testing

```bash
composer test -- --filter=ModuleManager
```

## Status

B2.5 implemented. Orchestration foundation is active. Full lifecycle management deferred.
