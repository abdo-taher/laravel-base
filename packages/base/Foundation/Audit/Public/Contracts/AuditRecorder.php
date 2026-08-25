<?php

declare(strict_types=1);

namespace Base\Foundation\Audit\Public\Contracts;

use Base\Foundation\Audit\Public\Exceptions\AuditRecordingFailed;
use Base\Foundation\Audit\Public\ValueObjects\AuditEvent;

/**
 * Primary boundary for writing audit records.
 *
 * Business modules inject this contract to emit audit events.
 * The underlying implementation dictates where events are stored
 * (e.g. database, in-memory, external log sink).
 *
 * Recorder failures are explicit and never silently swallowed.
 * If the recording fails, an AuditRecordingFailed exception is thrown.
 *
 * The caller or application policy decides whether the audit recording
 * is mandatory, retryable, asynchronous, or best-effort. Security-critical
 * operations may choose fail-closed behavior by allowing this exception
 * to halt the transaction.
 *
 * No framework dependencies.
 */
interface AuditRecorder
{
    /**
     * @throws AuditRecordingFailed
     */
    public function record(AuditEvent $event): void;
}
