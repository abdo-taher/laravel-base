<?php

declare(strict_types=1);

namespace Base\Foundation\Observability\Public\Contracts;

use Base\Foundation\Observability\Public\ValueObjects\MetricName;
use Base\Foundation\Observability\Public\ValueObjects\MetricTags;

/**
 * Telemetry metrics boundary.
 *
 * Observability is best-effort by default. Concrete adapters are responsible
 * for isolating backend failures to ensure telemetry drops do not affect
 * business outcomes.
 */
interface Metrics
{
    /**
     * Increment a counter metric.
     * Negative values are permitted for decrements.
     */
    public function increment(MetricName $name, int $value = 1, ?MetricTags $tags = null): void;

    /**
     * Set a gauge metric to a specific absolute value.
     */
    public function gauge(MetricName $name, float $value, ?MetricTags $tags = null): void;

    /**
     * Record a timing/histogram metric explicitly in milliseconds.
     */
    public function timing(MetricName $name, float $milliseconds, ?MetricTags $tags = null): void;
}
