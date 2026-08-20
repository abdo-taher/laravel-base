<?php

declare(strict_types=1);

namespace Base\Foundation\DependencyResolver;

use Illuminate\Support\ServiceProvider;

/**
 * DependencyResolver package service provider.
 *
 * Ownership: base-owned
 * Category:  Foundation
 *
 * Responsibilities (future):
 *   - Module dependency graph construction
 *   - Circular dependency detection
 *   - Topological sort for load order
 *   - Version constraint satisfaction
 *
 * This is a skeleton provider. No bindings are registered until
 * the DependencyResolver runtime is implemented.
 */
final class DependencyResolverServiceProvider extends ServiceProvider
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
