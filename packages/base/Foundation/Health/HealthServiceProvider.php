<?php

declare(strict_types=1);

namespace Base\Foundation\Health;

use Base\Foundation\Health\Application\InMemoryHealthRegistry;
use Base\Foundation\Health\Application\SystemHealthReporter;
use Base\Foundation\Health\Public\Contracts\HealthRegistry;
use Base\Foundation\Health\Public\Contracts\HealthReporter;
use Illuminate\Support\ServiceProvider;

/**
 * Health Foundation service provider.
 */
final class HealthServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(HealthRegistry::class, InMemoryHealthRegistry::class);
        $this->app->singleton(HealthReporter::class, SystemHealthReporter::class);
    }

    public function boot(): void
    {
        // Registration of actual checks belongs in downstream packages.
    }
}
