# Namespace and Autoload Contract

## B2.0 Implementation Plan

### Affected Files

- `docs/architecture/namespace-contract.md` — define the final namespace, PSR-4 ownership, forbidden mixing, and Deptrac naming contract.

### Affected Packages

None. This task documents existing and future namespace ownership only.

### Dependency Impact

None. Runtime dependencies and dependency direction are unchanged. No Composer mapping is changed during B2.0.

### Public Contracts Introduced

No PHP contract is introduced. This document becomes the architectural namespace and autoload contract for later implementation.

### Validation Required

- `composer validate`
- `composer quality`

### Rollback Considerations

Rollback removes this document. No source, autoload configuration, runtime state, package, module, extension, persistence, or dependency lock state is changed.

## Status

ACCEPTED FOR B2.0 IMPLEMENTATION

## Purpose

Define the final namespace and Composer PSR-4 ownership contract before runtime discovery, manifest parsing, capability resolution, or package loading is implemented.

This contract reconciles the superseded single `Modules/` layout with the accepted ownership model:

```text
packages/base/  Base-owned reusable packages
extensions/     project-owned Base customization
modules/        project-owned business modules
app/            host composition
```

Filesystem ownership and namespace ownership must remain aligned.

## Canonical Namespace Roots

| Ownership area | Filesystem root | Namespace root | Status |
| --- | --- | --- | --- |
| Base packages | `packages/base/` | `Base\` | Active |
| Project modules | `modules/` | `Modules\` | Reserved for module implementation |
| Project extensions | `extensions/` | `Extensions\` | Reserved for extension implementation |
| Laravel host | `app/` | `App\` | Active |
| Repository tests | `tests/` | `Tests\` | Active development-only mapping |

The architecture ownership roots are deliberately distinct. A physical ownership boundary must not be hidden beneath another owner's namespace.

## Base Package Namespace

Base-owned reusable packages live below `packages/base/` and use the namespace root `Base\`.

Directory segments below `packages/base/` map directly to namespace segments:

| Filesystem path | Namespace |
| --- | --- |
| `packages/base/Foundation/ModuleManager/` | `Base\Foundation\ModuleManager` |
| `packages/base/Foundation/Manifest/` | `Base\Foundation\Manifest` |
| `packages/base/Platform/Notifications/` | `Base\Platform\Notifications` |
| `packages/base/Specialized/GIS/` | `Base\Specialized\GIS` |

Example class mapping:

```text
packages/base/Foundation/Manifest/Public/Contracts/ManifestReader.php
    -> Base\Foundation\Manifest\Public\Contracts\ManifestReader
```

`Base\Foundation`, `Base\Platform`, and `Base\Specialized` express Base ownership categories. They do not grant cross-package access. Cross-package consumption remains limited to declared `Public\` contracts and allowed dependency directions.

Project-specific code must never use the `Base\` namespace root.

## Project Module Namespace Strategy

Project-owned or reusable business modules live directly below `modules/` and use the namespace root `Modules\`.

The first segment after `Modules\` is the module identity. The accepted physical model does not repeat a Product category unless `Product` is itself the module name.

| Filesystem path | Namespace |
| --- | --- |
| `modules/Product/` | `Modules\Product` |
| `modules/Cart/` | `Modules\Cart` |
| `modules/Wallet/` | `Modules\Wallet` |
| `modules/Orders/` | `Modules\Orders` |

Examples:

```text
modules/Orders/Public/Contracts/OrderQuery.php
    -> Modules\Orders\Public\Contracts\OrderQuery

modules/Orders/Application/Queries/OrderQueryHandler.php
    -> Modules\Orders\Application\Queries\OrderQueryHandler
```

Another component may import the Public contract when the dependency is declared and allowed. It must not import the internal handler.

The historical namespace `Base\Modules\<Category>\<Module>` belongs to the superseded single-root layout. New project modules must not use it.

## Extension Namespace Strategy

Project-owned customization lives below `extensions/` and uses the namespace root `Extensions\`.

Extensions targeting Base packages use `Extensions\Base\<Target>\...`, matching the accepted `extensions/Base/` structure.

| Filesystem path | Namespace |
| --- | --- |
| `extensions/Base/Identity/` | `Extensions\Base\Identity` |
| `extensions/Base/Notifications/` | `Extensions\Base\Notifications` |
| `extensions/Base/Settings/` | `Extensions\Base\Settings` |

Example:

```text
extensions/Base/Identity/CustomerProfileContributor.php
    -> Extensions\Base\Identity\CustomerProfileContributor
