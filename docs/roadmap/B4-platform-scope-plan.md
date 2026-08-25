# B4 Platform Scope, Dependency and Priority Plan

## 1. Define Platform
**Platform** packages provide high-level, business-neutral, cross-cutting technical capabilities that build upon Foundation primitives.

**Inclusion/Exclusion Rules:**
- **Reusable:** Must be universally applicable across different business products (e.g., e-commerce, SaaS, internal tools).
- **Optional:** Should be independently removable without breaking unrelated core architecture, unless explicitly required by a specific product.
- **Business-Neutral:** Must not contain domain-specific vocabulary (e.g., no `Wallet`, `Cart`, `UserProfile`).
- **Dependencies:** May consume `Foundation\*\Public` contracts. Must **never** depend on Product or Specialized packages.
- **Platform Independence:** Platform packages should not depend on other Platform packages by default. Any future Platform -> Platform dependency requires explicit justification.
- **Distinctions:**
  - *Foundation:* Mandatory, low-level primitives (Auth, Config, Dependency Graph).
  - *Platform:* High-level reusable tools (Files, Settings, Notifications).
  - *Specialized:* Niche/complex infrastructure requiring heavy third-party coupling (Search, Webhooks, Payment Gateways).
  - *Product:* Domain business logic.
  - *Host:* Application entry points (Laravel HTTP/Console).

## 2. Review Candidate Platform Packages

| Candidate | Classification | Rationale |
|-----------|----------------|-----------|
| **Settings** | **A. Platform — P0** | Essential for runtime-configurable projects. |
| **Files** | **A. Platform — P0** | Almost all apps need object storage/file uploads. |
| **Notifications** | **A. Platform — P0** | Universal need. Abstracts intent from delivery channels. |
| **FeatureFlags** | **B. Platform — P1** | Highly valuable for progressive delivery, but not strictly blocking for initial platform capability. |
| **Localization** | **F. Deferred** | Laravel natively handles translation catalogs effectively. Speculative abstraction provides little value. |
| **Webhooks** | **D. Specialized** | Inbound/outbound webhook orchestration is complex and product-specific. Belongs in Specialized modules. |
| **Queue/Scheduler** | **E. Host/Infrastructure** | Laravel's queue and scheduler are already robust abstractions. Wrapping them adds no semantic value. |
| **Search** | **D. Specialized** | Indexing strategies (Elasticsearch, Algolia, DB) carry high infrastructure coupling and should be Specialized. |
| **Realtime** | **D. Specialized** | WebSockets/Pub-Sub are heavily tied to specific infrastructure (Reverb, Pusher) and product-specific UI needs. |
| **ImportExport** | **F. Deferred** | Abstracting CSV/Excel processing rarely succeeds without a concrete business use-case driving it. |

## 3. Settings vs Configuration
- **Configuration (Foundation):** Developer/deployment/system configuration (e.g., `config/`, `.env`). Immutable at runtime. Driven by static files and environment variables.
- **Settings (Platform):** Runtime/admin/project-adjustable values. Mutated during application lifecycle. Persisted in the database.
- **Settings Scope (MVP):** B4.1 will implement **global/project-wide** runtime settings only. Explicitly **deferred** are tenant, user, organization, or product/domain scopes. No `tenant_id` or `user_id` tracking is permitted in the generic settings architecture at this time.

## 4. Files / Object Storage
- **Scope:** Generic object storage abstraction, metadata recording (mime type, size), visibility (public/private), and generation of temporary/signed URLs.
- **Non-Goals:** Does NOT implement a "Media Library". It will not know how to attach a file to a `Product` or `User`. It strictly manages the filesystem and metadata.
- **Priority:** P0.

## 5. Notifications
- **Scope:** Defines notification intent (the event), routing/addressing boundaries, and channel contracts (e.g., Email, SMS).
- **Recipients:** Notifications must not assume recipients are Identity Principals. They must be capable of addressing raw targets (e.g., email address, phone number, device token, or external system destination). Identity integration is strictly optional.
- **Non-Goals:** Does NOT embed `OrderShipped` intents, vendor models, or customer Eloquent classes. Purely orchestrates the dispatch of abstract payloads to abstract recipients.
- **Priority:** P0.

## 6. Feature Flags
- **Scope:** Simple boolean flags, context-based evaluation, and extensible persistence.
- **Non-Goals:** Complex A/B testing orchestration or analytics.
- **Priority:** P1.

## 7-12. Deferred Candidates
Localization, Webhooks, Queue/Scheduler, Search, Realtime, and ImportExport have been deferred or reclassified as Specialized/Infrastructure as justified in Section 2.

