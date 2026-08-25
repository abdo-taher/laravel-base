<?php

declare(strict_types=1);

namespace Base\Foundation\Observability;

use Base\Foundation\Observability\Application\InMemoryLogger;
use Base\Foundation\Observability\Application\InMemoryMetrics;
use Base\Foundation\Observability\Application\InMemoryTracer;
use Base\Foundation\Observability\Public\Contracts\Logger;
use Base\Foundation\Observability\Public\Contracts\Metrics;
use Base\Foundation\Observability\Public\Contracts\Tracer;
use Illuminate\Support\ServiceProvider;

/**
 * Observability Foundation service provider.
 *
 * Responsibilities:
 *   - Bind Logger, Metrics, and Tracer contracts to InMemory implementations
 *     for B3.6 Foundation verification.
 */
final class ObservabilityServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(Logger::class, InMemoryLogger::class);
        $this->app->singleton(Metrics::class, InMemoryMetrics::class);
        $this->app->singleton(Tracer::class, InMemoryTracer::class);
    }

    public function boot(): void
    {
        // No boot-time configuration required.
    }
}
