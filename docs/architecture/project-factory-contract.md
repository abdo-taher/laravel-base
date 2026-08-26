# Project Factory Architecture Contract

## Overview
The Project Factory is an independent, non-runtime Tooling layer responsible for deterministic generation of new project architectures based on the Base monorepo framework.

## Layer Boundaries
- **Layer Name:** `Tooling.ProjectFactory`
- **Allowed Dependencies:** `Foundation` (Public capabilities only, specifically Manifest & DependencyResolver), standard PHP libraries.
- **Forbidden Dependencies:** `Platform`, `Product`, `Specialized`. The Factory analyzes their manifests dynamically but MUST NOT statically import their code.
- **Reverse Dependencies:** Absolutely NO layer in Base (`Foundation`, `Platform`, `Product`, `Specialized`) may import or depend on `Tooling`.

## Core Entities
1. **ProjectDefinition:** A declarative value object defining the desired namespace, modules, and capabilities.
2. **GenerationPlan:** A deterministic execution graph listing filesystem mutations and resolved dependency nodes.
3. **Materializer:** An engine that translates a `GenerationPlan` into an atomic filesystem swap via a temporary staging directory.

## Transitive Resolution
The Factory MUST NOT invent a new dependency resolver. It MUST aggregate requested capabilities and pass them as seeds to `Base\Foundation\DependencyResolver\Application\ManifestDependencyResolver`.

## Execution Safety
- Factory operations MUST use a staging workspace.
- Operations MUST fail fast if target paths exist without explicit `--force`.
- Secrets MUST NEVER be logged, cached, or written to source-controlled template files.
