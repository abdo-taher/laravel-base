<?php

declare(strict_types=1);

namespace Base\Foundation\ExtensionRegistry;

use Illuminate\Support\ServiceProvider;

/**
 * ExtensionRegistry package service provider.
 *
 * Ownership: base-owned
 * Category:  Foundation
 *
 * Responsibilities (future):
 *   - Extension discovery (attributes, manifests)
 *   - Contributor registration
 *   - Decorator registration
 *   - Strategy registration
 *   - Optional extension safety (absent module handling)
 *
 * This is a skeleton provider. No bindings are registered until
 * the ExtensionRegistry runtime is implemented.
 */
final class ExtensionRegistryServiceProvider extends ServiceProvider
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
