# Module Public vs Internal Boundary Contract

## Status

ACCEPTED FOR B1 IMPLEMENTATION

## Purpose

Define exactly which parts of a Base module may be consumed by another module and which parts remain private implementation details.

This contract is mandatory for Foundation, Platform, Specialized, and Product modules.

## Core Rule

A module is private by default.

Cross-module dependencies are allowed only through the owning module's explicit:

`Public/`

namespace.

If code is not inside `Public/`, another module must treat it as private.

## Canonical Public Namespace

For a module:

`Base\Modules\<Category>\<Module>`

the only directly consumable namespace is:

`Base\Modules\<Category>\<Module>\Public\*`

Example:

`Base\Modules\Foundation\Identity\Public\Contracts\CurrentPrincipal`

may be consumed by another module.

Example:

`Base\Modules\Foundation\Identity\Infrastructure\Persistence\EloquentUserRepository`

must not be consumed by another module.

## Allowed Public Artifacts

The Public namespace may contain stable cross-module contracts such as:

- interfaces
- capability contracts
- query contracts
- command contracts where explicitly intended
- immutable DTOs
- integration events
- stable exceptions
- identifiers
- version metadata
- enums only when they are truly part of the public contract

## Public Contracts Must Be Stable

Public contracts are compatibility boundaries.

Changing them may affect multiple modules.

Therefore public contracts must:

- have clear ownership
- have documented semantics
- avoid implementation leakage
- avoid unnecessary framework coupling
- follow compatibility/versioning rules
- remain small and intentional

A query contract may return:

- public DTOs
- scalar values
- value objects that are explicitly public

It must not return internal models.

## Command Contract Rule

Cross-module mutation must be explicit.

A module may expose a Public command/application contract when synchronous mutation is required.

Example:

```text
Public/
└── Contracts/
    └── DisableUser.php
```

The owning module remains responsible for performing the mutation.

A consumer must never update the target module's tables directly.

## Integration Event Rule

Modules may collaborate asynchronously through Public integration events.

Integration events must be:

- immutable
- versioned
- schema-defined
- transport-neutral
- owned by the publishing module

Internal domain events do not automatically become integration events.

## Internal Domain Events

Events under internal Domain namespaces are private.

Example:

`Base\Modules\Product\Cart\Domain\Events\CartRecalculated`

must not be consumed directly by another module unless intentionally promoted into a Public integration event.

## Public Exceptions

A module may expose stable exceptions when consumers are expected to handle them.

Examples:

- capability unavailable
- invalid public request
- resource not found through a public query
- version incompatibility

Internal infrastructure exceptions must not cross module boundaries.

## Internal Namespaces

The following namespaces are private by default:

- Domain
- Application
- Infrastructure
- Interfaces
- Database
- Config
- Resources
- Tests

A module may reorganize internal folders when justified, but doing so does not make those classes public.

## Domain Privacy

Another module must not import:

- entities
- aggregates
- domain services
- internal domain events
- specifications
- policies

from another module's Domain namespace.

If another module needs a capability, the owning module must expose an appropriate Public contract.

## Application Privacy

Another module must not import:

- internal handlers
- internal use cases
- private application services
- internal command buses
- implementation orchestration

from another module's Application namespace.

Only explicitly published Public application contracts may cross boundaries.

## Infrastructure Privacy

Another module must not import:

- repositories
- persistence adapters
- Redis adapters
- HTTP clients
- provider SDK adapters
- mail implementations
- filesystem implementations

from another module's Infrastructure namespace.

## Interface Privacy

Another module must not import:

- controllers
- request validators
- API resources
- CLI command implementations
- queue consumers
- listeners

from another module's Interfaces namespace.

## Database Privacy

Another module must not import or rely on:

- migrations
- factories
- seeders
- table names
- persistence implementation

from another module's Database namespace.

## Configuration Privacy

A module must not read another module's private configuration files directly.

Forbidden example:

`config('identity.internal.password_policy')`

when used by another module.

Cross-module configuration requirements must be exposed through:

- Public configuration contracts
- capability contracts
- manifest options
- explicit DTOs

## Resource Privacy

Another module must not depend directly on another module's private:

- translation implementation
- view file
- internal template
- schema file

unless that resource is intentionally published as part of a stable Public contract.

## Same-Module Dependency Rule

Inside a module, internal layers may collaborate according to that module's architecture.

The Public/Internal rule exists primarily to protect module boundaries.

Future B1 work will define internal layer dependency rules separately where needed.

## Cross-Module Import Rule

Given:

Module A

and:

Module B

Module A may directly import only:

`Module B\Public\*`

Any direct import from Module B's internal namespace is an architecture violation.

### Example — Allowed

```php
use Base\Modules\Foundation\Identity\Public\Contracts\CurrentPrincipal;
```

This is allowed.

### Example — Forbidden Model Import

