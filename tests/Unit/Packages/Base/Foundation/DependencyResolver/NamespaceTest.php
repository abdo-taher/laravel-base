<?php

declare(strict_types=1);

namespace Tests\Unit\Packages\Base\Foundation\DependencyResolver;

use Base\Foundation\DependencyResolver\DependencyResolverServiceProvider;
use PHPUnit\Framework\TestCase;

/**
 * Proves the DependencyResolver package namespace is autoloaded correctly.
 *
 * This test has no Laravel dependency — it is a pure Composer autoload probe.
 */
final class NamespaceTest extends TestCase
{
    public function test_dependency_resolver_service_provider_class_exists(): void
    {
        $this->assertTrue(
            class_exists(DependencyResolverServiceProvider::class),
            'DependencyResolverServiceProvider must be discoverable via PSR-4 autoloading.',
        );
    }

    public function test_dependency_resolver_service_provider_has_correct_namespace(): void
    {
        $this->assertSame(
            'Base\Foundation\DependencyResolver\DependencyResolverServiceProvider',
            DependencyResolverServiceProvider::class,
        );
    }
}
