# Module Structure and Namespace Contract

## Status

ACCEPTED FOR B1 IMPLEMENTATION

## Purpose

Define the canonical filesystem, namespace, visibility, dependency, and ownership contract for every Base module.

This contract applies to Foundation, Platform, Specialized, and Product modules.

## Architecture Context

Base is a reusable Laravel modular-monolith foundation for future projects.

The Laravel application root acts as the host and composition shell.

Reusable technical capabilities and business capabilities belong in isolated modules rather than accumulating inside the Laravel `app/` directory.

The architecture must support:

- strict module isolation
- explicit public contracts
- DDD where domain complexity exists
- independent module ownership
- machine-enforced dependency direction
- optional business capabilities
- future Project Factory module selection
- future module lifecycle management

## Top-Level Module Directory

All Base-owned modules live under:

`Modules/`

The Laravel root:

`app/`

remains a thin host and composition shell.

Business capabilities and reusable platform capabilities must not accumulate in `app/`.

## Module Categories

The canonical module categories are:

- Foundation
- Platform
- Specialized
- Product

Canonical physical structure:

```text
Modules/
├── Foundation/
├── Platform/
├── Specialized/
└── Product/
```

### Foundation Modules

Foundation modules contain reusable technical primitives and essential platform capabilities.

Examples:

- SharedKernel
- Configuration
- ModuleManager
- Security
- Health
- Identity
- AccessControl
- Audit
- Observability

Foundation must remain business-free.

Foundation must not depend on:

- Platform
- Specialized
- Product

Foundation modules may depend only on other explicitly permitted Foundation public contracts.

### Platform Modules

Platform modules provide reusable horizontal capabilities.

Examples:

- Settings
- Files
- Notifications
- QueueManagement
- Scheduler
- FeatureFlags
- Webhooks
- Search
- Workflow
- Reporting
- Localization

Platform modules may depend on Foundation public contracts.

Platform must not depend on Product modules.

Platform capabilities must remain product-neutral.

### Specialized Modules

Specialized modules provide reusable technical capabilities with narrower applicability.

Examples:

- GIS
- Realtime
- DocumentProcessing
- LocationServices

Specialized dependencies must be explicit.

Specialized modules must not become hidden dependencies of Foundation.

### Product Modules

Product modules provide optional business capabilities.

Examples:

- ProductCatalog
- Inventory
- Cart
- Wallet
- Orders
- Customers

Product modules may consume declared public contracts from:

- Foundation
- Platform
- Specialized

Foundation and Platform must never depend on Product modules.

A Product module must remain optional unless explicitly selected by a generated project.

### Intended Dependency Direction

The conceptual dependency direction is:

```text
Product
  ↓
Platform / Specialized
  ↓
Foundation
```

Dependencies flow downward.

Reverse dependencies are forbidden.

Circular module dependencies are forbidden.

## Canonical Namespace

The root module namespace is:

`Base\Modules`

The namespace includes both category and module name.

Examples:

- `Base\Modules\Foundation\SharedKernel`
- `Base\Modules\Foundation\Identity`
- `Base\Modules\Foundation\AccessControl`
- `Base\Modules\Platform\Settings`
- `Base\Modules\Platform\Notifications`
- `Base\Modules\Specialized\GIS`
- `Base\Modules\Product\Product`
- `Base\Modules\Product\Cart`
- `Base\Modules\Product\Wallet`

Filesystem and namespace must match.

Example file:

`Modules/Foundation/Identity/Public/Contracts/CurrentPrincipal.php`

maps to:

`Base\Modules\Foundation\Identity\Public\Contracts\CurrentPrincipal`

## Canonical Module Shape

The canonical module shape is:

```text
Modules/<Category>/<Module>/
├── Public/
│   ├── Contracts/
│   ├── DTOs/
│   ├── Events/
│   └── Exceptions/
├── Domain/
├── Application/
├── Infrastructure/
├── Interfaces/
│   ├── Http/
│   ├── Console/
│   ├── Queue/
│   └── Listeners/
├── Database/
│   ├── Migrations/
│   ├── Factories/
│   └── Seeders/
├── Config/
├── Resources/
├── Tests/
│   ├── Unit/
│   ├── Integration/
│   ├── Contract/
│   └── Feature/
├── module.json
└── README.md
```

