<?php

declare(strict_types=1);

namespace Base\Foundation\Manifest;

use Base\Foundation\Manifest\Infrastructure\JsonManifestReader;
use Base\Foundation\Manifest\Public\Contracts\ManifestReader;
use Illuminate\Support\ServiceProvider;

/**
 * Manifest package service provider.
 *
 * Ownership: base-owned
 * Category:  Foundation
 *
 * Responsibilities:
 *   - module.json schema validation
 *   - Manifest parsing and normalisation
 *   - Manifest-to-runtime object hydration
 *   - Version compatibility validation
 */
final class ManifestServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(ManifestReader::class, JsonManifestReader::class);
    }

    public function boot(): void
    {
        // No boot-time behavior is required.
    }
}
