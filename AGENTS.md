# Agent Engineering Contract

## Required Reading Order

Before modifying the repository, read:

1. AGENTS.md
2. BUILD_PLAN.md
3. ARCHITECTURE.md
4. SECURITY.md
5. CONTRIBUTING.md
6. Relevant ADRs
7. Relevant module README
8. Relevant tests

## Mandatory Workflow

Every change follows:

Discover
→ Plan
→ Implement smallest safe slice
→ Validate
→ Document
→ Report

Before implementation, record the plan in BUILD_PLAN.md or the relevant phase/task document.

## Architecture Rules

- Modules are closed components.
- Only another module's Public namespace may be consumed.
- Internal Domain, Application, Infrastructure, Interfaces, Database, and Tests namespaces are private.
- No direct cross-module Eloquent model access.
- No cross-module table writes.
- No cross-module migrations.
- No circular dependencies.
- Platform modules cannot depend on Product modules.
- Foundation modules cannot depend on Platform or Product modules.
- SharedKernel must remain minimal.
- Optional dependencies require explicit fallback behavior.
- Authentication, authorization, encryption, tenancy isolation, and secret handling must fail closed.

## DDD Rule

Use DDD where business or domain complexity requires it.

Do not create artificial Entities, Aggregates, Repositories, or Services merely to satisfy folder conventions.

Boundaries are mandatory. DDD ceremony is not.

## Business-Free Base Rule

Foundation and Platform modules must contain no product-specific business rules.

Product and reusable business building blocks are optional modules.

## Validation Rule

Never mark a phase or task complete unless its required checks have actually passed.

Never weaken a test, architecture rule, or security check simply to make the build green.

## Git Rule

Do not commit generated runtime artifacts, secrets, local databases, logs, caches, vendor dependencies, or environment files.

## Security Rule

Never expose secrets in output, logs, documentation, fixtures, or source control.