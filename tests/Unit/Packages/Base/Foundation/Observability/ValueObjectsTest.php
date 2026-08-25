<?php

declare(strict_types=1);

namespace Tests\Unit\Packages\Base\Foundation\Observability;

use Base\Foundation\Observability\Public\ValueObjects\CorrelationId;
use Base\Foundation\Observability\Public\ValueObjects\LogContext;
use Base\Foundation\Observability\Public\ValueObjects\MetricName;
use Base\Foundation\Observability\Public\ValueObjects\MetricTags;
use Base\Foundation\Observability\Public\ValueObjects\ObservabilityContext;
use Base\Foundation\Observability\Public\ValueObjects\SpanName;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

final class ValueObjectsTest extends TestCase
{
    public function test_metric_tags_accepts_scalars(): void
    {
        $tags = new MetricTags([
            'str' => 'val',
            'int' => 1,
            'float' => 1.5,
            'bool' => true,
        ]);
        self::assertArrayHasKey('str', $tags->values);
    }

    public function test_metric_tags_rejects_empty_keys(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Metric tag keys must be non-empty strings.');
        new MetricTags(['   ' => 'val']);
    }

    public function test_metric_tags_rejects_non_string_keys(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Metric tag keys must be non-empty strings.');
        new MetricTags([0 => 'val']); // @phpstan-ignore-line
    }

    public function test_metric_tags_rejects_objects(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Metric tag values must be scalar');
        new MetricTags(['obj' => new \stdClass]); // @phpstan-ignore-line
    }

    public function test_metric_tags_rejects_null(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Metric tag values must be scalar');
        new MetricTags(['null' => null]); // @phpstan-ignore-line
    }

    public function test_correlation_id_accepts_valid_string(): void
    {
        $id = new CorrelationId('abc-123');
        self::assertSame('abc-123', $id->value);
        self::assertSame('abc-123', $id->toString());
    }

    public function test_correlation_id_rejects_empty_string(): void
    {
        $this->expectException(InvalidArgumentException::class);
        new CorrelationId('   ');
    }

    public function test_metric_name_rejects_empty_string(): void
    {
        $this->expectException(InvalidArgumentException::class);
        new MetricName('');
    }

    public function test_span_name_rejects_empty_string(): void
    {
        $this->expectException(InvalidArgumentException::class);
        new SpanName('');
    }

    public function test_log_context_accepts_scalars_and_nulls(): void
    {
        $ctx = new LogContext([
            'str' => 'value',
            'int' => 42,
            'float' => 3.14,
            'bool' => true,
            'null' => null,
            'nested' => ['a' => 1],
        ]);
        self::assertArrayHasKey('str', $ctx->values);
    }

    public function test_log_context_rejects_objects(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid LogContext value at path "obj"');
        new LogContext(['obj' => new \stdClass]);
    }

    public function test_log_context_rejects_resources(): void
    {
        $resource = fopen('php://memory', 'r');
        self::assertIsResource($resource);

        $this->expectException(InvalidArgumentException::class);

        try {
            new LogContext(['res' => $resource]);
        } finally {
            fclose($resource);
        }
    }

    public function test_log_context_rejects_closures(): void
    {
        $this->expectException(InvalidArgumentException::class);
        new LogContext(['func' => function () {}]);
    }

    public function test_log_context_rejects_non_string_keys(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('LogContext keys must be strings');
        new LogContext(['valid' => 1, 0 => 'invalid']); // @phpstan-ignore-line
    }

    public function test_observability_context_stores_properties(): void
    {
        $corr = new CorrelationId('abc');
        $ctx = new ObservabilityContext($corr);
        self::assertSame($corr, $ctx->correlationId);
    }
}
