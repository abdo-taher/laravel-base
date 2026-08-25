<?php

declare(strict_types=1);

namespace Base\Foundation\Observability\Public\Contracts;

use Throwable;

/**
 * Represents a single logical operation boundary (span) in a trace.
 *
 * Observability is best-effort by default. Concrete adapters are responsible
 * for isolating backend failures.
 */
interface Span
{
    /**
     * Mark the span as successfully completed.
     * Calling end() multiple times is safe and results in deterministic
     * behavior (e.g. subsequent calls are no-ops).
     */
    public function end(): void;

    /**
     * Record an exception against this span.
     * Calling this after end() is safe (e.g. ignored).
     * Does not expose vendor SDK types.
     */
    public function recordException(Throwable $exception): void;
}
