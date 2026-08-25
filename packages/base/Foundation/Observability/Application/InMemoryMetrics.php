<?php

declare(strict_types=1);

namespace Base\Foundation\Observability\Application;

use Base\Foundation\Observability\Public\Contracts\Metrics;
use Base\Foundation\Observability\Public\ValueObjects\MetricName;
use Base\Foundation\Observability\Public\ValueObjects\MetricTags;

/**
 * In-memory metrics implementation for B3.6 Foundation verification.
 */
final class InMemoryMetrics implements Metrics
{
    /** @var list<array{type: string, name: string, value: float, tags: ?MetricTags}> */
    private array $metrics = [];

    public function increment(MetricName $name, int $value = 1, ?MetricTags $tags = null): void
    {
        $this->metrics[] = ['type' => 'counter', 'name' => $name->toString(), 'value' => (float) $value, 'tags' => $tags];
    }

    public function gauge(MetricName $name, float $value, ?MetricTags $tags = null): void
    {
        $this->metrics[] = ['type' => 'gauge', 'name' => $name->toString(), 'value' => $value, 'tags' => $tags];
    }

    public function timing(MetricName $name, float $milliseconds, ?MetricTags $tags = null): void
    {
        $this->metrics[] = ['type' => 'timing', 'name' => $name->toString(), 'value' => $milliseconds, 'tags' => $tags];
    }

    /**
     * @return list<array{type: string, name: string, value: float, tags: ?MetricTags}>
     */
    public function getMetrics(): array
    {
        return $this->metrics;
    }

    public function clear(): void
    {
        $this->metrics = [];
    }
}
