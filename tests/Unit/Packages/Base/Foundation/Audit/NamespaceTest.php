<?php

declare(strict_types=1);

namespace Tests\Unit\Packages\Base\Foundation\Audit;

use Base\Foundation\Audit\AuditServiceProvider;
use PHPUnit\Framework\TestCase;

/**
 * Proves the Audit package namespace is autoloaded correctly.
 */
final class NamespaceTest extends TestCase
{
    public function test_audit_service_provider_class_exists(): void
    {
        self::assertTrue(
            class_exists(AuditServiceProvider::class),
            'AuditServiceProvider must be discoverable via PSR-4 autoloading.',
        );
    }

    public function test_audit_service_provider_has_correct_namespace(): void
    {
        self::assertSame(
            'Base\Foundation\Audit\AuditServiceProvider',
            AuditServiceProvider::class,
        );
    }
}
