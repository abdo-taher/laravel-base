<?php

declare(strict_types=1);

namespace Base\Foundation\Health\Public\ValueObjects;

/**
 * Represents the normalized status of a health check or aggregated report.
 */
enum HealthStatus: string
{
    case HEALTHY = 'healthy';
    case DEGRADED = 'degraded';
    case UNHEALTHY = 'unhealthy';
}
