<?php

declare(strict_types=1);

namespace Tests\Unit\Packages\Base\Foundation\CapabilityRegistry;

use Base\Foundation\CapabilityRegistry\CapabilityRegistryServiceProvider;
use PHPUnit\Framework\TestCase;

/**
 * Proves the CapabilityRegistry package namespace is autoloaded correctly.
 *
 * This test has no Laravel dependency — it is a pure Composer autoload probe.
 */
final class NamespaceTest extends TestCase
{
    public function test_capability_registry_service_provider_class_exists(): void
    {
        $this->assertTrue(
            class_exists(CapabilityRegistryServiceProvider::class),
            'CapabilityRegistryServiceProvider must be discoverable via PSR-4 autoloading.',
        );
    }

    public function test_capability_registry_service_provider_has_correct_namespace(): void
    {
        $this->assertSame(
            'Base\Foundation\CapabilityRegistry\CapabilityRegistryServiceProvider',
            CapabilityRegistryServiceProvider::class,
        );
    }
}
