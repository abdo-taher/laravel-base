# Capability Model

## Status

ACCEPTED FOR B1 IMPLEMENTATION

## Purpose

Define how Base packages, extensions, and modules communicate through
capabilities instead of concrete implementations.

The capability model is the foundation for dynamic dependency resolution
and project generation.

## Core Principle

Consumers depend on capabilities.

Consumers do not depend on implementation classes.

Example:

Bad:

    Cart
      depends on
    TwilioNotificationService

Good:

    Cart

    requires:

    notification.send

The runtime resolves the provider.

## Capability Definition

A capability represents a stable technical or business-neutral contract.

A capability has:

-   name;
-   version;
-   contract;
-   providers;
-   consumers;
-   availability rules.

Example:

    notification.send

## Capability Ownership

The component providing a capability owns:

-   capability contract;
-   documentation;
-   compatibility rules;
-   versioning.

Consumers only depend on the contract.

## Capability Provider

A provider implements a capability.

Example:

    Capability:

    notification.send


    Providers:

    EmailNotificationProvider
    SmsNotificationProvider
    PushNotificationProvider

The consumer does not know which provider is selected.

## Capability Contract

A capability contract should define:

-   input;
-   output;
-   errors;
-   compatibility rules;
-   security requirements.

It must not expose provider implementation details.

## Capability Registry

The future Capability Registry resolves:

-   available capabilities;
-   providers;
-   versions;
-   priorities;
-   fallback rules.

It works with:

-   package manifests;
-   module manifests;
-   extension metadata.

## Provider Discovery

Providers may be discovered through:

-   manifests;
-   PHP Attributes;
-   extension registration;
-   service metadata.

Discovery must not require hard-coded application maps.

## Attribute Provider Example

Conceptual:

``` php
#[CapabilityProvider('notification.send')]
final class EmailNotificationProvider
{
}
```

The runtime registers the provider.

## Version Resolution

Capabilities use semantic versioning.

Example:

Consumer requires:

    notification.send ^1.0

Available providers:

    1.2.0
    1.5.0
    2.0.0

The resolver selects a compatible version.

## Multiple Providers

Multiple providers may exist.

Selection may depend on:

-   project configuration;
-   profile;
-   environment;
-   priority;
-   strategy selection.

The selection must be deterministic.

## Required Capabilities

Required capability:

-   must exist;
-   must be compatible;
-   must be available before boot;
-   failure prevents activation.

Example:

Authentication capability.

## Optional Capabilities

Optional capability:

-   may be absent;
-   requires explicit fallback;
-   must not break boot.

Example:

Realtime notifications.

## Security Critical Capabilities

Security capabilities must fail closed.

Examples:

-   authentication;
-   authorization;
-   tenant isolation;
-   encryption;
-   secret access.

They must not silently use insecure fallback providers.

## Capability Injection

Consumers receive capability contracts.

Example:

``` php
interface NotificationSender
{
}
```

The runtime provides the implementation.

The consumer does not instantiate providers.

## No Concrete Provider Coupling

Forbidden:

``` php
new AwsNotificationProvider();
```

Forbidden:

``` php
TwilioNotificationService $service;
```

Allowed:

``` php
NotificationSender $sender;
```

## Capability Dependencies

Modules declare required capabilities.

Example:

``` json
{
  "requires": [
    {
      "capability": "notification.send",
      "version": "^1.0"
    }
  ]
}
```

## Capability And Modules

Modules depend on capabilities.

They should not depend on provider modules unless the architecture
explicitly requires it.

Example:

    Orders

    requires:

    payment.process

not:

    Orders

    depends on:

    StripePaymentModule

## Capability And Extensions

Extensions may provide additional capability providers.

Example:

    Project Extension

    provides:

    notification.send

The Base package remains unchanged.

## Capability Fallback

Fallback behavior must be explicit.

Example:

Allowed:

    notification.send unavailable

    use disabled-notification mode

Forbidden:

    authorization unavailable

    allow access

## Capability Lifecycle

Future lifecycle management validates:

-   provider availability;
-   dependency graph;
-   compatibility;
-   enable/disable state.

## Capability Events

Capability changes may produce events:

-   capability.available;
-   capability.disabled;
-   capability.updated.

Consumers must react safely.

## Capability Testing

Every capability requires:

Positive tests:

-   valid provider resolves;
-   contract works.

Negative tests:

-   incompatible version rejected;
-   missing required capability rejected;
-   forbidden provider selected rejected.

## Capability Security

Capability resolution must not bypass:

-   authorization;
-   tenant boundaries;
-   ownership rules;
-   audit requirements.

## Capability And Project Factory

The Project Factory uses capabilities to:

-   calculate required modules;
-   validate selections;
-   resolve dependencies;
-   generate project plans.

Example:

Selecting Cart may require:

    catalog.read
    payment.process
    notification.send

## Capability Locking

Generated projects should record resolved capabilities.

Example:

    project.lock

    capability:
     notification.send

    provider:
     email.notification

    version:
     1.2.0

## Anti Patterns

Forbidden:

-   concrete provider coupling;
-   hidden capability requirements;
-   giant provider switch statements;
-   insecure fallback;
-   capability names that expose implementation;
-   using capabilities to hide business ownership.

## B1 Capability Decision

The capability model is:

-   consumers depend on contracts;
-   providers implement capabilities;
-   resolution is metadata driven;
-   versions are explicit;
-   providers are replaceable;
-   security capabilities fail closed;
-   project generation uses capabilities for dependency resolution.

## Non Goals

This document does not implement:

-   CapabilityRegistry runtime;
-   provider resolver;
-   manifest parser;
-   ModuleManager;
-   runtime package loading.
