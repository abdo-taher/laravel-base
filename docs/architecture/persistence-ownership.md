# Persistence Ownership Model

## Status

ACCEPTED FOR B1 IMPLEMENTATION

## Purpose

Define ownership rules for databases, tables, migrations, factories,
seeders, and persistence access across Base packages, extensions, and
project modules.

The goal is to prevent hidden database coupling and keep modules
independently replaceable.

## Core Principle

Every persistent data structure has exactly one owner.

The owning package or module is responsible for:

-   schema ownership
-   migrations
-   persistence rules
-   data lifecycle
-   archival policy
-   retention policy

Other components must not directly modify owned data.

## Ownership Layers

The ownership model follows:

    packages/base
            |
            v

    extensions

            |
            v

    modules

Base packages own only their own technical data.

Project modules own business data.

Extensions contribute behavior but do not own foreign persistence.

## Table Ownership Rule

Every table must have one declared owner.

Example:

    users
    owner:
    Foundation.Identity

    wallet_accounts
    owner:
    Product.Wallet

A table cannot have multiple owners.

## Forbidden Access

A module must not:

-   write another module's tables;
-   update another module's rows directly;
-   import another module's Eloquent models;
-   depend on another module's repositories;
-   create hidden database joins as integration contracts.

## Allowed Data Collaboration

Cross-module collaboration uses:

-   Public query contracts;
-   Public command contracts;
-   DTOs;
-   projections;
-   integration events;
-   capability contracts.

## Migration Ownership

Migrations belong to the owning component.

Example:

    packages/base/Foundation/Identity/Database/Migrations

    modules/Wallet/Database/Migrations

A migration may only modify tables owned by that component.

## Cross-Module Migration Rule

Forbidden:

    Wallet migration
        modifies
    Identity users table

Correct:

    Wallet migration
        creates wallet tables

    Wallet
        references Identity through contracts

## Factories and Seeders

Factories and seeders belong to the owner.

Allowed:

    Wallet/Database/Factories
    Wallet/Database/Seeders

Forbidden:

    Wallet seeder
    creates
    Identity private records

## Repository Ownership

Repositories are private implementations.

A module may expose:

    Public Contracts

but not:

    Infrastructure Repository

Example:

Allowed:

    ProductCatalogQuery

Forbidden:

    ProductRepository

from another module.

## Read Access

Cross-module reads should use:

-   query contracts;
-   read models;
-   projections;
-   integration events.

Direct foreign model access is forbidden.

## Write Access

Cross-module writes require:

-   public command contract;
-   capability contract;
-   integration workflow.

Direct database mutation is forbidden.

## Relationship Rule

Database relationships must not create reverse dependencies.

Forbidden:

Identity model knows Wallet model.

Allowed:

Wallet contributes an optional relationship extension.

## Database Technology Independence

Public contracts must not expose:

-   SQL;
-   database builders;
-   Eloquent models;
-   database connections.

Persistence technology remains an internal implementation detail.

## Data Classification

Future ownership metadata should support:

-   public data;
-   internal data;
-   sensitive data;
-   confidential data.

Sensitive data requires additional security rules.

## Retention and Lifecycle

The owner defines:

-   retention;
-   archival;
-   deletion;
-   migration strategy.

Other modules cannot delete another owner's data.

## Backup and Recovery

Critical modules should declare:

-   backup requirements;
-   recovery expectations;
-   restoration procedures.

## Project Factory Rules

Future generation must validate:

-   selected modules do not have conflicting table ownership;
-   migrations belong to owners;
-   dependencies match manifests;
-   required storage capabilities exist.

## Module Disable Rules

Disabling a module must not silently delete its data.

The lifecycle policy must define:

-   preserve;
-   archive;
-   export;
-   migration.

## Enforcement

Future enforcement uses:

-   module manifests;
-   table ownership registry;
-   migration validators;
-   static analysis;
-   architecture tests;
-   CI checks.

## Negative Tests

The validator must reject:

-   Module A migration altering Module B table;
-   Module A importing Module B model;
-   Module A writing Module B repository;
-   duplicate table ownership.

## Positive Tests

The validator must allow:

-   module accessing its own persistence;
-   module using another module Public query contract;
-   module consuming integration events.

## B1.4 Decision

The accepted persistence ownership model:

-   every table has one owner;
-   migrations belong to owners;
-   persistence implementations are private;
-   cross-module access uses contracts;
-   direct foreign table access is forbidden;
-   disabling modules preserves data by policy.

## Non Goals

This document does not implement:

-   migration runner;
-   table registry runtime;
-   database abstraction;
-   archival engine;
-   backup automation.