```

The `Base` segment identifies the extension target family. It does not transfer ownership to Base and does not permit extensions to declare `Base\...` classes.

Extensions consume only declared Base Public extension contracts, capabilities, contributors, decorators, strategies, and events. Base packages must not import `Extensions\...`.

## Composer PSR-4 Ownership Rules

The authoritative PSR-4 ownership model is:

```json
{
    "Base\\": "packages/base/",
    "Modules\\": "modules/",
    "Extensions\\": "extensions/",
    "App\\": "app/"
}
```

`Tests\` remains a development-only mapping to `tests/`.

B2.0 does not add the reserved `Modules\` or `Extensions\` mappings to `composer.json`, because no real project modules or extensions are implemented in this phase. They must be added before production code under those roots is used.

PSR-4 ownership rules:

1. One namespace root has one owning filesystem root.
2. A filesystem root must not be mounted under multiple architecture namespace roots.
3. A more-specific mapping must not reassign part of another owner's tree.
4. Production namespaces must not resolve from tests, temporary fixtures, caches, or runtime storage.
5. `autoload-dev` must not make production code reachable only during development.
6. Namespace and path casing must match on case-sensitive filesystems.
7. Autoloadability does not grant permission to cross Public/Internal boundaries.

## Forbidden Namespace Mixing

The following are forbidden:

- project modules or extensions declaring `Base\...` classes;
- Base packages declaring `Modules\...` or `Extensions\...` classes;
- modules declaring `Extensions\...` classes or extensions declaring `Modules\...` classes;
- production classes declared below `Tests\...`;
- using `App\...` for reusable Base packages, business modules, or extensions;
- mapping `Base\Modules\...` to `modules/`;
- mapping `Base\Extensions\...` to `extensions/`;
- placing project-owned customization under `packages/base/`;
- placing Base-owned code under `modules/` or `extensions/`;
- importing foreign internal namespaces merely because Composer can autoload them.

Aliases, Composer `files` autoloading, and container bindings must not conceal forbidden ownership mixing.

## Public Boundary Impact

Namespace ownership and public visibility are separate checks. Valid ownership does not make a class public. Cross-boundary imports must still target the owning component's `Public\` namespace and follow the dependency matrix.

```text
Modules\Orders -> Base\Platform\Notifications\Public\Contracts\NotificationSender
    allowed when declared

Extensions\Base\Identity -> Base\Foundation\Identity\Public\Extensions\ProfileContributor
    allowed when declared

Modules\Orders -> Base\Platform\Notifications\Infrastructure\EmailSender
    forbidden

Base\Foundation\Identity -> Modules\Wallet\Public\Contracts\WalletQuery
    forbidden reverse dependency
```

## Deptrac Layer Naming Impact

Deptrac layer names should mirror ownership and namespace identity:

| Component | Layer pattern | Example |
| --- | --- | --- |
| Base package | `Base.<Category>.<Package>` | `Base.Foundation.ModuleManager` |
| Base Public surface | `Base.<Category>.<Package>.Public` | `Base.Platform.Notifications.Public` |
| Base internals | `Base.<Category>.<Package>.Internal` | `Base.Platform.Notifications.Internal` |
| Project module | `Modules.<Module>` | `Modules.Orders` |
| Module Public surface | `Modules.<Module>.Public` | `Modules.Orders.Public` |
| Module internals | `Modules.<Module>.Internal` | `Modules.Orders.Internal` |
| Base-targeted extension | `Extensions.Base.<Extension>` | `Extensions.Base.Identity` |

Layer names use dots for readability; PHP namespaces use backslashes.

Deptrac collectors must align with filesystem and namespace ownership. Rulesets must enforce:

- Base layers never access `Modules.*` or `Extensions.*`;
- Foundation never accesses Platform or Specialized;
- Platform never accesses Product modules;
- extensions access only declared Base Public layers;
- modules access only allowed and declared Public layers;
- foreign `.Internal` layers are never cross-boundary targets;
- circular dependencies remain forbidden.

Existing layers such as `Base.Foundation.ModuleManager` already follow this naming contract. Later runtime work may refine Public/Internal collectors but must not rename ownership roots or weaken dependency direction.

## B2.0 Decision

```text
packages/base/  -> Base\
modules/        -> Modules\
extensions/     -> Extensions\
app/            -> App\
tests/          -> Tests\ (development only)
```

This decision formalizes namespace ownership before runtime implementation and supersedes `Base\Modules\...` wherever that historical namespace conflicts with the accepted package/extension/module ownership model.

## Non-Goals

B2.0 does not:

- change `composer.json` or Deptrac configuration;
- implement discovery, manifests, registries, capabilities, or lifecycle behavior;
- create packages, modules, extensions, providers, or application code.
