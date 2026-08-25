<?php

declare(strict_types=1);

namespace Tests\Unit\Packages\Base\Foundation\Configuration;

use Base\Foundation\Configuration\ConfigurationServiceProvider;
use PHPUnit\Framework\TestCase;

/**
 * Proves the Configuration package namespace is autoloaded correctly.
 *
 * No Laravel dependency — pure Composer autoload probe.
 */
final class NamespaceTest extends TestCase
{
    public function test_configuration_service_provider_class_exists(): void
    {
        self::assertTrue(
            class_exists(ConfigurationServiceProvider::class),
            'ConfigurationServiceProvider must be discoverable via PSR-4 autoloading.',
        );
    }

    public function test_configuration_service_provider_has_correct_namespace(): void
    {
        self::assertSame(
            'Base\Foundation\Configuration\ConfigurationServiceProvider',
            ConfigurationServiceProvider::class,
        );
    }
}
