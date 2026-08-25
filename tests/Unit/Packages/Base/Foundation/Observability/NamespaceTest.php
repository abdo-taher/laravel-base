<?php

declare(strict_types=1);

namespace Tests\Unit\Packages\Base\Foundation\Observability;

use Base\Foundation\Observability\ObservabilityServiceProvider;
use PHPUnit\Framework\TestCase;

final class NamespaceTest extends TestCase
{
    public function test_observability_service_provider_class_exists(): void
    {
        self::assertTrue(class_exists(ObservabilityServiceProvider::class));
    }

    public function test_observability_service_provider_has_correct_namespace(): void
    {
        self::assertSame(
            'Base\Foundation\Observability\ObservabilityServiceProvider',
            ObservabilityServiceProvider::class,
        );
    }
}
