# FeatureFlags (Platform)

Provides a business-neutral, framework-independent boundary for semantic boolean feature toggle decisions (controlled enablement of technical/product code paths).

## Responsibility
The FeatureFlags package acts as the authoritative decision engine for boolean toggles. It guarantees fail-closed, deterministic evaluation globally without exposing persistence or runtime mutation mechanisms. 

It explicitly **does not** own:
- Product domain concepts (e.g., experiments, A/B testing).
- Targeting rules (tenants, users, geographic).
- Persistence layers (Database/Redis).
- Configuration values (Settings).

## Important Distinction: Settings vs FeatureFlags
- **Settings**: Runtime-adjustable typed configurations (e.g., `rate_limit_per_minute = 60`).
- **FeatureFlags**: Semantic global toggles (e.g., `feature.new-checkout = true`). Feature flags do not depend on Settings in this core.

## Important Distinction: AccessControl vs FeatureFlags
Feature flags dictate application flow ("Is this feature active?") but possess zero authorization domain ("Can User X perform Action Y?"). A feature flag being active must **never** circumvent proper `AccessControl` permission checks.

## Core API
The primary interface is `FeatureFlagEvaluator`:
```php
public function isEnabled(FeatureFlagKey $flag): bool;
```

## Definition Ownership
Code consumers own their feature flags through immutable `FeatureFlagDefinition` objects. They must be registered via `FeatureFlagRegistry` during application boot.

## Unknown Flag Semantics
Unregistered flags are strictly treated as fatal configuration errors. Evaluating an unknown flag unconditionally throws an `UnknownFeatureFlag` exception rather than silently defaulting to `false`. This protects developers from typos deploying silently disabled code paths.

## Override Precedence
Evaluation strictly respects the following order:
1. Unknown Key -> Exception
2. Valid Override present -> Override value
3. Valid Override absent -> Definition default value

## Immutable Override Provider
The `FeatureFlagOverrideProvider` is fundamentally read-only (`overrideFor`). It does not expose `enable()`, `disable()`, or `resetOverride()`. If temporary overrides are needed (for testing or bootstrapping), an immutable `InMemoryFeatureFlagOverrideProvider` is provided via constructor composition. 

## Zero Dependencies
Platform.FeatureFlags has exactly **0** Base dependencies. It requires no Settings, Identity, Audit, or database connections to operate.
