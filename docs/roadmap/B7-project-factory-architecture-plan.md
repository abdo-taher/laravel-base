# B7 Project Factory Architecture Plan

## 1. Factory Classification
The Project Factory is an independent **Tooling Layer** (`Base.Tooling.ProjectFactory`). It is strictly isolated from the production runtime of Base applications. It is not a Foundation capability or a Product feature; it is an orchestrator that analyzes Base capabilities and builds independent project runtimes.

## 2. Factory Responsibility
- Parse and validate `ProjectDefinition` models.
- Utilize Foundation's `ManifestDependencyResolver` to resolve requested capabilities and modules.
- Produce a deterministic `GenerationPlan` describing exactly what will be mutated.
- Materialize files safely (scaffold modules, apply templates).
- Provide dry-run and validation capabilities.

## 3. Explicit Non-Responsibilities
- The Factory will **not** manage ongoing deployments (this belongs to CI/CD orchestration).
- The Factory will **not** modify runtime databases directly (it manages generation-time config, not runtime data).
- The Factory will **not** enforce Product business logic.
- The Factory will **not** reinvent dependency resolution (must reuse `Foundation.DependencyResolver`).

## 4. Dependency Graph & Direction
- **Factory** -> Depends on `Foundation` (for Resolvers and Manifests) and `Templates`.
- **Factory** -> Does *not* depend on `Platform`, `Specialized`, or `Product`.
- **Host Application** -> Never depends on `Factory`.
- **Product Modules** -> Never depend on `Factory`.

## 5. ProjectDefinition Proposal
A framework-independent declarative model (e.g., `project.json` or equivalent object).
**MVP Fields:**
- `project`: `name`, `slug`, `namespace`
- `modules`: List of explicitly required modules (e.g., `Catalog`).
- `capabilities`: List of explicitly requested capabilities.
**Future Fields:**
- `applications`: `api`, `admin_dashboard`.
- `infrastructure`: `database`, `cache_driver`.

## 6. Module Selection Model
Projects declare required modules and capabilities. The Factory aggregates these requests into a set of "seed" dependencies.

## 7. Capability Resolution Model
The Factory feeds the "seed" dependencies into `ManifestDependencyResolver` alongside all available manifests in the Base catalog.
- If a project requests `Catalog`, the resolver automatically detects that `Catalog` requires `media.attachments`, pulling in `Platform.Media`.
- `Platform.Media` requires `files.storage`, pulling in `Platform.Files`.
No secondary engine is built; we rely on `Base\Foundation\DependencyResolver`.

## 8. GenerationPlan Model
An abstract representation of the intended execution, generated prior to disk mutation.
- `resolved_graph`: The ordered execution array of nodes.
- `filesystem_operations`: List of copies, template renderings, and atomic moves.
- `environment_operations`: Necessary keys to append to `.env`.
Supports `dry_run()`, `validate()`, and `execute()`.

## 9. Determinism Guarantees
- Given the identical Base tag/commit and identical `ProjectDefinition`, the generated `GenerationPlan` will be mathematically identical.
- Checksums or hash tracking could be used to verify consistency.

## 10. Idempotency Policy
- Re-running the Factory over an existing path will default to **Fail/Abort** to prevent destructive overrides.
- Explicit `--force` or `--merge` strategy (future) will be required to overwrite existing files. Temporary workspaces (staging) will be used to build the tree, swapping upon complete success.

## 11. Template Architecture
Templates remain simple filesystem skeletons (`.template` files) requiring basic string substitution (e.g., `{{NAMESPACE}}`). The Factory will avoid bloated template engines (no Twig/Blade) unless complex declarative loops become strictly necessary.

## 12. Application / Dashboard Model
Handled via `ApplicationSurfaceDefinition`. Defines entry points (`api.php`, `admin.php`). Roles are not hardcoded into Foundation; Foundation provides generic Identity boundaries, and the generated Application layer specifies the Auth guards and routing.

## 13. Configuration / Environment Boundary
Factory writes structural defaults (`.env.example`, `config/`) but does not inject live runtime secrets. Secrets are strictly managed by CI/CD or host provisioning tools.

## 14. Database Boundary
Factory provisions migration files and declarative schemas. It does *not* issue `CREATE DATABASE`, migrate data, or seed test data (that belongs to the deployment/host orchestrator).

## 15. Git Boundary
Factory MVP will not touch Git. A future Action/Adapter `GitInitializer` will handle `git init` and `git remote add`.

## 16. CI/CD Boundary
Factory MVP will ignore CI/CD. Future generation can copy `.github/workflows` from templates based on the definition.

## 17. Deployment Boundary
Strictly outside Factory Core. The Factory generates the code; deployment orchestrators (e.g., Forge, Vapor, K8s) consume the code.

## 18. ProjectRegistry Decision
**Deferred (Post-MVP).** We will focus on generating a single disposable project first. Managing fleets of generated projects adds unnecessary MVP complexity.

## 19. Failure / Rollback Model
**Temporary Staging Workspace:** Projects are materialized in a system temp directory (e.g., `/tmp/project-gen-xyz`). Only after the entire `GenerationPlan` succeeds is the directory atomically moved (`rename()`) to the final target path. This guarantees zero half-generated projects.

## 20. Security & Trust Boundaries
- **Path Traversal:** Strict validation of slugs and namespaces to prevent `../` directory escapes.
- **Template Escape:** Basic token replacement only.
- **Secrets:** Core rule: Never generate or write raw secrets to disk.

## 21. CLI Architecture
The Factory CLI will be built as an **Independent Executable** (e.g., `bin/factory`) decoupled from the Laravel Artisan runtime of the host application, preventing runtime pollution and ensuring it can run offline against the mono-repo.

## 22. Public Contracts / Value Objects Proposed
- `ProjectDefinition`
- `GenerationPlan`
- `FilesystemOperation`
- `FactoryExecutionResult`

## 23. Deptrac Changes Proposed
- Add layer `Tooling` (Factory).
- `Tooling` can depend on `Foundation` and `Symfony/Console` (or similar).
- `Foundation`, `Platform`, `Specialized`, `Product` MUST NOT depend on `Tooling`.

## 24. Testing Strategy
- **Unit:** Test `ProjectDefinition` hydration, validation, and missing capability errors.
- **Integration:** Test `GenerationPlan` builder yields expected dry-run lists. Test `ManifestDependencyResolver` wrapper.
- **Acceptance:** Full headless execution producing a temporary project folder containing `ReferenceCatalog`, `Media`, and `Files` and validating its structural integrity.

## 25. Acceptance-Test Definition
Create a disposable `project.json` requesting `Modules.ReferenceCatalog`. Assert that running the Factory successfully scaffolds a directory with `ReferenceCatalog`, automatically pulls in `Media` and `Files` via transitive resolution, and correctly applies string substitutions to the templates.
