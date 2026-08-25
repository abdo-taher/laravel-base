<?php

declare(strict_types=1);

namespace Base\Foundation\Health\Public\Contracts;

use Base\Foundation\Health\Public\ValueObjects\HealthCheckResult;

/**
 * A discrete check evaluating a specific component's health.
 *
 * Check implementations MUST NOT leak secrets or credentials
 * in their output or metadata. Timeouts should be handled
 * internally by the check itself.
 */
interface HealthCheck
{
    /**
     * A unique, human-readable identifier for this check.
     */
    public function name(): string;

    /**
     * Does this check affect application liveness?
     * (If it fails, the process is effectively dead and should be killed/restarted).
     */
    public function isLiveness(): bool;

    /**
     * Does this check affect application readiness?
     * (If it fails, the application cannot safely serve traffic).
     */
    public function isReadiness(): bool;

    /**
     * Evaluate the check and return its result.
     * Thrown exceptions should be caught by the Reporter and converted
     * to UNHEALTHY results to prevent health endpoints from crashing.
     */
    public function check(): HealthCheckResult;
}
