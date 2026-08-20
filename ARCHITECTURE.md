# Base Platform Architecture

## Architecture Style

Base uses a governed modular monolith.

Microservices are not the default.

## Core Dependency Direction

Experience
→ Product
→ Platform / Specialized
→ Foundation

Reverse dependencies are forbidden.

## Module Boundary

Conceptual structure:

Modules/<Category>/<Module>/
├── Public/
├── Domain/
├── Application/
├── Infrastructure/
├── Interfaces/
├── Database/
├── Tests/
├── module.json
└── README.md

Not every folder is mandatory.

Only Public is accessible by other modules.

## Foundation

Foundation contains reusable technical capabilities only.

Foundation must never depend on Product modules.

## Platform

Platform contains optional reusable horizontal capabilities.

Platform must never depend on Product modules.

## Product

Product modules contain business capabilities.

Examples include Product, Cart, Wallet, Orders, and project-specific domains.

Product modules may consume Foundation and Platform public contracts.

## Data Ownership

Each table has exactly one owning module.

Modules must never write directly to foreign tables.

## Collaboration

Synchronous:
- Public contracts
- Queries
- Explicitly exposed application contracts

Asynchronous:
- Versioned integration events

Read aggregation:
- Projections
- Read models

## Runtime Independence

Generated projects must not require the Project Factory or Control Plane during normal runtime.
