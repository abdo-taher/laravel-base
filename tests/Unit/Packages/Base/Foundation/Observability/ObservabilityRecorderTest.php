<?php

declare(strict_types=1);

namespace Tests\Unit\Packages\Base\Foundation\Observability;

use Base\Foundation\Observability\Application\InMemoryLogger;
use Base\Foundation\Observability\Application\InMemoryMetrics;
use Base\Foundation\Observability\Application\InMemoryTracer;
use Base\Foundation\Observability\Public\ValueObjects\MetricName;
use Base\Foundation\Observability\Public\ValueObjects\SpanName;
use PHPUnit\Framework\TestCase;

final class ObservabilityRecorderTest extends TestCase
{
    public function test_in_memory_logger_records_events(): void
    {
        $logger = new InMemoryLogger;
        $logger->info('test info');
        $logger->error('test error');

        $logs = $logger->getLogs();
        self::assertCount(2, $logs);
        self::assertSame('info', $logs[0]['level']);
        self::assertSame('test info', $logs[0]['message']);
        self::assertSame('error', $logs[1]['level']);
    }

    public function test_in_memory_metrics_records_events(): void
    {
        $metrics = new InMemoryMetrics;
        $metrics->increment(new MetricName('test.count'));
        $metrics->gauge(new MetricName('test.gauge'), 42.5);
        $metrics->timing(new MetricName('test.time'), 100);

        $m = $metrics->getMetrics();
        self::assertCount(3, $m);
        self::assertSame('counter', $m[0]['type']);
        self::assertSame('gauge', $m[1]['type']);
        self::assertSame('timing', $m[2]['type']);
    }

    public function test_in_memory_tracer_records_spans(): void
    {
        $tracer = new InMemoryTracer;
        $span = $tracer->start(new SpanName('test.span'));

        $span->recordException(new \RuntimeException('fail'));
        $span->end();

        $spans = $tracer->getSpans();
        self::assertCount(1, $spans);
        self::assertTrue($spans[0]['span']->isEnded);
        self::assertCount(1, $spans[0]['span']->exceptions);
    }

    public function test_span_lifecycle_is_deterministic(): void
    {
        $tracer = new InMemoryTracer;
        $span = $tracer->start(new SpanName('test.lifecycle'));

        // Multiple ends are safe
        $span->end();
        $span->end();

        // recordException after end is ignored
        $span->recordException(new \RuntimeException('late fail'));

        $spans = $tracer->getSpans();
        self::assertTrue($spans[0]['span']->isEnded);
        self::assertCount(0, $spans[0]['span']->exceptions);
    }
}
