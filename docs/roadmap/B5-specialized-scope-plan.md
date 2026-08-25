# B5 Specialized Layer Scope & Architecture Plan

## 1. Current Milestone State
- **B3 Foundation**: Completed and frozen (`base-foundation-packages`).
- **B4 Platform**: Completed and frozen (`base-platform-packages`).
- **B5 Specialized**: Planning phase (current).
- **Product**: Future phase.

## 2. Definition of Specialized
The `Specialized` layer contains optional advanced technical capabilities that involve heavier infrastructure or provider complexity. These packages remain entirely business-neutral. They may depend on Foundation or Platform Public contracts when explicitly justified, but they must **never** be required by Foundation or Platform packages. They must **never** contain Product or domain-specific business logic.

## 3. Foundation vs Platform vs Specialized vs Product
- **Foundation**: Mandatory low-level technical primitives and runtime orchestration.
- **Platform**: Broadly reusable application capabilities with light infrastructure complexity.
- **Specialized**: Optional advanced capabilities with provider-specific or infrastructure-heavy behavior.
- **Product**: Business and domain modules (e.g., Cart, Orders, Wallet).

## 4. Candidate Evaluation Matrix

| Candidate | Category | Verdict | Rationale |
|-----------|----------|---------|-----------|
| Outbound Webhooks | Specialized | **B5 P0** | High enterprise ROI. Smallest coherent scope for system-to-system messaging. |
| Inbound Webhooks | Specialized | **Deferred**| Distinct semantics (signature verification/replay). Deferred to keep B5 MVP small. |
| Search | Specialized | **Deferred**| High infrastructure complexity. Does not materially improve project generation before Product modules exist. Deferred to B6+ or later. |
| Realtime | Specialized | **Deferred**| Transport-heavy. Often handled natively by Laravel Reverb. |
| ImportExport | Specialized | **Deferred**| Heavily tied to application workflows/domain mapping. |

## 5. Webhooks Decision
**Decision: Outbound Webhooks Only (B5 P0).**
Inbound and Outbound Webhooks represent fundamentally different architectural concerns.
- *Inbound* handles signature verification, replay protection, and request authenticity.
- *Outbound* handles payload dispatch, endpoint routing, and transport failure signaling.
To preserve the smallest coherent B5 scope, B5 will implement **Outbound Webhooks only** (`Base\Specialized\OutboundWebhooks`). Inbound Webhooks are deferred.

## 6. Search Decision
**Decision: Deferred.**
While Search represents a valid Specialized capability, it does not materially improve Base project generation before concrete Product data exists to be indexed. To maximize ROI and avoid building speculative architectural symmetry, Search is deferred to B6 or later.

## 7. Retry and Async Ownership
**Decision: Sync Dispatch Core.**
The `OutboundWebhooks` core dispatcher is strictly synchronous. It evaluates an endpoint and payload, invokes transport, and returns synchronous acceptance or failure.
- The core **must not** own hidden retry loops.
- Generics queues are **prohibited** as a Base abstraction.
- Reliable delivery (retries, asynchronous dispatch) is handled by the application composition layer invoking the dispatcher via native Laravel Jobs, or internally by provider SDKs.

## 8. Dependency Policy
- **Required**: Zero Base dependencies.
- **Optional Integrations**: None. Configuration values (retry limits, timeouts, secrets) will be supplied to Infrastructure adapters natively via DI during composition, eliminating the need to couple `Specialized` to `Platform.Settings` or `Platform.FeatureFlags`.
- **Prohibited**: No Specialized package may depend on a Product package.

## 9. Persistence Classification
**Decision: No Package-Owned Persistence for Core MVP.**
The core MVP is restricted to synchronous dispatch semantics (`WebhookDispatcher -> endpoint + payload -> transport -> success/failure`).
- Features like persistent endpoint registries, delivery attempt histories, dead-letter queues, and receipts represent *optional infrastructure* or *deferred* features.
- No database tables or migrations will be created for `OutboundWebhooks` in B5.0.

## 10. Provider Strategy
Specialized packages will heavily rely on explicit Dependency Injection (DI) and the existing `Foundation.CapabilityRegistry`. No new plugin architecture will be invented.

## 11. Extension Strategy
Extensions will focus purely on providing custom transport adapters (e.g., Guzzle HTTP, AWS EventBridge) mapping standard payloads to provider SDKs.

## 12. Proposed Capabilities
- `outbound-webhooks.dispatch`

## 13. Proposed Namespaces/Package Locations
- `Base\Specialized\OutboundWebhooks\` -> `packages/base/Specialized/OutboundWebhooks/`

## 14. P0/P1/P2 Classification
- **P0**: `OutboundWebhooks`
- **P1**: None
- **P2**: None
- **Deferred**: `InboundWebhooks`, `Search`, `Realtime`, `ImportExport`

## 15. Dependency Graphs

### Required Dependency Graph
- `Base.Specialized.OutboundWebhooks` -> (none)

### Optional Integration Graph
- `Base.Specialized.OutboundWebhooks` -> (none)

## 16. Recommended B5 Execution Order
1. B5.1 - OutboundWebhooks

## 17. Non-Goals
- DO NOT implement Inbound Webhooks.
- DO NOT implement Search.
- DO NOT implement generic Queue/Async wrappers.
- DO NOT implement core-owned database tables for webhook logs.
- DO NOT couple to Settings or FeatureFlags.
- DO NOT place Product business logic inside Specialized.

## 18. B5 Exit Criteria
- `OutboundWebhooks` is independently removable.
- Public boundary is framework-free.
- Zero Base package dependencies.
- Manifest validates successfully.
- Deptrac enforces layer isolation.
- Full quality gates pass.
- Final B5 Architecture Review completed before freeze tag.

## 19. Recommended Next Phase After B5
**Phase B6 - Product Layer Bootstrapping.**
Following the hardened, minimal B5 delivery of enterprise outbound messaging, the architecture is definitively ready to bootstrap isolated Product domain modules leveraging the B3-B5 infrastructure.
