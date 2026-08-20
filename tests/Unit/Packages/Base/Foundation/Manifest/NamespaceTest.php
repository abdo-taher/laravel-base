<?php

declare(strict_types=1);

namespace Tests\Unit\Packages\Base\Foundation\Manifest;

use Base\Foundation\Manifest\ManifestServiceProvider;
use PHPUnit\Framework\TestCase;

/**
 * Proves the Manifest package namespace is autoloaded correctly.
 *
 * This test has no Laravel dependency — it is a pure Composer autoload probe.
 */
final class NamespaceTest extends TestCase
{
    public function test_manifest_service_provider_class_exists(): void
    {
        $this->assertTrue(
            class_exists(ManifestServiceProvider::class),
            'ManifestServiceProvider must be discoverable via PSR-4 autoloading.',
        );
    }

    public function test_manifest_service_provider_has_correct_namespace(): void
    {
        $this->assertSame(
            'Base\Foundation\Manifest\ManifestServiceProvider',
            ManifestServiceProvider::class,
        );
    }
}
