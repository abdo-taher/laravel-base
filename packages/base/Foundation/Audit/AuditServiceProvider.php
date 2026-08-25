<?php

declare(strict_types=1);

namespace Base\Foundation\Audit;

use Base\Foundation\Audit\Application\InMemoryAuditRecorder;
use Base\Foundation\Audit\Application\SystemClock;
use Base\Foundation\Audit\Public\Contracts\AuditRecorder;
use Base\Foundation\Audit\Public\Contracts\Clock;
use Illuminate\Support\ServiceProvider;

/**
 * Audit Foundation package service provider.
 *
 * Ownership: base-owned
 * Category:  Foundation
 *
 * Responsibilities:
 *   - Bind AuditRecorder to InMemoryAuditRecorder (for B3.5 Foundation scope).
 *   - Bind local Clock to SystemClock.
 *
 * Public contracts remain Laravel-free.
 */
final class AuditServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(Clock::class, SystemClock::class);
        $this->app->singleton(AuditRecorder::class, InMemoryAuditRecorder::class);
    }

    public function boot(): void
    {
        // No boot-time behavior required.
    }
}
