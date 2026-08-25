<?php

declare(strict_types=1);

namespace Base\Foundation\Identity;

use Base\Foundation\Identity\Infrastructure\LaravelAuthenticator;
use Base\Foundation\Identity\Infrastructure\LaravelCurrentPrincipal;
use Base\Foundation\Identity\Public\Contracts\Authenticator;
use Base\Foundation\Identity\Public\Contracts\CurrentPrincipal;
use Illuminate\Support\ServiceProvider;

/**
 * Identity Foundation package service provider.
 *
 * Ownership: base-owned
 * Category:  Foundation
 *
 * Responsibilities:
 *   - Bind Authenticator to the Laravel-backed implementation.
 *   - Bind CurrentPrincipal to the Laravel-backed implementation.
 *
 * Authentication mechanism adapters live in Infrastructure.
 * Public contracts remain Laravel-free.
 *
 * PrincipalEnricher discovery and enrichment pipeline wiring are
 * deferred until the full ExtensionRegistry runtime is available.
 */
final class IdentityServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(
            Authenticator::class,
            LaravelAuthenticator::class,
        );

        $this->app->singleton(
            CurrentPrincipal::class,
            LaravelCurrentPrincipal::class,
        );
    }

    public function boot(): void
    {
        // No boot-time behavior in B3.3.
        // PrincipalEnricher pipeline wiring is deferred.
    }
}
