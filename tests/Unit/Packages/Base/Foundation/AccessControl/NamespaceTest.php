<?php

declare(strict_types=1);

namespace Tests\Unit\Packages\Base\Foundation\AccessControl;

use Base\Foundation\AccessControl\AccessControlServiceProvider;
use PHPUnit\Framework\TestCase;

/**
 * Proves the AccessControl package namespace is autoloaded correctly.
 *
 * No Laravel dependency — pure Composer autoload probe.
 */
final class NamespaceTest extends TestCase
{
    public function test_access_control_service_provider_class_exists(): void
    {
        self::assertTrue(
            class_exists(AccessControlServiceProvider::class),
            'AccessControlServiceProvider must be discoverable via PSR-4 autoloading.',
        );
    }

    public function test_access_control_service_provider_has_correct_namespace(): void
    {
        self::assertSame(
            'Base\Foundation\AccessControl\AccessControlServiceProvider',
            AccessControlServiceProvider::class,
        );
    }
}
