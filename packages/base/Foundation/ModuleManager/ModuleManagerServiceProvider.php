<?php

declare(strict_types=1);

namespace Base\Foundation\ModuleManager;

use Illuminate\Support\ServiceProvider;

/**
 * ModuleManager package service provider.
 *
 * Ownership: base-owned
 * Category:  Foundation
 *
 * Responsibilities (future):
 *   - Module discovery
 *   - Manifest loading
 *   - Module dependency validation
 *   - Module lifecycle management
 *
 * This is a skeleton provider. No bindings are registered until
 * the ModuleManager runtime is implemented.
 */
final class ModuleManagerServiceProvider extends ServiceProvider
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