Not every directory is mandatory.

Empty architectural directories must not be created merely to satisfy this diagram.

A module creates only the structures required by its behavior.

## Public Contract Rule

Only code under:

`<Module>\Public\*`

may be imported directly by another module.

This is the explicit cross-module contract surface.

Allowed public artifacts include:

- interfaces
- immutable DTOs
- query contracts
- command contracts where appropriate
- capability contracts
- integration events
- stable exceptions
- version metadata

### Public Contract Restrictions

Public contracts must not expose implementation details.

They must not expose:

- Eloquent models
- Laravel HTTP Requests
- Laravel API Resources
- database query builders
- infrastructure adapters
- concrete repositories
- provider SDK objects
- private persistence structures
- module-internal entities whose lifecycle is not public

A public contract must describe capability or behavior rather than reveal implementation ownership.

## Internal Boundary

Everything outside:

`<Module>\Public\*`

is internal to the owning module unless another architecture contract explicitly says otherwise.

The following are private implementation areas:

- Domain
- Application
- Infrastructure
- Interfaces
- Database
- Config
- Resources
- Tests

Another module must not import classes directly from these namespaces.

## Domain Layer

`Domain/` contains domain behavior when meaningful domain complexity exists.

Possible contents include:

- Entities
- Aggregates
- ValueObjects
- DomainServices
- DomainEvents
- Specifications
- Policies

DDD tactical patterns are not mandatory merely because a module exists.

Do not create empty aggregates, repositories, services, or value objects when they add no domain value.

## Application Layer

`Application/` coordinates module use cases.

Possible contents include:

- Commands
- Queries
- Handlers
- Application services
- transaction coordination
- DTO mappings
- ports
- use-case orchestration

Application code may depend on:

- its own Domain
- its own Public contracts
- explicitly allowed Public contracts from dependency modules

Application code must not depend on another module's implementation internals.

## Infrastructure Layer

`Infrastructure/` contains technical implementations.

Examples:

- Eloquent persistence
- Redis adapters
- S3-compatible storage adapters
- mail providers
- HTTP clients
- queue adapters
- provider SDK integrations
- filesystem implementations

Infrastructure implements contracts owned by its module or explicitly consumed public contracts.

Infrastructure ownership does not make its classes public.

## Interfaces Layer

`Interfaces/` contains delivery and transport mechanisms.

Examples:

```text
Interfaces/
├── Http/
├── Console/
├── Queue/
└── Listeners/
```

HTTP controllers must remain thin.

Transport concerns must not become owners of domain behavior.

## Data Ownership Rule

Each persistent table has exactly one owning module.

A module must not:

- directly write another module's tables
- import another module's Eloquent model
- alter another module's tables through migrations
- use another module's repository implementation
- create hidden persistence coupling

Cross-module reads and writes must eventually use explicit mechanisms such as:

- public query contracts
- public mutation contracts
- capabilities
- projections
- integration events

## Migration Ownership

Module database migrations live under:

`Modules/<Category>/<Module>/Database/Migrations/`

A migration may alter only tables owned by its module.

Cross-module migrations are forbidden.

The Laravel root `database/` directory will eventually contain only genuine host-level persistence infrastructure, if any remains necessary.

## Factory and Seeder Ownership

Module-specific factories belong under:

`Database/Factories/`

Module-specific seeders belong under:

`Database/Seeders/`

A module must not seed another module's private persistence directly.

## Configuration Ownership

Module-owned configuration belongs under:

`Config/`

Other modules must not directly depend on another module's private configuration implementation.

Configuration needed across a module boundary must be represented through an explicit public capability or contract.

## Resource Ownership

Module-owned resources belong under:

`Resources/`

Examples may include:

