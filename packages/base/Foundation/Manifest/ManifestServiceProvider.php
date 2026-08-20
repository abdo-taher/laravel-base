<?php

declare(strict_types=1);

namespace Base\Foundation\Manifest;

use Illuminate\Support\ServiceProvider;

/**
 * Manifest package service provider.
 *
 * Ownership: base-owned
 * Category:  Foundation
 *
 * Responsibilities (future):
 *   - module.json schema validation
 *   - Manifest parsing and normalisation
 *   - Manifest-to-runtime object hydration
 *   - Version compatibility validation
 *
 * This is a skeleton provider. No bindings are registered until
 * the Manifest runtime is implemented.
 */
final class ManifestServiceProvider extends ServiceProvider
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
