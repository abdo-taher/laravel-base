<?php

declare(strict_types=1);

namespace Base\Foundation\CapabilityRegistry;

use Base\Foundation\CapabilityRegistry\Application\InMemoryCapabilityRegistry;
use Base\Foundation\CapabilityRegistry\Public\Contracts\CapabilityResolver;
use Illuminate\Support\ServiceProvider;

/**
 * CapabilityRegistry package service provider.
 *
 * Ownership: base-owned
 * Category:  Foundation
 *
 * Responsibilities:
 *   - Capability provider registration
 *   - Version-aware capability resolution
 *   - Optional capability absence
 *   - Ambiguous-provider fail-closed behavior
 */
final class CapabilityRegistryServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(CapabilityResolver::class, InMemoryCapabilityRegistry::class);
    }

    public function boot(): void
    {
        // No boot-time behavior or provider discovery is implemented in B2.3.
    }
}