## 13. Dependency Graph
Platform packages remain strictly isolated from each other. Foundation.Audit and Foundation.Identity are explicitly decoupled as optional integrations, ensuring modules remain operational if Audit/Identity are absent.

**Required Dependency Graph:**
```text
Platform.Settings
    -> Foundation.Configuration (for defaults/overrides)

Platform.Files
    -> Foundation.Configuration (for disk config)

Platform.Notifications
    -> Foundation.Configuration (for channel config)

Platform.FeatureFlags
    -> Foundation.Configuration (for flag defaults)
```

**Optional Integration Graph:**
```text
Platform.Settings
    -.> Foundation.Audit (optional capability: log setting mutations)

Platform.Files
    -.> Foundation.Audit (optional capability: log file uploads/deletions)

Platform.Notifications
    -.> Foundation.Identity (optional capability: resolve Principal email/phone)
    -.> Foundation.Audit (optional capability: log notification dispatch)

Platform.FeatureFlags
    -.> Foundation.Audit (optional capability: log flag overrides)
```

## 14. Capability Contracts
- **Settings**:
  - Package: `Settings` (Namespace: `Base\Platform\Settings`)
  - Capability: `settings.repository` (Required for consumers)
  - Public Contracts: `SettingDefinition`, `SettingsRepository`
- **Files**:
  - Package: `Files` (Namespace: `Base\Platform\Files`)
  - Capability: `files.storage` (Optional)
  - Public Contracts: `FileMetadata`, `StorageAdapter`, `FileManager`
- **Notifications**:
  - Package: `Notifications` (Namespace: `Base\Platform\Notifications`)
  - Capability: `notifications.dispatcher` (Optional)
  - Public Contracts: `NotificationIntent`, `NotificationChannel`, `NotificationDispatcher`
- **FeatureFlags**:
  - Package: `FeatureFlags` (Namespace: `Base\Platform\FeatureFlags`)
  - Capability: `feature-flags.evaluator` (Optional)
  - Public Contracts: `FeatureFlagDefinition`, `FlagEvaluator`

## 15. Persistence Ownership
Assumptions of mandatory table ownership have been removed. The storage model for each package is classified as follows:
- **Settings:** Required package-owned persistence (runtime mutable settings inherently require DB tables, to be defined explicitly in B4.1).
- **Files:** No persistence required for core operation. Must not mandate a `file_records` table merely to abstract object storage (S3/local). Adapter metadata persistence is optional.
- **Notifications:** No persistence required for core operation. Dispatching via SMTP/APNs does not strictly require a mandatory `notification_logs` table.
- **FeatureFlags:** No persistence required for core operation. Must support in-memory/config/provider-backed evaluation. Database-backed overrides are strictly an optional adapter persistence mechanism.

## 16. Extension Model
All B4 packages **strictly reuse the existing `Foundation\ExtensionRegistry` architecture**. No package-specific plugin or discovery systems will be invented. 

Current expectations:
- **Settings:** Extension contract exists (`SettingContributor`), runtime discovery deferred, runtime wiring deferred.
- **Files:** Extension contract exists (`StorageAdapter`), runtime discovery deferred, runtime wiring deferred.
- **Notifications:** Extension contract exists (`NotificationChannel`), runtime discovery deferred, runtime wiring deferred.
- **FeatureFlags:** Extension contract exists (`FeatureFlagContributor`), runtime discovery deferred, runtime wiring deferred.

*Note: Passive extension contracts will not be described or implemented as actively wired runtime integrations until a concrete consumer requires it.*

## 17. MVP Priority (Execution Sequence)
1. **B4.1 — Platform.Settings** (P0: Required for subsequent dynamic control)
2. **B4.2 — Platform.Files** (P0: Broadly required for uploads)
3. **B4.3 — Platform.Notifications** (P0: Broadly required for communication)
4. **B4.4 — Platform.FeatureFlags** (P1: Can follow immediately after P0)

## 18. Non-Goals
This phase explicitly prohibits:
- Domain product logic (Cart, Wallet, Orders).
- Marketplace or vendor/customer workflows.
- Domain-specific notification templates (e.g., "Order Receipt").
- UI implementation.
- Project generator or deployment automation code.

## 19. B4 Exit Criteria
The B4 milestone is complete when:
- All accepted P0 Platform packages (`Settings`, `Files`, `Notifications`) are fully implemented.
- Package isolation is strictly maintained and enforced by Deptrac.
- Public/Internal namespace boundaries are 100% compliant.
- Manifests (`module.json`) correctly track all dependencies and provided capabilities.
- Zero dependencies exist on Product logic.
- All quality gates (tests, static analysis, architecture validation) pass.
- A final B4 architecture review document is generated.
