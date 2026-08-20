# B1 — Module Structure Contract

## Status

PLANNING

No module implementation has started.

## Goal

Define the physical, namespace, dependency, ownership, and public-contract structure that every Base module must follow before the first real module is created.

## Architecture Context

Base is a modular monolith.

DDD is applied where domain complexity exists, but every module follows strict isolation boundaries regardless of whether it contains a rich domain model.

The Laravel root application remains a host/composition shell and must not become the business layer.

## Module Categories

Base recognizes four primary module categories:

1. Foundation
2. Platform
3. Specialized
4. Product

Experience/presentation composition will be designed separately and must not leak presentation concerns into Foundation modules.

## Intended Dependency Direction

The intended dependency direction is:

Product
→ Specialized / Platform
→ Foundation

Foundation must never depend on higher-level modules.

Platform must never depend on Product.

Specialized modules must declare their dependencies explicitly.

Product modules remain optional and must never become dependencies of Foundation or Platform modules.

## Public Contract Rule

A module's implementation is private by default.

Cross-module access is permitted only through explicitly declared public contracts.

Consumers must not import another module's:

- internal domain implementation
- application handlers
- infrastructure adapters
- persistence models
- repositories
- migrations
- internal service providers

## Data Ownership Rule

Each module owns its persistence.

A module must not:

- write another module's tables directly
- use another module's Eloquent models directly
- alter another module's tables through migrations
- rely on cross-module database joins as its integration contract

Cross-module data access must eventually use explicit query contracts, capabilities, projections, or integration events.

## Initial Structure Questions

Before implementation, B1 must decide:

1. Top-level module directory name.
2. PHP namespace convention.
3. Category representation in the filesystem.
4. Required directories inside every module.
5. Public contract location.
6. Internal implementation boundary.
7. Module service-provider convention.
8. Route ownership convention.
9. Migration ownership convention.
10. Configuration ownership convention.
11. Translation/resource ownership convention.
12. Test ownership convention.
13. Module metadata/manifest location.
14. Required versus optional dependency declaration.
15. Capability declaration model.
16. How Composer discovers module namespaces.
17. How Laravel discovers module providers.
18. How Deptrac recognizes module categories and Public/Internal boundaries.
19. How PHPStan analyses module code.
20. How module-independent tests are executed.

## Candidate Physical Model

The initial candidate is:

Modules/
  Foundation/
    SharedKernel/
    Identity/
    AccessControl/
    Audit/
    Observability/

  Platform/
    Settings/
    Files/
    Notifications/
    FeatureFlags/

  Specialized/
    Search/
    Realtime/

  Product/
    Product/
    Cart/
    Wallet/

This is a candidate only.

B1 must validate the structure before any of these modules are created.

## Candidate Module Internal Shape

A module may use:

ModuleName/
  Domain/
  Application/
  Infrastructure/
  Public/
  Presentation/
  Database/
  Config/
  Resources/
  Tests/
  module.php

Not every directory is mandatory.

The final contract must distinguish mandatory structure from optional structure.

## DDD Rule

DDD does not mean every module requires entities, aggregates, repositories, or domain services.

Technical modules may remain intentionally simple.

Rich domain patterns are introduced only when domain behavior requires them.

Architecture boundaries are mandatory; unnecessary tactical DDD ceremony is not.

## Foundation Constraint

Foundation modules contain reusable technical capabilities only.

They must contain no Product, Cart, Wallet, Order, Visit, Facility, Inspection, or other application-specific business behavior.

## Product Module Constraint

Product modules are optional consumers of the Base Platform.

Examples include:

- Product
- Cart
- Wallet

Removing those modules must not break the technical Base Platform.

## Enforcement Requirements

B1 must produce rules that can later be enforced by:

- Composer autoloading
- Deptrac
- PHPStan
- architecture scripts
- module tests
- future manifest validation

Documentation-only boundaries are insufficient.

## B1 Planned Steps

### B1.1

Define canonical filesystem and namespace structure.

### B1.2

Define Public versus Internal dependency boundaries.

### B1.3

Define module category dependency matrix.

### B1.4

Define persistence and migration ownership.

### B1.5

Define module metadata contract.

### B1.6

Update Composer, PHPStan, and Deptrac design for future Modules/ support.

### B1.7

Create architecture validation tests for the contract.

### B1.8

Validate the contract using temporary positive and negative architecture probes.

### B1.9

Record the accepted contract before implementing SharedKernel.

## Non-Goals

Do not implement during B1 planning:

- SharedKernel behavior
- Identity/authentication
- AccessControl
- Settings
- Product
- Cart
- Wallet
- Module Manager runtime
- module enable/disable lifecycle
- PostgreSQL
- Redis
- MinIO
- Docker
- UI architecture

## Exit Criteria

B1 is complete only when:

- canonical module structure is documented
- namespace rules are documented
- category dependency matrix is explicit
- Public/Internal boundary is explicit
- persistence ownership is explicit
- metadata contract is defined
- Composer strategy is defined
- PHPStan strategy is defined
- Deptrac strategy is defined
- architecture enforcement is proven with negative tests
- `composer quality` passes
- milestone is committed

## B1.1 Decision

Status: COMPLETE

The canonical module filesystem and namespace structure is now defined.

Canonical directory:

`Modules/<Category>/<Module>/`

Canonical namespace:

`Base\Modules\<Category>\<Module>`

Canonical categories:

- Foundation
- Platform
- Specialized
- Product

Only:

`<Module>\Public\*`

is directly consumable by another module.

Internal module implementation remains private.

Detailed architecture contract:

`docs/architecture/module-structure.md`

B1.1 defines structure only. No module runtime or business module has been implemented.

## B1.2 Decision

Status: COMPLETE

Modules are private by default.

Only:

`Base\Modules\<Category>\<Module>\Public\*`

may be consumed directly by another module.

A valid dependency additionally requires explicit declaration in the future module metadata.

The Public namespace is a compatibility surface, not a place for shared implementation.

Detailed contract:

`docs/architecture/module-public-boundary.md`

## B1.3 Decision

Status: COMPLETE

The canonical module category dependency matrix is defined.

Summary:

- Foundation may depend only on explicitly allowed Foundation Public contracts.
- Platform may consume Foundation Public contracts.
- Specialized may consume Foundation and explicitly allowed Platform/Specialized Public contracts.
- Product may consume Foundation, Platform, Specialized, and explicitly declared Product Public contracts.
- Foundation must never depend on Platform, Specialized, or Product.
- Platform and Specialized must never depend on Product.
- All cross-module dependencies require Public targets.
- All dependencies must be explicitly declared.
- Circular dependencies are forbidden.

Detailed contract:

`docs/architecture/module-dependency-matrix.md`

## B1.2.1 Decision

Status: COMPLETE

The Base package ownership and extension model is now defined.

Key decisions:

- Base reusable technical capabilities live under `packages/base/`.
- Product/business modules live outside Base packages under `modules/`.
- Project customization lives under project-owned extension code in `extensions/`.
- Project customization must not modify Base package internals.
- Capabilities are injected through Public contracts rather than foreign concrete classes.
- PHP Attributes may provide declarative registration metadata.
- AOP/interceptors are reserved for cross-cutting behavior.
- Optional cross-module relationships use contribution/extension contracts rather than reverse dependencies.
- Generated-project upgrade safety depends on strict ownership separation.

Detailed contract:

`docs/architecture/base-package-extension-model.md`

This decision refines and partially supersedes the original single `Modules/` physical-root assumption in B1.1.

## Current Step

B1.2.2 — Validate package, extension, attribute, AOP, and contribution boundaries before continuing the dependency matrix.
