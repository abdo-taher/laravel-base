# Module Manifest Contract

## Status

ACCEPTED FOR B1 IMPLEMENTATION

## Purpose

Define the metadata contract used by Base packages, extensions, and
project modules.

The manifest is the source of truth for:

-   identity;
-   category;
-   version;
-   dependencies;
-   capabilities;
-   lifecycle information;
-   ownership metadata.

## Core Principle

Every package and module declares itself.

Runtime discovery must not depend on hidden assumptions or hard-coded
registration.

## Manifest Location

Each package or module owns:

    module.json

Examples:

    packages/base/Foundation/Identity/module.json

    modules/Wallet/module.json

    extensions/Base/Identity/module.json

## Required Identity

Every manifest must define:

``` json
{
  "name": "Identity",
  "category": "Foundation",
  "version": "1.0.0"
}
```

## Category Values

Supported categories:

-   Foundation
-   Platform
-   Specialized
-   Product
-   Extension

## Versioning

Versions follow semantic versioning:

    MAJOR.MINOR.PATCH

Breaking public contract changes require a major version.

Compatible additions use minor versions.

Bug fixes use patch versions.

## Ownership Metadata

The manifest declares ownership:

Example:

``` json
{
  "ownership": "base-owned"
}
```

Possible ownership values:

-   base-owned
-   project-owned
-   generated-managed
-   protected

## Dependencies

Dependencies are explicit.

Example:

``` json
{
  "dependencies": {
    "required": [
      {
        "capability": "identity.current-principal",
        "version": "^1.0"
      }
    ]
  }
}
```

A dependency must define:

-   target capability or package;
-   version constraint;
-   required/optional behavior.

## Optional Dependencies

Optional dependencies define fallback behavior.

Example:

``` json
{
  "optional": [
    {
      "capability": "notification.send",
      "version": "^1.0"
    }
  ]
}
```

Missing optional capabilities must not break boot.

## Capabilities Provided

A manifest declares capabilities it provides.

Example:

``` json
{
  "provides": [
    {
      "capability": "notification.send",
      "version": "1.0.0"
    }
  ]
}
```

Consumers depend on capabilities instead of concrete implementations.

## Extension Points

Packages declare available extension points.

Example:

``` json
{
  "extension_points": [
    "permission.contributor",
    "settings.contributor",
    "relation.contributor"
  ]
}
```

## Contributions

Extensions may declare contributions.

Example:

``` json
{
  "contributes": [
    "wallet.permissions",
    "wallet.relations"
  ]
}
```

## Events

Modules declare integration events.

Example:

``` json
{
  "events": {
    "publishes": [
      "wallet.transfer.completed.v1"
    ],
    "consumes": [
      "identity.user.disabled.v1"
    ]
  }
}
```

Events must be versioned.

## Persistence Ownership

The manifest may declare owned persistence.

Example:

``` json
{
  "data": {
    "tables": [
      "wallet_accounts",
      "wallet_transactions"
    ]
  }
}
```

Table ownership must be unique.

## Permissions

Modules may declare owned permissions.

Example:

``` json
{
  "permissions": [
    "wallet.view",
    "wallet.transfer"
  ]
}
```

AccessControl evaluates permissions but does not own business
permissions.

## Configuration

Modules may declare configuration metadata.

Example:

``` json
{
  "configuration": [
    "wallet.currency",
    "wallet.limits"
  ]
}
```

The module owns configuration meaning.

## Lifecycle Metadata

Future lifecycle management may use:

``` json
{
  "lifecycle": {
    "enableable": true,
    "disable_policy": "preserve-data"
  }
}
```

## Profiles

A manifest may define compatible project profiles.

Example:

``` json
{
  "profiles": [
    "api",
    "saas",
    "enterprise"
  ]
}
```

## Security Metadata

Security-sensitive modules may declare:

-   required privileges;
-   security classification;
-   protected capabilities.

Example:

``` json
{
  "security": {
    "classification": "critical"
  }
}
```

## Compatibility Rules

Manifest validation checks:

-   dependency availability;
-   version compatibility;
-   category rules;
-   capability conflicts;
-   lifecycle conflicts.

## Dependency Validation

The validator must reject:

-   undeclared dependencies;
-   forbidden category dependencies;
-   cycles;
-   incompatible versions.

## Runtime Usage

Future ModuleManager uses manifests to:

-   discover packages;
-   validate dependencies;
-   resolve capabilities;
-   load extensions;
-   generate project plans.

## Project Factory Usage

The Project Factory uses manifests to:

-   display available modules;
-   validate selections;
-   resolve required capabilities;
-   generate lock files.

## Lock File Relationship

A generated project will eventually contain a resolved lock file
describing:

-   selected packages;
-   selected modules;
-   versions;
-   capabilities;
-   generated decisions.

The lock file is separate from the manifest.

## Anti Patterns

Forbidden:

-   hidden dependencies;
-   hard-coded module registration;
-   editing manifests manually after generation without validation;
-   declaring every dependency optional;
-   exposing internal implementation through metadata.

## B1 Manifest Decision

The manifest is the authoritative declaration boundary for:

-   identity;
-   dependencies;
-   capabilities;
-   extension points;
-   events;
-   ownership;
-   lifecycle metadata.

The manifest describes what a component is.

It does not contain business logic.

## Non Goals

This document does not implement:

-   manifest parser;
-   ModuleManager;
-   capability resolver;
-   lock generation;
-   project generator.
