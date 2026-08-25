<?php

declare(strict_types=1);

namespace Base\Foundation\Health\Public\ValueObjects;

/**
 * The individual outcome of a single health check.
 */
final readonly class HealthCheckResult
{
    public function __construct(
        public string $checkName,
        public HealthStatus $status,
        public string $reason = '',
        public ?HealthMetadata $metadata = null,
    ) {}

    public static function healthy(string $checkName, string $reason = '', ?HealthMetadata $metadata = null): self
    {
        return new self($checkName, HealthStatus::HEALTHY, $reason, $metadata);
    }

    public static function degraded(string $checkName, string $reason, ?HealthMetadata $metadata = null): self
    {
        return new self($checkName, HealthStatus::DEGRADED, $reason, $metadata);
    }

    public static function unhealthy(string $checkName, string $reason, ?HealthMetadata $metadata = null): self
    {
        return new self($checkName, HealthStatus::UNHEALTHY, $reason, $metadata);
    }
}
