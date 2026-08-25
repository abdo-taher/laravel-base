<?php

declare(strict_types=1);

namespace Tests\Unit\Packages\Base\Foundation\Health;

use Base\Foundation\Health\Application\InMemoryHealthRegistry;
use Base\Foundation\Health\Application\SystemHealthReporter;
use Base\Foundation\Health\Public\Contracts\HealthCheck;
use Base\Foundation\Health\Public\ValueObjects\HealthCheckResult;
use Base\Foundation\Health\Public\ValueObjects\HealthStatus;
use PHPUnit\Framework\TestCase;

final class HealthReporterTest extends TestCase
{
    private function createCheck(string $name, bool $liveness, bool $readiness, callable $logic): HealthCheck
    {
        return new class($name, $liveness, $readiness, $logic) implements HealthCheck
        {
            public function __construct(
                private readonly string $name,
                private readonly bool $liveness,
                private readonly bool $readiness,
                private readonly mixed $logic
            ) {}

            public function name(): string
            {
                return $this->name;
            }

            public function isLiveness(): bool
            {
                return $this->liveness;
            }

            public function isReadiness(): bool
            {
                return $this->readiness;
            }

            public function check(): HealthCheckResult
            {
                return ($this->logic)();
            }
        };
    }

    public function test_reporter_evaluates_liveness_only(): void
    {
        $registry = new InMemoryHealthRegistry;
        $registry->register($this->createCheck('live_ok', true, false, fn () => HealthCheckResult::healthy('live_ok')));
        $registry->register($this->createCheck('ready_fail', false, true, fn () => HealthCheckResult::unhealthy('ready_fail', 'fail')));

        $reporter = new SystemHealthReporter($registry);

        $liveness = $reporter->getLiveness();
        self::assertSame(HealthStatus::HEALTHY, $liveness->status);
        self::assertCount(1, $liveness->results);
        self::assertArrayHasKey('live_ok', $liveness->results);
    }

    public function test_reporter_aggregates_unhealthy_state(): void
    {
        $registry = new InMemoryHealthRegistry;
        $registry->register($this->createCheck('ok1', false, true, fn () => HealthCheckResult::healthy('ok1')));
        $registry->register($this->createCheck('fail1', false, true, fn () => HealthCheckResult::unhealthy('fail1', 'bad')));
        $registry->register($this->createCheck('deg1', false, true, fn () => HealthCheckResult::degraded('deg1', 'slow')));

        $reporter = new SystemHealthReporter($registry);

        $readiness = $reporter->getReadiness();
        // UNHEALTHY overrides DEGRADED and HEALTHY
        self::assertSame(HealthStatus::UNHEALTHY, $readiness->status);
        self::assertCount(3, $readiness->results);
    }

    public function test_reporter_aggregates_degraded_state(): void
    {
        $registry = new InMemoryHealthRegistry;
        $registry->register($this->createCheck('ok1', false, true, fn () => HealthCheckResult::healthy('ok1')));
        $registry->register($this->createCheck('deg1', false, true, fn () => HealthCheckResult::degraded('deg1', 'slow')));

        $reporter = new SystemHealthReporter($registry);

        $readiness = $reporter->getReadiness();
        // DEGRADED overrides HEALTHY
        self::assertSame(HealthStatus::DEGRADED, $readiness->status);
    }

    public function test_reporter_catches_exceptions_and_returns_unhealthy(): void
    {
        $registry = new InMemoryHealthRegistry;
        $registry->register($this->createCheck('crash1', true, true, fn () => throw new \RuntimeException('Secret DB Error')));

        $reporter = new SystemHealthReporter($registry);

        $details = $reporter->getDetails();
        self::assertSame(HealthStatus::UNHEALTHY, $details->status);
        self::assertCount(1, $details->results);

        $result = $details->results['crash1'];
        self::assertSame(HealthStatus::UNHEALTHY, $result->status);

        // Assert we don't leak the actual exception message, only the class type to indicate an unexpected error
        self::assertStringContainsString('RuntimeException', $result->reason);
        self::assertStringNotContainsString('Secret DB Error', $result->reason);
    }

    public function test_reporter_details_only_check_does_not_affect_liveness_or_readiness(): void
    {
        $registry = new InMemoryHealthRegistry;
        // A check that is false for liveness and false for readiness
        $registry->register($this->createCheck('details_fail', false, false, fn () => HealthCheckResult::unhealthy('details_fail', 'fail')));

        $reporter = new SystemHealthReporter($registry);

        $liveness = $reporter->getLiveness();
        self::assertSame(HealthStatus::HEALTHY, $liveness->status);
        self::assertCount(0, $liveness->results);

        $readiness = $reporter->getReadiness();
        self::assertSame(HealthStatus::HEALTHY, $readiness->status);
        self::assertCount(0, $readiness->results);

        $details = $reporter->getDetails();
        self::assertSame(HealthStatus::UNHEALTHY, $details->status);
        self::assertCount(1, $details->results);
    }

    public function test_reporter_empty_registry_is_healthy(): void
    {
        $registry = new InMemoryHealthRegistry;
        $reporter = new SystemHealthReporter($registry);

        self::assertSame(HealthStatus::HEALTHY, $reporter->getLiveness()->status);
        self::assertCount(0, $reporter->getLiveness()->results);

        self::assertSame(HealthStatus::HEALTHY, $reporter->getDetails()->status);
        self::assertCount(0, $reporter->getDetails()->results);
    }

    public function test_registry_throws_on_duplicate_name(): void
    {
        $registry = new InMemoryHealthRegistry;
        $registry->register($this->createCheck('dup', true, true, fn () => HealthCheckResult::healthy('dup')));

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('already registered');
        $registry->register($this->createCheck('dup', false, false, fn () => HealthCheckResult::healthy('dup')));
    }
}
