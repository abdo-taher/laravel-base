<?php

declare(strict_types=1);

namespace Base\Foundation\Health\Application;

use Base\Foundation\Health\Public\Contracts\HealthCheck;
use Base\Foundation\Health\Public\Contracts\HealthRegistry;

final class InMemoryHealthRegistry implements HealthRegistry
{
    /** @var array<string, HealthCheck> */
    private array $checks = [];

    public function register(HealthCheck $check): void
    {
        if (isset($this->checks[$check->name()])) {
            throw new \InvalidArgumentException("A health check with the name '{$check->name()}' is already registered.");
        }
        $this->checks[$check->name()] = $check;
    }

    /**
     * @return iterable<HealthCheck>
     */
    public function getChecks(): iterable
    {
        return array_values($this->checks);
    }
}
