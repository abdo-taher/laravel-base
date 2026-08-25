<?php

declare(strict_types=1);

namespace Base\Foundation\Health\Public\ValueObjects;

/**
 * The aggregated report of multiple health checks.
 */
final readonly class HealthReport
{
    /**
     * @param  array<string, HealthCheckResult>  $results  Keyed by check name
     */
    public function __construct(
        public HealthStatus $status,
        public array $results = [],
    ) {}
}
