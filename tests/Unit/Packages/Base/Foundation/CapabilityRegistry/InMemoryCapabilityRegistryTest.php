<?php

declare(strict_types=1);

namespace Tests\Unit\Packages\Base\Foundation\CapabilityRegistry;

use Base\Foundation\CapabilityRegistry\Application\InMemoryCapabilityRegistry;
use Base\Foundation\CapabilityRegistry\Application\VersionConstraintMatcher;
use Base\Foundation\CapabilityRegistry\Public\Contracts\CapabilityContract;
use Base\Foundation\CapabilityRegistry\Public\Contracts\CapabilityProviderContract;
use Base\Foundation\CapabilityRegistry\Public\Exceptions\CapabilityResolutionFailed;
use Base\Foundation\CapabilityRegistry\Public\ValueObjects\CapabilityName;
use Base\Foundation\CapabilityRegistry\Public\ValueObjects\CapabilityProviderDefinition;
use Base\Foundation\CapabilityRegistry\Public\ValueObjects\CapabilityVersion;
use PHPUnit\Framework\TestCase;

final class InMemoryCapabilityRegistryTest extends TestCase
{
    public function test_single_provider_resolves(): void
    {
        $registry = $this->registry();
        $provider = $this->provider();
        $registry->register($this->definition('notification.send', '1.0.0', $provider));

        $result = $registry->resolve(new CapabilityName('notification.send'), '1.0.0');

        self::assertTrue($result->isResolved());
        self::assertSame($provider, $result->provider?->provider);
    }

    public function test_compatible_caret_version_resolves(): void
    {
        $registry = $this->registry();
        $definition = $this->definition('notification.send', '1.5.0', $this->provider());
        $registry->register($definition);

        $result = $registry->resolve(new CapabilityName('notification.send'), '^1.0');

        self::assertSame($definition, $result->provider);
    }

    public function test_optional_capability_absence_returns_unresolved_result(): void
    {
        $result = $this->registry()->resolve(
            new CapabilityName('notification.send'),
            '^1.0',
            required: false,
        );

        self::assertFalse($result->isResolved());
        self::assertNull($result->provider);
        self::assertFalse($result->required);
    }

    public function test_explicit_strategy_selects_one_provider(): void
    {
        $registry = $this->registry();
        $email = $this->definition(
            'notification.send',
            '1.0.0',
            $this->provider(),
            strategy: 'email',
        );
        $sms = $this->definition(
            'notification.send',
            '1.0.0',
            $this->provider(),
            strategy: 'sms',
        );
        $registry->register($sms);
        $registry->register($email);

        $result = $registry->resolve(
            new CapabilityName('notification.send'),
            '^1.0',
            strategy: 'email',
        );

        self::assertSame($email, $result->provider);
    }

    public function test_metadata_and_priority_are_preserved_without_automatic_selection(): void
    {
        $registry = $this->registry();
        $definition = $this->definition(
            'search.query',
            '2.0.0',
            $this->provider(),
            metadata: ['region' => 'primary'],
            priority: 100,
        );
        $registry->register($definition);

        $result = $registry->resolve(new CapabilityName('search.query'), '^2.0');

        self::assertNotNull($result->provider);
        self::assertSame(['region' => 'primary'], $result->provider->metadata);
        self::assertSame(100, $result->provider->priority);
    }

    public function test_missing_required_capability_is_rejected(): void
    {
        $this->expectException(CapabilityResolutionFailed::class);
        $this->expectExceptionMessage('Required capability is unavailable: notification.send');

        $this->registry()->resolve(new CapabilityName('notification.send'), '^1.0');
    }

    public function test_incompatible_version_is_rejected(): void
    {
        $registry = $this->registry();
        $registry->register($this->definition('notification.send', '1.5.0', $this->provider()));

        $this->expectException(CapabilityResolutionFailed::class);
        $this->expectExceptionMessage('satisfies version constraint ^2.0');

        $registry->resolve(new CapabilityName('notification.send'), '^2.0');
    }

    public function test_ambiguous_providers_are_rejected_even_with_different_priorities(): void
    {
        $registry = $this->registry();
        $registry->register($this->definition(
            'notification.send',
            '1.0.0',
            $this->provider(),
            priority: 100,
        ));
        $registry->register($this->definition(
            'notification.send',
            '1.1.0',
            $this->provider(),
            priority: 10,
        ));

        $this->expectException(CapabilityResolutionFailed::class);
        $this->expectExceptionMessage('has multiple providers');

        $registry->resolve(new CapabilityName('notification.send'), '^1.0');
    }

    private function registry(): InMemoryCapabilityRegistry
    {
        return new InMemoryCapabilityRegistry(new VersionConstraintMatcher);
    }

    /** @param array<string, mixed> $metadata */
    private function definition(
        string $name,
        string $version,
        CapabilityProviderContract $provider,
        array $metadata = [],
        int $priority = 0,
        ?string $strategy = null,
    ): CapabilityProviderDefinition {
        return new CapabilityProviderDefinition(
            new CapabilityName($name),
            new CapabilityVersion($version),
            $provider,
            $metadata,
            $priority,
            $strategy,
        );
    }

    private function provider(): CapabilityProviderContract
    {
        return new class implements CapabilityProviderContract
        {
            public function provide(): CapabilityContract
            {
                return new class implements CapabilityContract {};
            }
        };
    }
}
