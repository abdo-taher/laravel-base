<?php

declare(strict_types=1);

namespace Base\Foundation\AccessControl;

use Base\Foundation\AccessControl\Application\PolicyEvaluator;
use Base\Foundation\AccessControl\Public\Contracts\AuthorizationChecker;
use Illuminate\Support\ServiceProvider;

/**
 * AccessControl Foundation package service provider.
 *
 * Ownership: base-owned
 * Category:  Foundation
 *
 * Responsibilities:
 *   - Bind AuthorizationChecker to the PolicyEvaluator implementation.
 *
 * Policy contributor discovery and wiring are deferred until the full
 * ExtensionRegistry runtime is available (consistent with
 * Configuration and Identity approach).
 *
 * Public contracts remain Laravel-free.
 */
final class AccessControlServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(
            AuthorizationChecker::class,
            PolicyEvaluator::class,
        );
    }

    public function boot(): void
    {
        // No boot-time behavior in B3.4.
        // AuthorizationPolicy contributor wiring is deferred to
        // the full ExtensionRegistry runtime.
    }
}
