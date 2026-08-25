# Health Foundation

## Ownership

- **Owner:** Base Platform
- **Classification:** base-owned
- **Category:** Foundation
- **Security classification:** internal

## Purpose

Provides a framework-independent boundary for determining application health (Liveness, Readiness, and Detailed Diagnostics).

Health strictly focuses on check aggregation and status. It explicitly does **not** depend on Observability or Laravel controllers/routes.

## Public Contracts

| Contract | Kind | Purpose |
|---|---|---|
| `HealthCheck` | interface | Evaluates a specific component's state |
| `HealthRegistry` | interface | Extension point for registering checks |
| `HealthReporter` | interface | Aggregates checks into a final status |
| `HealthStatus` | enum | `HEALTHY`, `DEGRADED`, or `UNHEALTHY` |
| `HealthReport` | readonly class | The overall aggregation payload |
| `HealthCheckResult`| readonly class | A single check's payload |
| `HealthMetadata` | readonly class | Strict structured metadata container |

## Semantics

- **Liveness (`isLiveness()`):** Determines if the process is capable of recovering or needs to be killed. Only absolutely critical internal invariants should declare this.
- **Readiness (`isReadiness()`):** Determines if the node can serve traffic. Essential dependencies (e.g. primary database) will cause this to fail.
- **Details:** Includes all checks, providing diagnostics without failing traffic routing.

## Security Rules

- **Exception Shielding:** The reporter catches all check exceptions to prevent the endpoint from crashing, returning an `UNHEALTHY` result with the exception's class name. The actual stack trace is purposefully swallowed to avoid leaking connection strings.
- **Strict Metadata:** Rejects arbitrary PHP objects, closures, and resources.
- **Secret Safety:** While `HealthMetadata` rejects models to prevent hidden property leaks, callers MUST NOT include passwords, tokens, or API keys in the metadata.

## Dependencies

- None. Health has zero internal foundation dependencies.

## Testing

```bash
composer test -- --filter=Health
```
