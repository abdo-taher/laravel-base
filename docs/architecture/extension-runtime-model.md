# Extension Runtime Model

## Status

ACCEPTED FOR B1 IMPLEMENTATION

## Purpose

Define how Base packages can be extended by projects and modules without modifying Base package source code.

This model defines:

- extension discovery
- attributes
- contributors
- decorators
- strategies
- capability injection
- relation contributions
- AOP/interceptors

The goal is to keep Base packages stable while allowing unlimited project customization through controlled extension points.

---

## Core Principle

Base packages expose contracts.

Projects and modules provide implementations.

Base packages never depend on project-specific extensions.

The dependency direction is:

```text
Base Package
        |
        v
Public Extension Contract
        |
        v
Project Extension
```

The reverse direction is forbidden.

---

# Extension Registry

## Purpose

The Extension Registry is responsible for discovering and managing extension points.

It manages:

- contributors
- decorators
- strategies
- capability providers
- relation contributors
- metadata providers

The registry must not become a hard-coded service map.

---

## Extension Discovery

Extensions may be discovered through:

- package metadata
- module metadata
- PHP attributes
- capability declarations
- explicit registration contracts

Discovery must be deterministic.

The same project definition must always produce the same extension graph.

---

# Attribute Model

## Purpose

PHP Attributes are used as declarative metadata.

Attributes describe:

"What does this class provide?"

They must not hide:

"How business logic works."

---

## Allowed Attribute Uses

Attributes may define:

- capability providers
- contributors
- listeners
- policies
- authorization metadata
- transaction boundaries
- audit metadata
- tracing metadata
- caching metadata
- idempotency metadata

---

## Example

```php
#[Transactional]
#[Audited]
#[Authorize]
final class TransferMoneyHandler
{
}
```

The attributes describe cross-cutting behavior.

The actual wallet transfer rule remains inside domain/application logic.

---

# Contributor Model

## Purpose

Contributors allow modules and projects to add behavior without modifying Base packages.

Examples:

- permission contributors
- settings contributors
- navigation contributors
- relation contributors
- validation contributors
- serialization contributors

---

## Ownership Rule

The module that owns the capability owns its contributor.

Example:

Wallet owns:

```text
WalletPermissionContributor
```

AccessControl owns:

- permission storage
- permission evaluation
- authorization engine

AccessControl does not contain Wallet business rules.

---

# Capability Injection Model

## Principle

Consumers depend on capabilities.

Consumers do not depend on concrete implementations.

---

## Example

Consumer requires:

```text
notification.send
```

Provider declares:

```text
provides notification.send
```

The runtime resolves the provider.

---

## Forbidden

A central provider containing:

```text
NotificationService = TwilioNotificationService
WalletService = WalletService
SearchService = ElasticSearchService
```

for every project.

This creates hidden coupling.

---

## Preferred

Capabilities are resolved through:

- manifests
- registries
- contracts
- strategies

---

# Strategy Model

## Purpose

Strategies allow multiple implementations of the same capability.

Examples:

```text
storage.strategy

search.strategy

notification.strategy

payment.strategy
```

---

## Rules

Strategies must:

- implement a public contract
- be selectable declaratively
- be version compatible
- remain replaceable

---

# Decorator Model

## Purpose

Decorators allow project-specific behavior around Base capabilities.

Good examples:

- extra validation
- audit enrichment
- metrics
- policy checks

---

## Forbidden

Decorators must not bypass:

- authorization
- tenant isolation
- security rules
- data ownership

---

# AOP Model

## Purpose

AOP/interceptors are used only for cross-cutting concerns.

---

## Allowed Concerns

Examples:

- transactions
- authorization
- auditing
- tracing
- metrics
- caching
- idempotency
- retry policies

---

## Forbidden

Business rules must not be hidden inside AOP.

Example:

Bad:

```text
#[AutomaticallyApproveOrder]
```

Good:

```text
#[Transactional]
#[Audited]
#[Authorize]
```

---

# Interceptor Ordering

Execution order must be deterministic.

Conceptual flow:

```text
Request

↓

Authorization

↓

Idempotency

↓

Transaction

↓

Application Handler

↓

Audit

↓

Metrics
```

The final ordering system is defined with the runtime implementation.

---

# Relation Contribution Model

## Problem

Modules must not modify another module's models.

Forbidden:

```php
class User
{
    public function wallet()
    {
        return $this->hasOne(Wallet::class);
    }
}
```

inside Identity.

This creates reverse dependency.

---

## Solution

The owning module contributes the relation.

Example:

```text
Wallet

provides:

UserWalletRelationContributor
```

Identity remains unaware of Wallet.

---

## Rules

A relation contribution:

- belongs to the owning module
- uses declared extension contracts
- does not modify foreign package files
- does not bypass data ownership rules

---

# Module Targeted Injection

Extensions may target specific capabilities/modules.

Example:

```yaml
extension:
  capability: audit.enrichment

  targets:
    - wallet
    - orders
```

The extension must not contain:

```php
if ($module === 'Wallet')
```

logic.

---

# Optional Extension Safety

If a target module is disabled:

The system must:

- skip unavailable extensions
- avoid boot failures
- avoid missing-class errors
- keep remaining modules operational

---

# Package Boundary Rule

Base packages may expose:

- public contracts
- extension contracts
- capability contracts

Base packages must not expose internal implementation as extension points.

---

# Extension Versioning

Extensions depend on explicit contracts.

Compatibility validation checks:

- Base package version
- extension contract version
- capability version

---

# Testing Requirements

Every extension mechanism requires:

## Positive Tests

Example:

Extension implements valid Public contract.

Expected:

PASS

---

## Negative Tests

Example:

Extension imports Base internal class.

Expected:

FAIL

---

# Enforcement

Future enforcement uses:

- Deptrac
- PHPStan rules
- manifest validation
- architecture tests

Rules:

- extensions may depend on Public contracts
- extensions may not depend on Base internals
- Base packages may not depend on extensions

---

# Anti Patterns

Forbidden:

- modifying Base package files
- giant central binding providers
- hidden service locator dependencies
- AOP replacing domain logic
- relations injected by modifying foreign models
- making every class an extension point
- using extensions to bypass architecture rules

---

# B1.2.2 Decision

The accepted extension runtime model:

1. Base packages expose explicit extension contracts.
2. Projects customize through extensions.
3. Attributes provide metadata discovery.
4. AOP handles only cross-cutting concerns.
5. Capabilities replace concrete provider coupling.
6. Relations are contributed externally.
7. Injection is metadata-driven.
8. Base packages never depend on project extensions.

---

# Non Goals

This step does not implement:

- ExtensionRegistry runtime
- Attribute scanner
- ModuleManager
- CapabilityRegistry
- real packages
- real modules

This document defines the architecture contract only.