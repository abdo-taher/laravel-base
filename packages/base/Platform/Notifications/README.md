# Notifications (Platform)

Provides a business-neutral, framework-independent, and independently usable boundary for outbound communications (e.g., email, SMS, push).

## Responsibility
The Notifications package acts as an orchestration boundary between application intents and infrastructure delivery mechanisms.

It explicitly **does not** own:
- Product domain concepts (e.g., invoices, marketing campaigns).
- Templating engines or rendering logic.
- Queues, async processing, or retry loops.
- Database persistence/receipts.
- Identity resolution (Principals to target addresses).

## Public API
The primary interface is `NotificationDispatcher`:
```php
public function dispatch(NotificationMessage $message, ChannelAddress $target): void;
```
The dispatch is strictly synchronous and returns `void` on successful infrastructure acceptance.

## Message Model
`NotificationMessage` is a minimal, typed intent model containing only:
- `$body` (non-empty string)
- `$subject` (optional non-empty string)
It prohibits arbitrary arrays or payload bags to strictly guard the boundary against leaking domain objects, resources, or credentials.

## Target Model
`ChannelAddress` couples a `ChannelName` to an opaque target string. The core enforces generic string rules, but the selected `NotificationChannel` adapter owns channel-specific format validation.

## Failure Semantics
The core is strictly **fail-closed**:
- Unrecognized channels throw `UnknownNotificationChannel`.
- Format rejections or provider failures throw `NotificationDispatchFailed` or `InvalidChannelAddress`.
*Security:* Exception messages deliberately scrub complete raw addresses or provider API keys to prevent PII/credential leakage.

## Dependency Graph
Zero mandatory Base package dependencies. Identity and Audit integrations are inherently deferred/optional.
