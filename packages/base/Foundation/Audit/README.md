# Audit Foundation

## Ownership

- **Owner:** Base Platform
- **Classification:** base-owned
- **Category:** Foundation
- **Security classification:** critical

## Purpose

Provides immutable audit event recording.

Audit owns the capability to record **"who did what to what, when, and with what context"**.

Audit does **not** own:

| Concern | Belongs to |
|---|---|
| Business action names (e.g. `module.action`) | Business modules |
| Authentication / Users | Identity |
| Authorization checks | AccessControl |
| Infrastructure sinks (e.g. Postgres, Dynamo) | Future phases / Infrastructure |
| General application logging | Observability |

## Audit Model

```
Principal + Action + SubjectRef + Metadata + Timestamp → AuditEvent → AuditRecorder
```

## Public Contracts

| Contract | Kind | Purpose |
|---|---|---|
| `AuditRecorder` | interface | Primary recording boundary |
| `Clock` | interface | Audit-local clock for consistent timestamps |
| `AuditEvent` | readonly class | The fully constructed immutable record |
| `Action` | readonly value object | Identifies what happened |
| `SubjectRef` | readonly value object | Resource identifier |
| `Metadata` | readonly value object | Arbitrary scalar/array context |
| `AuditRecordingFailed` | exception | Thrown when a sink fails (fail-closed) |

All Public contracts are strictly decoupled from Laravel and Eloquent.

## Usage

### Recording an Event

```php
public function __construct(
    private readonly AuditRecorder $audit,
    private readonly Clock $clock,
) {}

public function doSomething(Principal $principal): void
{
    // ... business logic ...

    $this->audit->record(new AuditEvent(
        eventId: Str::uuid()->toString(),
        action: new Action('module.something_done'),
        timestamp: $this->clock->now(),
        principal: $principal,
        subject: new SubjectRef('document', 'doc-123'),
        metadata: new Metadata(['status' => 'success']),
    ));
}
```

## Security Rules

- **Strict Metadata:** The `Metadata` value object rejects arbitrary PHP objects. It deeply traverses nested arrays to ensure only scalars and nulls are recorded. This prevents accidentally serializing full Eloquent models or services which might contain secrets.
- **Fail Closed:** If the `AuditRecorder` underlying sink fails, it throws `AuditRecordingFailed`. Calling business logic should not swallow this exception for security-critical operations.
- **No Identity Mutation:** Audit takes a readonly `Principal` and does not interact with underlying user tables.

## Extension Model

Any module can emit audit events by resolving `AuditRecorder` and passing an `AuditEvent`. There is no central registry for actions; modules own their action naming conventions.

## Persistence

B3.4/B3.5 provides `InMemoryAuditRecorder` strictly for Foundation-level architectural verification and testing. Database-backed or external log-backed recorders belong in the Infrastructure layer and are deferred.

## Dependencies

- `Base\Foundation\Identity\Public\ValueObjects\Principal` (parameter type)

Audit must **not** depend on AccessControl to avoid circular dependencies (Identity ← AccessControl and Identity ← Audit).

## Testing

```bash
composer test -- --filter=Audit
```
