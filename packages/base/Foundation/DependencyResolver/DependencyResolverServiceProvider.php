<?php

declare(strict_types=1);

namespace Base\Foundation\DependencyResolver;

use Base\Foundation\DependencyResolver\Application\ManifestDependencyResolver;
use Base\Foundation\DependencyResolver\Public\Contracts\DependencyResolver;
use Illuminate\Support\ServiceProvider;

/**
 * DependencyResolver package service provider.
 *
 * Ownership: base-owned
 * Category:  Foundation
 *
 * Responsibilities:
 *   - Dependency graph construction
 *   - Dependency direction validation
 *   - Circular dependency detection
 *   - Deterministic topological ordering
 */
final class DependencyResolverServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(DependencyResolver::class, ManifestDependencyResolver::class);
    }

    public function boot(): void
    {
        // No boot-time behavior is required.
    }
}
