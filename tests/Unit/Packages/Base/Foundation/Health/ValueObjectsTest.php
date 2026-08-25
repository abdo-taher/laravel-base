<?php

declare(strict_types=1);

namespace Tests\Unit\Packages\Base\Foundation\Health;

use Base\Foundation\Health\Public\ValueObjects\HealthCheckResult;
use Base\Foundation\Health\Public\ValueObjects\HealthMetadata;
use Base\Foundation\Health\Public\ValueObjects\HealthReport;
use Base\Foundation\Health\Public\ValueObjects\HealthStatus;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

final class ValueObjectsTest extends TestCase
{
    public function test_health_metadata_accepts_scalars_and_nulls(): void
    {
        $meta = new HealthMetadata([
            'str' => 'value',
            'int' => 42,
            'float' => 3.14,
            'bool' => true,
            'null' => null,
            'nested' => ['a' => 1],
        ]);
        self::assertArrayHasKey('str', $meta->values);
    }

    public function test_health_metadata_rejects_objects(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid HealthMetadata value at path "obj"');
        new HealthMetadata(['obj' => new \stdClass]);
    }

    public function test_health_metadata_rejects_resources(): void
    {
        $resource = fopen('php://memory', 'r');
        self::assertIsResource($resource);

        $this->expectException(InvalidArgumentException::class);

        try {
            new HealthMetadata(['res' => $resource]);
        } finally {
            fclose($resource);
        }
    }

    public function test_health_metadata_rejects_closures(): void
    {
        $this->expectException(InvalidArgumentException::class);
        new HealthMetadata(['func' => function () {}]);
    }

    public function test_health_metadata_rejects_non_string_keys(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('HealthMetadata keys must be non-empty strings');
        new HealthMetadata(['valid' => 1, 0 => 'invalid']); // @phpstan-ignore-line
    }

    public function test_health_metadata_rejects_empty_string_keys(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('HealthMetadata keys must be non-empty strings');
        new HealthMetadata(['   ' => 'invalid']);
    }

    public function test_health_check_result_factories(): void
    {
        $healthy = HealthCheckResult::healthy('test1', 'ok');
        self::assertSame(HealthStatus::HEALTHY, $healthy->status);
        self::assertSame('test1', $healthy->checkName);
        self::assertSame('ok', $healthy->reason);

        $degraded = HealthCheckResult::degraded('test2', 'slow');
        self::assertSame(HealthStatus::DEGRADED, $degraded->status);

        $unhealthy = HealthCheckResult::unhealthy('test3', 'fail');
        self::assertSame(HealthStatus::UNHEALTHY, $unhealthy->status);
    }

    public function test_health_report_stores_properties(): void
    {
        $result = HealthCheckResult::healthy('test');
        $report = new HealthReport(HealthStatus::HEALTHY, ['test' => $result]);

        self::assertSame(HealthStatus::HEALTHY, $report->status);
        self::assertCount(1, $report->results);
        self::assertSame($result, $report->results['test']);
    }
}
