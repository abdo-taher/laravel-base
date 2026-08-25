<?php

declare(strict_types=1);

namespace Tests\Unit\Packages\Base\Foundation\Health;

use Base\Foundation\Health\HealthServiceProvider;
use PHPUnit\Framework\TestCase;

final class NamespaceTest extends TestCase
{
    public function test_health_service_provider_class_exists(): void
    {
        self::assertTrue(class_exists(HealthServiceProvider::class));
    }

    public function test_health_service_provider_has_correct_namespace(): void
    {
        self::assertSame(
            'Base\Foundation\Health\HealthServiceProvider',
            HealthServiceProvider::class,
        );
    }
}
