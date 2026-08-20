# Base Package Immutability and Extension Model

## Status

ACCEPTED FOR B1 IMPLEMENTATION

## Purpose

Define how reusable Base capabilities are packaged, protected from project-specific modification, extended safely, and consumed by future projects.

The goal is to allow Base packages to evolve and upgrade without requiring generated projects to fork or modify package internals.

## Core Principle

Base-owned capabilities are treated as stable in-project packages.

Project-specific customization must not modify Base package internals.

The canonical rule is:

`Base package code is owned by Base.`

`Project customization is owned by the project.`

## Canonical Repository Areas

The intended repository model is:

```text
project/
├── packages/
│   └── base/
│       ├── Foundation/
│       ├── Platform/
│       └── Specialized/
│
├── modules/
│   └── Product/
│
├── extensions/
│   └── Base/
│
├── app/
│   └── host/composition only
│
└── docs/
```

## Base Packages

Reusable Base-owned technical capabilities live under:

`packages/base/`

Examples:

```text
packages/base/Foundation/SharedKernel
packages/base/Foundation/Identity
packages/base/Foundation/AccessControl
packages/base/Foundation/Audit

packages/base/Platform/Settings
packages/base/Platform/Notifications
packages/base/Platform/Files

packages/base/Specialized/GIS
```

These packages form the reusable Base distribution.

## Project Modules

Project-specific or reusable business modules live outside Base packages.

Canonical location:

`modules/`

Examples:

```text
modules/Product
modules/Cart
modules/Wallet
modules/Orders
```

Product modules are optional consumers of Base.

They must never become dependencies of Base Foundation or Platform packages.

## Project Extensions

Project-specific customization of Base behavior lives under:

`extensions/`

Example:

```text
extensions/Base/Identity
extensions/Base/Notifications
extensions/Base/Settings
```

Extensions customize behavior through declared extension points.

They must not patch Base package source files.

## Laravel Host

The Laravel `app/` directory remains a host/composition shell.

It may:

- boot the Base runtime;
- compose selected modules;
- register project-owned extensions;
- provide framework entrypoints.

It must not become the primary location for reusable domain or platform behavior.

## Package Immutability Rule

Project code must not directly modify:

`packages/base/**`

to introduce project-specific behavior.

Base package changes are allowed only when changing the Base product itself.

A generated project customization must instead use:

- Public contracts;
- extension points;
- contributors;
- decorators;
- strategies;
- attributes;
- capabilities;
- integration events;
- project modules.

## Ownership Rule

Every file must eventually have an ownership classification.

Conceptual ownership modes include:

- `base-owned`
- `project-owned`
- `generated-managed`
- `merge-managed`
- `protected`

Base package source defaults to:

`base-owned`

Project extensions default to:

`project-owned`

## Upgrade Safety

A future Base upgrade must be able to replace or update Base-owned package files without overwriting project-owned customization.

Project-specific behavior therefore must not be stored inside Base package implementation files.

## Extension Point

An extension point is an explicit contract allowing project or module code to contribute behavior to a Base package without modifying the package.

Examples:

- profile contributors;
- navigation contributors;
- relation contributors;
- permission contributors;
- settings contributors;
- policy contributors;
- serialization contributors;
- notification channel contributors.

Extension points must be deliberate.

Not every internal service is an extension point.

## Extension Registry

The future Base runtime may provide an Extension Registry.

Conceptually:

```text
ExtensionRegistry
├── contributors
├── decorators
├── strategies
├── relation contributions
├── metadata contributions
└── attribute-discovered extensions
```

The exact runtime implementation is defined later.

## Contributor Model

A contributor adds project-owned behavior through a stable Base contract.

Conceptual example:

```php
interface UserProfileContributor
{
    public function contribute(UserProfileDefinition $profile): void;
}
```

A project can implement:

`extensions/Base/Identity/CustomerProfileContributor`

without modifying Identity package internals.

## Decorator Model

A Base capability may allow project-owned decorators.

Conceptually:

```text
Base capability
    ↓
Project decorator
    ↓
Underlying implementation
```

Decorators are appropriate for behavior such as:

- additional validation;
- instrumentation;
- project-specific policy;
- response enrichment.

Decorators must not bypass security or data ownership rules.

## Strategy Model

Capabilities with multiple valid implementations may expose strategy contracts.

Examples:

