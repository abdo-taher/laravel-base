<?php

declare(strict_types=1);

namespace Tests\Unit\Packages\Base\Foundation\Security;

use Base\Foundation\Security\SecurityServiceProvider;
use PHPUnit\Framework\TestCase;

final class NamespaceTest extends TestCase
{
    public function test_security_service_provider_class_exists(): void
    {
        self::assertTrue(class_exists(SecurityServiceProvider::class));
    }

    public function test_security_service_provider_has_correct_namespace(): void
    {
        self::assertSame(
            'Base\Foundation\Security\SecurityServiceProvider',
            SecurityServiceProvider::class,
        );
    }
}
