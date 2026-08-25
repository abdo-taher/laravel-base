# Product Module Contract

## Canonical Structure
- **Physical Location**: `modules/<ModuleName>/`
- **Namespace Root**: `Modules\<ModuleName>\`
- **Autoloading**: Root `composer.json` maps `"Modules\\": "modules/"`

## Ownership & Manifest
- **Manifest File**: `module.json`
- **Category**: `Product`
- **Ownership**: `project-owned`
- **Lifecycle**: Base upgrades do not overwrite module internals.

## Mandatory vs Optional Directories
- **Mandatory**: `module.json`, `README.md`, `<ModuleName>ServiceProvider.php` (if bindings/boot logic required)
- **Optional**: `Public/`, `Domain/`, `Application/`, `Infrastructure/`, `Presentation/`, `Database/`, `Config/`, `Resources/`, `Tests/`

## Public Boundary Enforcement
- Only `Modules\<ModuleName>\Public\*` may be imported by other modules.
- Internal namespaces (e.g., Domain, Application, Database) are strictly private.
- Eloquent models, controllers, and migrations must NEVER be exposed publicly.

## Dependency Model
- Declared in `module.json`.
- **Allowed Targets**: Foundation Public, Platform Public, Specialized Public, other Product Public.
- **Forbidden Targets**: Base cannot depend on Product. Product internals cannot be imported.
- **Cycle Prevention**: No circular Product dependencies.
- **Required**: Boot/composition fails if absent.
- **Optional**: Consuming module must degrade gracefully.

## Persistence Ownership
- The module uniquely owns its tables and migrations.
- No cross-module Eloquent model imports.
- No cross-module DB joins.
- Use simple scalar IDs (UUID/ULID) as foreign references.

## Route & Test Ownership
- **Routes**: Owned entirely by the module under `Presentation/Routes/`. Composed via the module's `ServiceProvider`.
- **Tests**: Owned by the module under `Tests/` (or a mirrored path if the project test framework requires it, though `modules/<Module>/Tests/` is preferred for isolation).

## ServiceProvider Convention
- **Register**: Binds module-owned Public contracts to internal Application/Infrastructure adapters.
- **Boot**: Loads routes, migrations, resources.
- Must NOT execute business logic, mutate unrelated modules, or scan foreign modules.

## Events
- **Internal Domain Events**: Module-private.
- **Integration Events**: Public compatibility contracts exposed for cross-module consumption.

## Removability Semantics
- Removing a Product module immediately unregisters its routes, capabilities, and migrations.
- Base Foundation/Platform/Specialized layers are unaffected.
- Dependent modules resolve their gracefully degrading (optional) or failing (required) states.

## ModuleManager Discovery
- `ModuleManager` scans `modules/` natively through the unified boot sequence if configured, safely treating `templates/` as invisible due to `.template` extensions.