- `storage.strategy`
- `search.strategy`
- `tenant.strategy`
- `notification.strategy`

Projects select supported strategies declaratively.

Strategy selection must not require editing Base package internals.

## Capability Injection

Modules should depend on capabilities rather than concrete provider classes whenever practical.

Conceptual consumer:

```text
requires capability: notification.send
```

Conceptual provider:

```text
provides capability: notification.send
```

The future ModuleManager/Capability Registry resolves the implementation.

Consumers must not hard-code provider implementation classes.

## Dependency Injection Rule

Constructor injection remains preferred.

However, cross-package dependencies must inject:

- Public contracts;
- capability contracts;
- strategy interfaces.

They must not inject foreign internal concrete classes.

## No Central Hard-Coded Binding Map

The Laravel host must not contain a giant hard-coded map such as:

```text
IdentityContract      -> IdentityConcrete
NotificationContract  -> TwilioConcrete
WalletContract        -> WalletConcrete
...
```

for every ecosystem capability.

Bindings should eventually be derived from:

- package/module metadata;
- capabilities;
- extension registration;
- strategy selection.

## Attributes

PHP Attributes may be used as declarative metadata where they improve discoverability and reduce central hard-coded registration.

Potential uses include:

- capability providers;
- contributors;
- listeners;
- policies;
- transactional handlers;
- auditing;
- authorization metadata;
- idempotency;
- caching;
- tracing;
- relation contributions.

Attributes describe metadata.

They must not hide critical business rules.

## Attribute Example

Conceptual example:

```php
#[Transactional]
#[Audited('wallet.transfer')]
#[Authorize('wallet.transfer')]
#[Idempotent]
final class TransferMoneyHandler
{
}
```

The exact Attributes and runtime implementation are defined later.

## AOP Model

Aspect-oriented behavior may be used for cross-cutting concerns.

Suitable concerns include:

- transactions;
- authorization;
- audit logging;
- tracing;
- metrics;
- caching;
- idempotency;
- retry policies.

AOP must not become a hidden business-rule engine.

## Business Logic Must Remain Explicit

Example business rule:

> Transfer amount must not exceed available wallet balance.

This belongs in explicit domain/application behavior.

It must not be hidden inside an interceptor or generic aspect.

## Interceptor Model

Attributes may eventually resolve to ordered interceptors around an application use case.

Conceptually:

```text
request
  ↓
authorization
  ↓
idempotency
  ↓
transaction
  ↓
application handler
  ↓
audit
  ↓
metrics/tracing
```

Ordering must be deterministic.

## Relation Contribution Model

Cross-module model relationships must not create reverse dependencies.

Forbidden example:

Identity package adding:

```php
public function wallet()
{
    return $this->hasOne(Wallet::class);
}
```

This would make Identity aware of Wallet.

The module that owns the optional relation should contribute it externally.

Conceptual example:

```text
Wallet
  contributes relation:
    target: identity.user
    relation: wallet
    resolver: WalletRelationResolver
```

Identity remains unaware of Wallet.

## Attribute-Based Relation Contribution

A future implementation may support declarative relation contributions.

Conceptual example:

```php
#[ExtendsModel('identity.user')]
final class WalletUserRelations
{
    #[Relation('wallet')]
    public function wallet(object $user): object
    {
        // relation resolution
    }
}
```

This is a conceptual design only.

The final implementation must preserve type safety and ownership.

## Relation Ownership

The relation contributor owns the optional relationship semantics.

The target module must not become dependent on the contributing module.

Example:

- Identity does not depend on Wallet.
- Wallet may contribute an optional relationship to Identity's extension surface.

## Cross-Module Database Rule Still Applies

A relation contribution does not grant unrestricted persistence access.

Table ownership remains explicit.

The contributing module may use its own persistence to resolve the relation.

It must not mutate the target module's private tables.

## Shared Module Concept

A capability used by many modules does not automatically belong in SharedKernel.

Choose among:

- Foundation package;
- Platform package;
- capability contract;
- extension point;
- project module.

SharedKernel remains reserved for genuinely stable primitives.

## Injection Into Specific Modules

A project may configure an extension to apply only to selected modules/capabilities.

Conceptual metadata:

```text
extension:
  capability: audit.enricher
  targets:
    - wallet
    - orders
```

The target selection must be declarative.

The extension must not contain a hard-coded central switch statement listing every known module.

