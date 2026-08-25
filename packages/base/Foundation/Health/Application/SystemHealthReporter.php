<?php

declare(strict_types=1);

namespace Base\Foundation\Health\Application;

use Base\Foundation\Health\Public\Contracts\HealthCheck;
use Base\Foundation\Health\Public\Contracts\HealthRegistry;
use Base\Foundation\Health\Public\Contracts\HealthReporter;
use Base\Foundation\Health\Public\ValueObjects\HealthCheckResult;
use Base\Foundation\Health\Public\ValueObjects\HealthReport;
use Base\Foundation\Health\Public\ValueObjects\HealthStatus;
use Throwable;

final class SystemHealthReporter implements HealthReporter
{
    public function __construct(private readonly HealthRegistry $registry) {}

    public function getLiveness(): HealthReport
    {
        return $this->evaluate(fn (HealthCheck $c) => $c->isLiveness());
    }

    public function getReadiness(): HealthReport
    {
        return $this->evaluate(fn (HealthCheck $c) => $c->isReadiness());
    }

    public function getDetails(): HealthReport
    {
        return $this->evaluate(fn (HealthCheck $c) => true);
    }

    /**
     * @param  callable(HealthCheck): bool  $filter
     */
    private function evaluate(callable $filter): HealthReport
    {
        $overallStatus = HealthStatus::HEALTHY;
        $results = [];

        foreach ($this->registry->getChecks() as $check) {
            if (! $filter($check)) {
                continue;
            }

            try {
                $result = $check->check();
            } catch (Throwable $e) {
                // Ensure exceptions do not crash the reporter.
                // Hide stack traces to prevent credential exposure.
                $result = HealthCheckResult::unhealthy(
                    $check->name(),
                    'Check threw an exception: '.get_class($e)
                );
            }

            $results[$check->name()] = $result;

            if ($result->status === HealthStatus::UNHEALTHY) {
                $overallStatus = HealthStatus::UNHEALTHY;
            } elseif ($result->status === HealthStatus::DEGRADED && $overallStatus !== HealthStatus::UNHEALTHY) {
                $overallStatus = HealthStatus::DEGRADED;
            }
        }

        return new HealthReport($overallStatus, $results);
    }
}
