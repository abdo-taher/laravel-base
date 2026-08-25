<?php

declare(strict_types=1);

namespace Base\Foundation\Health\Public\Contracts;

/**
 * Registry for health checks.
 *
 * Provides a framework-independent registration mechanism
 * compatible with the extension architecture.
 */
interface HealthRegistry
{
    /**
     * Registers a health check.
     *
     * If a check with the same name is already registered, this MUST
     * throw an InvalidArgumentException. Last-one-wins is not permitted.
     */
    public function register(HealthCheck $check): void;

    /**
     * @return iterable<HealthCheck>
     */
    public function getChecks(): iterable;
}
