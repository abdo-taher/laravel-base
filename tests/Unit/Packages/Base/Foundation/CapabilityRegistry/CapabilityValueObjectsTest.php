<?php

declare(strict_types=1);

namespace Tests\Unit\Packages\Base\Foundation\CapabilityRegistry;

use Base\Foundation\CapabilityRegistry\Application\VersionConstraintMatcher;
use Base\Foundation\CapabilityRegistry\Public\Contracts\CapabilityContract;
use Base\Foundation\CapabilityRegistry\Public\Contracts\CapabilityProviderContract;
use Base\Foundation\CapabilityRegistry\Public\Exceptions\InvalidCapabilityDefinition;
use Base\Foundation\CapabilityRegistry\Public\ValueObjects\CapabilityName;
use Base\Foundation\CapabilityRegistry\Public\ValueObjects\CapabilityProviderDefinition;
use Base\Foundation\CapabilityRegistry\Public\ValueObjects\CapabilityResolutionResult;
use Base\Foundation\CapabilityRegistry\Public\ValueObjects\CapabilityVersion;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

final class CapabilityValueObjectsTest extends TestCase
{
    public function test_capability_values_are_readonly(): void
    {
        self::assertTrue((new ReflectionClass(CapabilityName::class))->isReadOnly());
        self::assertTrue((new ReflectionClass(CapabilityVersion::class))->isReadOnly());
        self::assertTrue((new ReflectionClass(CapabilityProviderDefinition::class))->isReadOnly());
        self::assertTrue((new ReflectionClass(CapabilityResolutionResult::class))->isReadOnly());
    }

    #[DataProvider('invalidNames')]
    public function test_invalid_capability_names_are_rejected(string $name): void
    {
        $this->expectException(InvalidCapabilityDefinition::class);

        new CapabilityName($name);
    }

    /** @return iterable<string, array{string}> */
    public static function invalidNames(): iterable
    {
        yield 'empty' => [''];
        yield 'uppercase' => ['Notification.Send'];
        yield 'spaces' => ['notification send'];
    }

    public function test_invalid_capability_version_is_rejected(): void
    {
        $this->expectException(InvalidCapabilityDefinition::class);
        $this->expectExceptionMessage('Invalid capability version');

        new CapabilityVersion('1.0');
    }

    public function test_unsupported_constraint_is_rejected(): void
    {
        $this->expectException(InvalidCapabilityDefinition::class);
        $this->expectExceptionMessage('Unsupported capability version constraint');

        (new VersionConstraintMatcher)->matches(new CapabilityVersion('1.0.0'), '~1.0');
    }

    public function test_provider_definition_preserves_strategy_foundation(): void
    {
        $provider = new class implements CapabilityProviderContract
        {
            public function provide(): CapabilityContract
            {
                return new class implements CapabilityContract {};
            }
        };
        $definition = new CapabilityProviderDefinition(
            name: new CapabilityName('notification.send'),
            version: new CapabilityVersion('1.2.0'),
            provider: $provider,
            metadata: ['channel' => 'email'],
            priority: 50,
            strategy: 'email',
        );

        self::assertSame('notification.send', $definition->name->value);
        self::assertSame('1.2.0', $definition->version->value);
        self::assertSame(['channel' => 'email'], $definition->metadata);
        self::assertSame(50, $definition->priority);
        self::assertSame('email', $definition->strategy);
    }
}
