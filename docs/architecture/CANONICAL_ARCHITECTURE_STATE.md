# Canonical Architecture State

## Status

ACCEPTED ARCHITECTURE SOURCE OF TRUTH

## Mission

This document is the canonical reconciliation point for Base
architecture before implementation phases.

## Final Repository Model

``` text
packages/base/
    Foundation/
    Platform/
    Specialized/

extensions/
    Base/

modules/
    Product/
    Cart/
    Wallet/
    Orders/

app/
    Host composition only
```

## Ownership Model

-   packages/base: Base-owned stable packages.
-   extensions: project-owned customization.
-   modules: project/business capabilities.
-   app: host composition only.

## Base Package Rules

Base packages contain reusable capabilities.

They must not depend on project modules or project extensions.

## Module Rules

Modules consume Base capabilities through Public contracts.

Modules must not modify Base package internals.

## Extension Rules

Extensions customize Base behavior through:

-   Public contracts
-   Contributors
-   Decorators
-   Strategies
-   Attributes
-   Capabilities
-   Events

## Dependency Direction

``` text
modules
    |
    v
extensions
    |
    v
packages/base
```

Reverse dependencies are forbidden.

## Public Boundary

Only Public contracts cross boundaries.

Forbidden:

-   foreign internal classes
-   foreign repositories
-   foreign models
-   foreign infrastructure adapters

## Capability Model

Consumers depend on capabilities, not concrete providers.

Example:

`notification.send`

## Attributes and AOP

Attributes provide metadata.

AOP is limited to cross-cutting concerns:

-   authorization
-   transactions
-   audit
-   tracing
-   metrics
-   caching
-   idempotency

Business rules remain explicit.

## Persistence Ownership

Every table has one owner.

Rules:

-   migrations belong to owners
-   foreign table writes are forbidden
-   private models stay private
-   cross-module access uses contracts

## Manifest Model

Manifests define:

-   identity
-   version
-   dependencies
-   capabilities
-   extension points
-   ownership
-   lifecycle metadata

## Upgrade Safety

Base upgrades must not overwrite project customization.

Customization belongs outside Base packages.

## Enforcement

The architecture is enforced by:

-   Deptrac
-   PHPStan
-   manifest validation
-   architecture scripts
-   CI quality gates

## Superseded Layout

The old single layout:

``` text
Modules/
Foundation
Platform
Product
```

is superseded.

The final model separates:

-   packages/base
-   extensions
-   modules

## B1 Exit Criteria

B1 completes when:

-   architecture contracts are validated
-   dependency rules are enforced
-   ownership rules are explicit
-   persistence ownership is defined
-   capability and manifest models are defined
-   quality gates pass

## Next Phase

B2 Runtime Foundation:

-   Package loader
-   Manifest parser
-   Extension registry
-   Capability registry
-   Dependency resolver
-   Runtime discovery
