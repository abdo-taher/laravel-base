# Observability Foundation

## Ownership

- **Owner:** Base Platform
- **Classification:** base-owned
- **Category:** Foundation
- **Security classification:** internal

## Purpose

Provides a framework-independent boundary for operational telemetry (logging, metrics, and tracing).

Observability owns the contracts for emitting operational signals but does **not** own the semantics or nomenclatures of business events. It explicitly does **not** depend on or interact with the `Audit` foundation.

## Public Contracts

| Contract | Kind | Purpose |
|---|---|---|
| `Logger` | interface | Structured operational logging |
| `Metrics` | interface | Emitting counters, gauges, and timings |
| `Tracer` | interface | Starting and ending spans |
| `Span` | interface | Marking boundaries and recording exceptions |
| `LogContext` | readonly class | Safe context container for logging |
| `ObservabilityContext` | readonly class | Container for trace and correlation IDs |
| `CorrelationId` | readonly class | Non-empty correlation identifier |
| `MetricName` | readonly class | Well-formed metric nomenclature |
| `SpanName` | readonly class | Well-formed span nomenclature |

## Security Rules

- **Strict LogContext:** Rejects arbitrary PHP objects, closures, and resources. Permits only scalar values, nulls, and nested arrays with strictly string keys.
- **Fail-Open Policy:** Observability adapters MUST swallow underlying sink exceptions. Telemetry failures MUST NEVER crash or alter business workflows. This is fundamentally opposite to Audit's fail-closed policy.
- **Secret Safety:** While `LogContext` rejects models to prevent hidden property leaks, it does not currently provide an automated redaction engine for scalars. Callers MUST NOT include passwords, tokens, API keys, or authorization headers in contexts.

## Dependencies

- None. Observability has zero internal foundation dependencies.

## Testing

```bash
composer test -- --filter=Observability
```
