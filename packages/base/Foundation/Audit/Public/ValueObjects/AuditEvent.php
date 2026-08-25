<?php

declare(strict_types=1);

namespace Base\Foundation\Audit\Public\ValueObjects;

use Base\Foundation\Identity\Public\ValueObjects\Principal;
use DateTimeImmutable;
use InvalidArgumentException;

/**
 * An immutable record of an audited action.
 *
 * Captures "who did what to what, when, and with what context."
 *
 * - Actor (Principal) is optional to allow system-generated events.
 * - Subject (SubjectRef) is optional for actions not tied to a specific resource.
 * - Event ID ensures idempotency in external sinks.
 *
 * No framework dependencies.
 */
final readonly class AuditEvent
{
    public function __construct(
        public string $eventId,
        public Action $action,
        public DateTimeImmutable $timestamp,
        public ?Principal $principal = null,
        public ?SubjectRef $subject = null,
        public ?Metadata $metadata = null,
    ) {
        if (trim($eventId) === '') {
            throw new InvalidArgumentException('Event ID must be a non-empty string.');
        }
    }
}
