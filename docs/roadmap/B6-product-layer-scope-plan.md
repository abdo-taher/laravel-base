# B6 Product Layer Scope & Architecture Plan

## 1. Current Frozen State
- **B3 Foundation**: Tagged (`base-foundation-packages`)
- **B4 Platform**: Tagged (`base-platform-packages`)
- **B5 Specialized**: Tagged (`base-specialized-packages`) (includes OutboundWebhooks only)

## 2. Product Layer Definition
The Product Layer contains all business and domain-specific capabilities. Unlike Foundation, Platform, and Specialized modules which are reusable technical building blocks, Product modules solve concrete business problems (e.g., E-Commerce, Content Management, Wallets).

## 3. Whether Base Should Ship Product Modules
**Base should NOT ship concrete Product modules as mandatory core dependencies.** The primary goal of Base is to be a reusable enterprise technical foundation. Concrete business modules should exist as project-specific implementations, optional templates, or generated components rather than living permanently within the reusable base repository.

## 4. Physical Ownership Decision
Product modules belong in the `modules/` directory at the project root, NOT in `packages/base/Product/`. This physically separates project-owned business logic from base-owned technical logic.

## 5. Namespace Decision
Product modules use the canonical namespace `Modules\<ModuleName>\` (e.g., `Modules\Catalog\`). They do not use the `Base\` namespace.

## 6. Product Dependency Policy
- **Can depend on**: Foundation (Public), Platform (Public), Specialized (Public), and other Product modules (Public only).
- **Cannot depend on**: Internals of any module.
- **Reverse Dependency**: Base packages (Foundation/Platform/Specialized) must NEVER depend on any Product module.
- Circular dependencies between Product modules are strictly forbidden.
- **Required vs Optional Dependencies**:
  - If a declared required Product dependency is absent, composition/boot must explicitly fail.
  - If an optional integration is absent, the consuming module must remain valid and handle the absence gracefully.

## 7. Persistence Ownership
- Every Product table has exactly one Product owner.
- No foreign Product Eloquent model imports are permitted.
- No cross-module database joins are permitted as an integration contract.
- A module may store foreign identifiers as scalar references (e.g., UUIDs/ULIDs) without importing the foreign persistence model.
- No migrations may alter another Product module's tables.

## 8. Product Public API Rules
The `Public/` boundary of a Product module is tightly controlled and may expose only intentionally stable cross-module contracts:
- Public query contracts
- Public command/use-case contracts (for synchronous invocation)
- Immutable DTOs / Result objects
- Capability contracts
- Integration Events

Internal Domain entities, aggregates, repositories, application handlers, Eloquent models, migrations, and infrastructure adapters must remain strictly private. It should not be assumed that every Application command or query class is Public.

## 9. Domain Events vs Integration Events
- **Internal Domain Events**: Remain private to the module and are used for intra-module consistency.
- **Integration Events**: An optional Public compatibility contract intended for cross-module asynchronous communication.
Not all Domain Events are exposed automatically. B6 will not introduce a new EventBus abstraction; Laravel's native events are sufficient infrastructure for now.

## 10. Identity Integration
Product modules integrate with Identity via pure IDs (e.g., UUIDs) or Public Principal contracts. Product modules must not import or rely on Eloquent relations to `App\Models\User` or internal Identity infrastructure.

## 11. AccessControl Integration
Product modules own their specific business permission vocabulary. Permission registration is performed explicitly/manually through the Foundation's existing `PermissionContributor` boundary.
- No Product permissions belong hard-coded inside `Foundation.AccessControl`.
- Automatic ExtensionRegistry/CapabilityRegistry discovery for permissions remains deferred unless a concrete runtime consumer requires it.

## 12. Platform / Specialized Consumption
Product modules may consume Settings, Files, Notifications, and OutboundWebhooks explicitly as needed. The Platform/Specialized layers remain completely ignorant of the Product's business context.

## 13. Removability Semantics
Product modules must be independently removable.
- **Case A**: No other module depends on Product X. Removal leaves the host/Base healthy.
- **Case B**: Product Y has an optional integration with Product X. Product Y continues to function without that optional integration.
- **Case C**: Product Y declares a required dependency on Product X. Composition/Boot explicitly fails if X is removed.
Removal of a Product module must never break Foundation, Platform, or Specialized packages.

## 14. Composer / Autoload Direction
B6.1 will explicitly resolve how the `Modules\` namespace maps to the `modules/` directory in the root `composer.json` (e.g., `"Modules\\": "modules/"`). All generated Product modules will share this root autoloading mapping.

## 15. ModuleManager Alignment
Existing ModuleManager search-path-based discovery will be evaluated to find project modules in `modules/`. If a gap exists, it will be identified before modifying the runtime.

## 16. Module Manifest Ownership
Product module manifests (`module.json`) must declare:
- `category: Product`
- `ownership: project-owned`
Generated Product modules remain project-owned after generation. They are never upgraded as Base package internals and may evolve independently.

## 17. Extension Policy
Product modules only define extension points if they anticipate dynamic project-level customization. They do not automatically define them by default.

## 18. Accepted B6 Scope & Execution Order
- **B6.1 — Product Module Contract + Template**: Define canonical `modules/<Module>/` structure, namespace, manifest template, mandatory vs optional directories, Public/Internal boundaries, and ownership conventions (ServiceProvider, routes, migrations, tests, autoloading). Does not include building CLI tools.
- **B6.2 — Minimal Reference Product Module + Loading Proof**: Implement the smallest neutral business concept possible strictly to prove architectural loading (discovery, namespace, manifests, isolation). Will not implement complex realism (Cart/Wallet) nor unnecessary persistence.
- **B6.3 — B6 Final Architecture Review & Freeze**

## 19. Non-Goals
B6 explicitly does NOT implement E-commerce, Wallets, Orders, payments, or complex admin dashboards. It also explicitly defers executable generation tooling (Artisan CLI/generators) to a future phase.

## 20. Recommended Next Phase
After B6 successfully proves the Product module contract, the roadmap should transition to **B7 — Project Composition / Factory Tooling**. B7 will focus on module generation, module selection, and full project composition automation.
