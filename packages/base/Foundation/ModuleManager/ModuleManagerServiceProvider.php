<?php

declare(strict_types=1);

namespace Base\Foundation\ModuleManager;

use Base\Foundation\CapabilityRegistry\Public\Contracts\CapabilityResolver;
use Base\Foundation\DependencyResolver\Public\Contracts\DependencyResolver;
use Base\Foundation\Manifest\Public\Contracts\ManifestReader;
use Base\Foundation\ModuleManager\Application\FilesystemModuleDiscovery;
use Base\Foundation\ModuleManager\Application\OrchestrationModuleManager;
use Base\Foundation\ModuleManager\Public\Contracts\ModuleDiscovery;
use Base\Foundation\ModuleManager\Public\Contracts\ModuleManager;
use Illuminate\Support\ServiceProvider;

/**
 * ModuleManager package service provider.
 *
 * Ownership: base-owned
 * Category:  Foundation
 *
 * Responsibilities:
 *   - Module discovery (filesystem manifest scanning)
 *   - Manifest validation orchestration (via ManifestReader)
 *   - Dependency resolution orchestration (via DependencyResolver)
 *   - Capability registration orchestration (via CapabilityResolver)
 *   - Deterministic boot plan production
 *
 * Extension point registration orchestration is deferred until the
 * Manifest value object carries extension_points metadata (post B2.5).
 * Full lifecycle management is also deferred.
 */
final class ModuleManagerServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(
            ModuleDiscovery::class,
            fn (): FilesystemModuleDiscovery => new FilesystemModuleDiscovery(
                $this->app->make(ManifestReader::class),
            ),
        );

        $this->app->singleton(
            ModuleManager::class,
            fn (): OrchestrationModuleManager => new OrchestrationModuleManager(
                $this->app->make(ModuleDiscovery::class),
                $this->app->make(DependencyResolver::class),
                $this->app->make(CapabilityResolver::class),
            ),
        );
    }

    public function boot(): void
    {
        // No boot-time behavior. Callers invoke ModuleManager::boot() explicitly.
    }
}
