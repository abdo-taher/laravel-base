<?php

declare(strict_types=1);

namespace Base\Foundation\Observability\Application;

use Base\Foundation\Observability\Public\Contracts\Span;
use Base\Foundation\Observability\Public\Contracts\Tracer;
use Base\Foundation\Observability\Public\ValueObjects\ObservabilityContext;
use Base\Foundation\Observability\Public\ValueObjects\SpanName;

final class InMemoryTracer implements Tracer
{
    /** @var list<array{name: string, context: ?ObservabilityContext, span: InMemorySpan}> */
    private array $spans = [];

    public function start(SpanName $name, ?ObservabilityContext $context = null): Span
    {
        $span = new InMemorySpan;
        $this->spans[] = ['name' => $name->toString(), 'context' => $context, 'span' => $span];

        return $span;
    }

    /**
     * @return list<array{name: string, context: ?ObservabilityContext, span: InMemorySpan}>
     */
    public function getSpans(): array
    {
        return $this->spans;
    }

    public function clear(): void
    {
        $this->spans = [];
    }
}
