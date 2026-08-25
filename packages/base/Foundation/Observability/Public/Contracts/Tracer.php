<?php

declare(strict_types=1);

namespace Base\Foundation\Observability\Public\Contracts;

use Base\Foundation\Observability\Public\ValueObjects\ObservabilityContext;
use Base\Foundation\Observability\Public\ValueObjects\SpanName;

/**
 * Tracing boundary for starting spans.
 *
 * Observability is best-effort by default. Concrete adapters are responsible
 * for isolating backend failures.
 */
interface Tracer
{
    /**
     * Start a new span for the given name.
     */
    public function start(SpanName $name, ?ObservabilityContext $context = null): Span;
}
