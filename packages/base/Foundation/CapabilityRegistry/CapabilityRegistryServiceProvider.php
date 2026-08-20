<?php

declare(strict_types=1);

namespace Base\Foundation\CapabilityRegistry;

use Illuminate\Support\ServiceProvider;

/**
 * CapabilityRegistry package service provider.
 *
 * Ownership: base-owned
 * Category:  Foundation
 *
 * Responsibilities (future):
 *   - Capability registration
 *   - Provider discovery and resolution
 *   - Version-aware capability lookup
 *   - Security capability fail-closed enforcement
 *
 * This is a skeleton provider. No bindings are registered until
 * the CapabilityRegistry runtime is implemented.
 */
final class CapabilityRegistryServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // Runtime implementation deferred.
    }

    public function boot(): void
    {
        // Runtime implementation deferred.
    }
}
