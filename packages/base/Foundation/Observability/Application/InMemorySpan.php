<?php

declare(strict_types=1);

namespace Base\Foundation\Observability\Application;

use Base\Foundation\Observability\Public\Contracts\Span;
use Throwable;

final class InMemorySpan implements Span
{
    public bool $isEnded = false;

    /** @var list<Throwable> */
    public array $exceptions = [];

    public function end(): void
    {
        $this->isEnded = true;
    }

    public function recordException(Throwable $exception): void
    {
        if ($this->isEnded) {
            return;
        }

        $this->exceptions[] = $exception;
    }
}
