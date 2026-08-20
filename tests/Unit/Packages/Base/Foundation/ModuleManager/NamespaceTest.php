<?php

declare(strict_types=1);

namespace Tests\Unit\Packages\Base\Foundation\ModuleManager;

use Base\Foundation\ModuleManager\ModuleManagerServiceProvider;
use PHPUnit\Framework\TestCase;

/**
 * Proves the ModuleManager package namespace is autoloaded correctly.
 *
 * This test has no Laravel dependency — it is a pure Composer autoload probe.
 */
final class NamespaceTest extends TestCase
{
    public function test_module_manager_service_provider_class_exists(): void
    {
        $this->assertTrue(
            class_exists(ModuleManagerServiceProvider::class),
            'ModuleManagerServiceProvider must be discoverable via PSR-4 autoloading.',
        );
    }

    public function test_module_manager_service_provider_has_correct_namespace(): void
    {
        $this->assertSame(
            'Base\Foundation\ModuleManager\ModuleManagerServiceProvider',
            ModuleManagerServiceProvider::class,
        );
    }
}