- translations
- schemas
- templates
- module-owned static metadata

Presentation composition and the future UI Kit remain separate concerns.

## Tests

Tests that validate module behavior belong with the module.

Recommended categories are:

- Unit
- Integration
- Contract
- Feature

Architecture tests may additionally exist at repository level when validating ecosystem-wide invariants.

## Module Manifest

Each module owns:

`module.json`

The exact manifest schema will be defined in a later B1 step.

The manifest will eventually describe concepts such as:

- module identity
- category
- version
- capabilities provided
- required capabilities
- optional capabilities
- dependencies
- integration events
- table ownership
- permissions
- configuration
- lifecycle metadata

No final manifest schema is implemented during B1.1.

## Module README

Every module must eventually document:

- purpose
- category
- public contracts
- capabilities provided
- dependencies
- data ownership
- configuration
- lifecycle behavior
- testing commands

## DDD Rule

DDD is used where domain complexity exists.

Strict module boundaries are mandatory everywhere.

DDD tactical structures are not mandatory everywhere.

A simple technical module does not need artificial:

- aggregates
- repositories
- domain services
- factories
- specifications

Architecture should remain explicit without becoming ceremonial.

## Foundation Constraint

Foundation modules are the lowest reusable architectural layer.

They must remain:

- business-free
- presentation-agnostic
- independent of Product
- independent of optional higher-level modules

Foundation must not contain business concepts merely because multiple current applications happen to use them.

## Product Module Constraint

Product modules are optional business capabilities.

Examples such as:

- Product
- Cart
- Wallet

must not become dependencies of the Base Platform itself.

A future project may select:

- Product without Cart
- Product with Cart
- Cart without Wallet where its declared contracts permit it
- Wallet independently where its declared contracts permit it

Dependencies must be explicit rather than assumed.

## Composer Strategy

The initial Base repository uses one root Composer package.

The intended PSR-4 mapping is:

```json
{
    "Base\\Modules\\": "Modules/"
}
```

Package-per-module extraction is deferred until operationally justified.

The module architecture must not depend on being physically split into separate Git repositories or Composer packages.

## Laravel Provider Strategy

Modules own their service providers or equivalent bootstrap integration.

The Laravel host must not become a giant provider containing knowledge of every module implementation.

The future ModuleManager will handle:

- discovery
- validation
- dependency resolution
- registration
- bootstrapping

That behavior is not implemented during B1.1.

## Enforcement Requirements

This contract must eventually be machine-enforced through:

- Composer PSR-4
- PHPStan
- Deptrac
- architecture scripts
- manifest validation
- dependency graph validation
- architecture tests

Documentation alone is not sufficient.

Future Deptrac rules must evolve from the current host architecture gate to module-aware rules.

## Future Architecture Enforcement

When `Modules/` exists, enforcement must validate:

- Foundation cannot depend on Platform.
- Foundation cannot depend on Specialized.
- Foundation cannot depend on Product.
- Platform cannot depend on Product.
- Cross-module imports target only Public namespaces.
- Internal module namespaces remain private.
- Circular dependencies are forbidden.
- Undeclared dependencies are forbidden.
- Cross-module persistence coupling is forbidden.
- Module dependency declarations agree with actual code dependencies.

## Non-Goals

B1.1 does not implement:

- the Modules/ runtime
- SharedKernel
- Identity
- AccessControl
- Settings
- Product
- Cart
- Wallet
- ModuleManager
- module discovery
- module lifecycle
- capability registry
- manifest parser
- manifest schema
- module migration loader
- module route loader
- module provider loader
- module enable/disable behavior

B1.1 defines the canonical structural contract only.

## B1.1 Decision

The accepted structural decision is:

`Modules/<Category>/<Module>/`

with namespace:

`Base\Modules\<Category>\<Module>`

and the only directly consumable cross-module namespace:

`Base\Modules\<Category>\<Module>\Public\*`

Categories are:

- Foundation
- Platform
- Specialized
- Product

This decision becomes the basis for the remaining B1 work.
