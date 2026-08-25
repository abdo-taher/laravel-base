<?php

declare(strict_types=1);

namespace Base\Foundation\Health\Public\Contracts;

use Base\Foundation\Health\Public\ValueObjects\HealthReport;

/**
 * Generates aggregated health reports.
 *
 * It is responsible for safely executing registered checks,
 * catching unhandled exceptions to prevent crashes, and
 * aggregating the results deterministically.
 */
interface HealthReporter
{
    /**
     * Evaluates checks declaring isLiveness() === true.
     * If no checks match, returns a HEALTHY report with empty results.
     */
    public function getLiveness(): HealthReport;

    /**
     * Evaluates checks declaring isReadiness() === true.
     * If no checks match, returns a HEALTHY report with empty results.
     */
    public function getReadiness(): HealthReport;

    /**
     * Evaluates all registered checks (liveness, readiness, and details only).
     * If no checks are registered, returns a HEALTHY report with empty results.
     */
    public function getDetails(): HealthReport;
}
