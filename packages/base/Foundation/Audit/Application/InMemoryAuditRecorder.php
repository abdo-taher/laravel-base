<?php

declare(strict_types=1);

namespace Base\Foundation\Audit\Application;

use Base\Foundation\Audit\Public\Contracts\AuditRecorder;
use Base\Foundation\Audit\Public\ValueObjects\AuditEvent;

/**
 * An in-memory implementation of AuditRecorder for Foundation testing
 * and B3.5 verification.
 *
 * Future phases will introduce a DatabaseAuditRecorder or LogAuditRecorder
 * in the Infrastructure layer, but Foundation uses this to prove the
 * recording boundary.
 */
final class InMemoryAuditRecorder implements AuditRecorder
{
    /** @var list<AuditEvent> */
    private array $events = [];

    public function record(AuditEvent $event): void
    {
        // Simple append. No failure conditions in the in-memory sink,
        // so it never throws AuditRecordingFailed.
        $this->events[] = $event;
    }

    /**
     * @return list<AuditEvent>
     */
    public function getRecordedEvents(): array
    {
        return $this->events;
    }

    public function clear(): void
    {
        $this->events = [];
    }
}
