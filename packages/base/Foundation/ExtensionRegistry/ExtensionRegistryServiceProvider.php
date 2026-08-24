<?php

declare(strict_types=1);

namespace Base\Foundation\ExtensionRegistry;

use Base\Foundation\ExtensionRegistry\Application\InMemoryExtensionRegistry;
use Base\Foundation\ExtensionRegistry\Public\Contracts\ExtensionRegistry;
use Illuminate\Support\ServiceProvider;

/**
 * Registers the in-memory ExtensionRegistry foundation. Discovery and runtime
 * boot orchestration remain outside this package slice.
 */
final class ExtensionRegistryServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(ExtensionRegistry::class, InMemoryExtensionRegistry::class);
    }

    public function boot(): void
    {
        // Runtime implementation deferred.
    }
}
