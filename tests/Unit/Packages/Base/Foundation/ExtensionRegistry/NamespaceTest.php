<?php

declare(strict_types=1);

namespace Tests\Unit\Packages\Base\Foundation\ExtensionRegistry;

use Base\Foundation\ExtensionRegistry\ExtensionRegistryServiceProvider;
use PHPUnit\Framework\TestCase;

/**
 * Proves the ExtensionRegistry package namespace is autoloaded correctly.
 *
 * This test has no Laravel dependency — it is a pure Composer autoload probe.
 */
final class NamespaceTest extends TestCase
{
    public function test_extension_registry_service_provider_class_exists(): void
    {
        $this->assertTrue(
            class_exists(ExtensionRegistryServiceProvider::class),
            'ExtensionRegistryServiceProvider must be discoverable via PSR-4 autoloading.',
        );
    }

    public function test_extension_registry_service_provider_has_correct_namespace(): void
    {
        $this->assertSame(
            'Base\Foundation\ExtensionRegistry\ExtensionRegistryServiceProvider',
            ExtensionRegistryServiceProvider::class,
        );
    }
}
