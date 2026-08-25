<?php

declare(strict_types=1);

namespace Tests\Unit\Packages\Base\Foundation\Identity;

use Base\Foundation\Identity\IdentityServiceProvider;
use PHPUnit\Framework\TestCase;

/**
 * Proves the Identity package namespace is autoloaded correctly.
 *
 * No Laravel dependency — pure Composer autoload probe.
 */
final class NamespaceTest extends TestCase
{
    public function test_identity_service_provider_class_exists(): void
    {
        self::assertTrue(
            class_exists(IdentityServiceProvider::class),
            'IdentityServiceProvider must be discoverable via PSR-4 autoloading.',
        );
    }

    public function test_identity_service_provider_has_correct_namespace(): void
    {
        self::assertSame(
            'Base\Foundation\Identity\IdentityServiceProvider',
            IdentityServiceProvider::class,
        );
    }
}
