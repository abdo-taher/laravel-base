<?php

declare(strict_types=1);

namespace Base\Foundation\Audit\Public\Exceptions;

use RuntimeException;
use Throwable;

/**
 * Thrown when an audit event fails to be recorded by the active sink.
 *
 * Recorder failures are explicit. This exception ensures that failures
 * are never silently swallowed.
 *
 * The caller or application policy is responsible for deciding how to
 * handle this failure (e.g. halting the transaction for fail-closed security,
 * retrying, or logging and continuing).
 *
 * Does not expose internal storage implementation details in the message.
 */
final class AuditRecordingFailed extends RuntimeException
{
    public static function forEvent(string $eventId, ?Throwable $previous = null): self
    {
        return new self(
            sprintf('Failed to record audit event: %s. The audit sink is unavailable or rejected the payload.', $eventId),
            0,
            $previous
        );
    }
}
