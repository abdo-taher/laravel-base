# Module Dependency Matrix

## Status

ACCEPTED FOR B1 IMPLEMENTATION

## Purpose

Define the allowed dependency direction between Base packages,
extensions, and project modules.

A valid dependency requires an allowed direction, a Public contract
target, and an explicit declaration.

## Ownership Layers

    packages/base/
            |
            v
    extensions/
            |
            v
    modules/

## Dependency Direction

The architecture direction is:

    modules
       |
       v
    extensions
       |
       v
    packages/base

Base packages never depend on project code.

## Base Rules

Foundation may depend only on approved Foundation Public contracts.

Platform may depend on Foundation Public contracts.

Specialized may depend on Foundation and approved Platform Public
contracts.

Product modules may consume Foundation, Platform, and Specialized Public
contracts.

## Forbidden Dependencies

Forbidden:

-   Foundation -\> Platform
-   Foundation -\> Product
-   Foundation -\> Extension
-   Platform -\> Product
-   Extension -\> Base Internal
-   Product -\> Foreign Internal implementation

## Capability Model

Consumers should depend on capabilities instead of concrete
implementations.

Example:

    requires capability:
    notification.send

The runtime resolves the provider.

## Circular Dependencies

Circular dependencies are forbidden.

Resolve cycles using:

-   events
-   capabilities
-   extracted contracts
-   orchestration

## Public Contract Rule

Cross-boundary access is allowed only through:

    <Module>\Public\*

Internal namespaces remain private.

## Runtime Injection

Cross-module injection must use:

-   Public interfaces
-   Capability contracts
-   Strategy interfaces

Concrete foreign implementations are forbidden.

## Relation Contributions

Modules contribute optional relationships through extension contracts.

A module must not modify another module's models to create relations.

## Enforcement

Future enforcement uses:

-   Deptrac
-   PHPStan rules
-   manifest validation
-   architecture tests

## Negative Tests

The architecture validator must reject:

-   reverse category dependencies
-   foreign internal imports
-   circular dependencies
-   undeclared dependencies

## Positive Tests

The architecture validator must allow:

-   Platform -\> Foundation Public
-   Product -\> Platform Public
-   Extension -\> Base Public Contract

## B1.3 Decision

The dependency matrix is mandatory for Base architecture.

All dependencies require:

-   Public target
-   Explicit declaration
-   Compatible version
-   No cycle
