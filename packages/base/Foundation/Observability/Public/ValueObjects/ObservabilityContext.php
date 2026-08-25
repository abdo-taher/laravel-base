<?php

declare(strict_types=1);

namespace Base\Foundation\Observability\Public\ValueObjects;

/**
 * An immutable container for observability propagation.
 *
 * This context is passed across boundaries to link logs, metrics,
 * and traces to the same logical operation. It avoids mutable
 * context bags by requiring explicit instantiation.
 */
final readonly class ObservabilityContext
{
    public function __construct(
        public CorrelationId $correlationId,
        // TraceId and Baggage can be added here in the future
        // if OpenTelemetry traces need explicit propagation.
    ) {}
}