## Conditional Contributions

Extensions may be conditional on:

- module availability;
- capability availability;
- project profile;
- environment policy;
- feature flag.

Conditions must be explicit and validated.

## Optional Module Safety

An extension targeting an optional module must not break boot when that module is absent.

Example:

If Wallet is disabled:

- Wallet relations are not registered;
- Wallet contributors are not registered;
- Identity still boots;
- no missing-class error occurs.

## Event Contribution

Modules may subscribe to Public integration events without modifying the publisher.

Example:

```text
Orders publishes OrderPlacedV1

Wallet may consume it
Notifications may consume it
Reporting may consume it
```

Orders does not import any consumer.

## Permission Contribution

A module owns the permissions describing its capability.

AccessControl owns permission evaluation.

Example:

Wallet may contribute:

```text
wallet.view
wallet.transfer
wallet.adjust
```

without adding Wallet-specific behavior to AccessControl package internals.

## Settings Contribution

Settings provides the generic settings engine.

A module contributes its own settings definitions.

Example:

Wallet may contribute:

```text
wallet.minimum_transfer
wallet.currency_policy
```

Settings must not contain Wallet-specific logic.

## Navigation Contribution

Modules may contribute navigation metadata to an Experience layer.

Foundation packages must not become presentation-aware.

Disabling a module removes its contributions.

## Validation Contribution

Modules may expose validation extension points where domain ownership remains clear.

Project-specific validation must not require editing Base-owned validation classes.

## Security Constraints

Extensions must not be allowed to weaken mandatory security invariants silently.

Project extensions must not disable:

- authorization;
- tenant isolation;
- encryption requirements;
- secret handling;
- production safety policy

without an explicit supported policy mechanism.

## Extension Compatibility

Extensions depend on explicit public extension contracts.

A Base upgrade may reject an incompatible extension.

Future compatibility validation must check:

- Base package version;
- extension contract version;
- target capability version.

## Extension Manifest

Project extensions may eventually own metadata describing:

- identity;
- target package/capability;
- required version;
- provided contributions;
- activation conditions.

Exact schema is deferred.

## Package Manifest

Base packages will eventually declare:

- package identity;
- version;
- category;
- Public capabilities;
- extension points;
- dependencies;
- lifecycle metadata.

Exact manifest design is defined later.

## Generated Project Rule

The future Project Factory must distinguish:

- Base package files
- Project module files
- Project extension files
- Generated managed files

It must not place project customization into Base-owned package files.

## Generated File Ownership

Future generated-file metadata must allow upgrade tooling to determine whether a file may be:

- replaced;
- merged;
- preserved;
- rejected due to project ownership.

## Project Factory Customization Flow

Conceptually:

```text
Blueprint
  ↓
select Base packages
  ↓
select Product modules
  ↓
select strategies
  ↓
generate project extensions
  ↓
generate project-owned configuration
```

The generator must not fork Base package internals for ordinary customization.

## Package Update Flow

Conceptually:

```text
new Base version
  ↓
compatibility validation
  ↓
package update
  ↓
extension compatibility validation
  ↓
tests
  ↓
project diff
```

Project extensions remain outside the updated package.

## Anti-Patterns

Forbidden:

- editing Base package source for one project;
- copying Base classes into project code and modifying them;
- `project-specific if project == X` logic inside Base packages;
- giant central binding files listing concrete implementations;
- foreign internal class injection;
- magic service-locator dependencies;
- adding optional module relations directly into lower-level Base models;
- using AOP to hide business rules;
- making every service an extension point;
- moving optional business code into SharedKernel.

## B1.2.1 Decision

The accepted model is:

- Base reusable capabilities are stable in-project packages under `packages/base/`.
- Project/business modules live outside Base packages under `modules/`.
- Project-specific customization lives outside Base packages under `extensions/`.
- Base package internals are not modified for ordinary project customization.
- Extension occurs through declared contracts, contributors, decorators, strategies, attributes, capabilities, events, and extension registries.
- Optional cross-module relations are contributed by the owning optional module without introducing reverse dependencies.
- AOP is reserved for explicit cross-cutting concerns.
- Capability injection replaces hard-coded provider coupling where practical.
- Generated-project upgrade safety depends on strict ownership separation.

This decision supersedes the earlier assumption that all reusable and Product modules must share one physical `Modules/` root.