```php
use Base\Modules\Foundation\Identity\Infrastructure\Persistence\Models\User;
```

This is forbidden.

### Example — Forbidden Handler Import

```php
use Base\Modules\Product\Product\Application\Handlers\CreateProductHandler;
```

This is forbidden from another module.

### Example — Forbidden Repository Import

```php
use Base\Modules\Product\Product\Infrastructure\Persistence\ProductRepository;
```

This is forbidden from another module.

### Example — Allowed Query Contract

```php
use Base\Modules\Product\Product\Public\Queries\ProductCatalog;
```

This is allowed if declared as a dependency.

### Example — Allowed Integration Event

```php
use Base\Modules\Product\Orders\Public\Events\OrderPlacedV1;
```

This is allowed for a declared consumer.

## Dependency Declaration Requirement

A Public import being technically allowed does not automatically mean the dependency is valid.

The consuming module must also explicitly declare the dependency in its future module manifest.

Therefore both conditions must hold:

- target namespace is Public;
- dependency is declared.

## Capability Preference

Where practical, consumers should depend on capabilities rather than implementation module names.

Example capability:

`notification.send`

A consumer should not care whether the implementation is:

- database notification
- email provider
- external messaging provider

The future Capability Registry resolves the implementation.

## Public Namespace Is Not a Shared Dumping Ground

The Public directory must remain intentionally small.

Do not move classes into Public merely to bypass architecture validation.

A class belongs in Public only when it represents a deliberate cross-module compatibility contract.

## SharedKernel Rule

SharedKernel is not an escape hatch for cross-module dependencies.

Code must not be moved into SharedKernel simply because multiple modules need access to it.

SharedKernel is reserved for genuinely stable and broadly reusable primitives.

## Framework Coupling Rule

Public contracts should minimize Laravel/framework coupling.

Framework types may be used only when they are genuinely part of the Base public technical contract and no cleaner boundary exists.

Framework-independent contracts are preferred.

## Security Rule

Security-critical boundaries must not use permissive fallbacks.

Public contracts for:

- authentication
- authorization
- tenant isolation
- encryption
- secret access

must fail closed when their required capability is unavailable.

## Optional Capability Rule

Optional dependencies may expose safe unavailable behavior.

Examples:

- notification skipped
- realtime publication unavailable
- optional analytics disabled

Optional behavior must be explicit and observable.

## Compatibility Rule

Public contracts will eventually follow semantic compatibility rules.

Breaking changes to public contracts require:

- version change
- migration path
- consumer impact review
- compatibility validation

Exact versioning implementation is defined later.

## Testing Rule

Every important Public contract should eventually have contract tests.

Contract tests validate:

- expected inputs
- expected outputs
- compatibility
- error semantics
- optional behavior where applicable

## Deptrac Enforcement Goal

Deptrac must eventually enforce:

- cross-module imports may target Public only;
- internal module imports from foreign modules are forbidden;
- category dependency direction is respected.

## PHPStan Enforcement Goal

PHPStan/custom rules should eventually detect:

- forbidden foreign internal imports;
- public DTOs exposing forbidden framework/persistence types;
- cross-module model usage where static analysis can identify it.

## Manifest Enforcement Goal

Future manifest validation must ensure:

- consumed modules/capabilities are declared;
- required dependencies exist;
- versions are compatible;
- optional dependencies define fallback behavior where required.

## Architecture Script Enforcement Goal

Custom architecture validation may be used for rules Deptrac alone cannot reliably enforce.

Examples:

- undeclared manifest dependency
- table ownership violations
- migration ownership violations
- foreign configuration access
- forbidden public contract types

## Negative Test Requirement

B1 must prove this rule with a temporary architecture fixture.

A fixture representing:

Product Cart

must be rejected if it imports:

Product Product Infrastructure

directly.

A corresponding fixture importing:

Product Product Public

must be accepted when dependency direction and declarations permit it.

No production Product or Cart module is implemented for this test.

## Anti-Patterns

Explicitly forbidden:

- making everything Public
- moving implementation into Public to silence Deptrac
- shared Eloquent models
- shared repository implementations
- direct foreign handler invocation
- service locator calls targeting foreign concrete classes
- foreign config reads
- foreign table access
- hiding cross-module coupling inside helpers
- moving business code into SharedKernel to avoid dependency rules

## B1.2 Decision

The accepted module visibility rule is:

Private by default.

Only:

`Base\Modules\<Category>\<Module>\Public\*`

is directly consumable by another module.

A valid cross-module dependency requires both:

- a Public target;
- an explicit dependency declaration.

All other module implementation namespaces remain private.

## Non-Goals

B1.2 does not implement:

- real modules
- ModuleManager
- capability registry
- manifest schema
- module discovery
- Identity
- Settings
- Product
- Cart
- Wallet
- persistence integration

B1.2 defines the visibility contract only.
