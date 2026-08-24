<?php

declare(strict_types=1);

namespace Tests\Unit\Packages\Base\Foundation\ExtensionRegistry;

use Base\Foundation\ExtensionRegistry\Application\InMemoryExtensionRegistry;
use Base\Foundation\ExtensionRegistry\Public\Contracts\ContributorContract;
use Base\Foundation\ExtensionRegistry\Public\Contracts\DecoratorContract;
use Base\Foundation\ExtensionRegistry\Public\Contracts\ExtensionContract;
use Base\Foundation\ExtensionRegistry\Public\Contracts\MetadataExtensionContract;
use Base\Foundation\ExtensionRegistry\Public\Contracts\StrategyContract;
use Base\Foundation\ExtensionRegistry\Public\Exceptions\ExtensionRegistrationFailed;
use Base\Foundation\ExtensionRegistry\Public\ValueObjects\ContributionDefinition;
use Base\Foundation\ExtensionRegistry\Public\ValueObjects\ExtensionDefinition;
use Base\Foundation\ExtensionRegistry\Public\ValueObjects\ExtensionPoint;
use PHPUnit\Framework\TestCase;

final class InMemoryExtensionRegistryTest extends TestCase
{
    public function test_it_registers_an_extension_and_resolves_its_point(): void
    {
        $registry = new InMemoryExtensionRegistry;
        $point = new ExtensionPoint('navigation.items', ExtensionPoint::CONTRIBUTOR, ContributorContract::class);
        $registry->registerPoint($point);
        $registry->register(new ExtensionDefinition('project.navigation', true, [
            new ContributionDefinition('primary', 'navigation.items', new class implements ContributorContract {}),
        ]));

        $this->assertSame($point, $registry->extensionPoint('navigation.items'));
        $this->assertCount(1, $registry->contributors('navigation.items'));
    }

    public function test_it_collects_contributors_in_deterministic_order(): void
    {
        $registry = $this->contributorRegistry();
        $low = new class implements ContributorContract {};
        $high = new class implements ContributorContract {};
        $registry->register(new ExtensionDefinition('z-extension', true, [
            new ContributionDefinition('low', 'navigation.items', $low, 10),
        ]));
        $registry->register(new ExtensionDefinition('a-extension', true, [
            new ContributionDefinition('high', 'navigation.items', $high, 20),
        ]));

        $this->assertSame([$high, $low], $registry->contributors('navigation.items'));
    }

    public function test_it_registers_each_supported_extension_kind(): void
    {
        $registry = new InMemoryExtensionRegistry;
        $registry->registerPoint(new ExtensionPoint('decorators', ExtensionPoint::DECORATOR, DecoratorContract::class));
        $registry->registerPoint(new ExtensionPoint('strategies', ExtensionPoint::STRATEGY, StrategyContract::class));
        $registry->registerPoint(new ExtensionPoint('metadata', ExtensionPoint::METADATA, MetadataExtensionContract::class));
        $decorator = new class implements DecoratorContract {};
        $strategy = new class implements StrategyContract {};
        $metadata = new class implements MetadataExtensionContract {};
        $registry->register(new ExtensionDefinition('project.extensions', true, [
            new ContributionDefinition('decorator', 'decorators', $decorator),
            new ContributionDefinition('strategy', 'strategies', $strategy),
            new ContributionDefinition('metadata', 'metadata', $metadata),
        ]));

        $this->assertSame([$decorator], $registry->decorators('decorators'));
        $this->assertSame([$strategy], $registry->strategies('strategies'));
        $this->assertSame([$metadata], $registry->metadataExtensions('metadata'));
    }

    public function test_it_rejects_duplicate_incompatible_contributions(): void
    {
        $registry = $this->contributorRegistry();
        $registry->register(new ExtensionDefinition('first', true, [
            new ContributionDefinition('duplicate', 'navigation.items', new class implements ContributorContract {}),
        ]));
        $this->expectException(ExtensionRegistrationFailed::class);
        $this->expectExceptionMessage('Duplicate incompatible contribution');
        $registry->register(new ExtensionDefinition('second', true, [
            new ContributionDefinition('duplicate', 'navigation.items', new class implements ContributorContract {}),
        ]));
    }

    public function test_it_rejects_an_invalid_extension_contract(): void
    {
        $registry = $this->contributorRegistry();
        $this->expectException(ExtensionRegistrationFailed::class);
        $this->expectExceptionMessage('does not implement extension point contract');
        $registry->register(new ExtensionDefinition('invalid', true, [
            new ContributionDefinition('invalid', 'navigation.items', new class implements ExtensionContract {}),
        ]));
    }

    public function test_it_rejects_disabled_extension_registration_without_side_effects(): void
    {
        $registry = $this->contributorRegistry();
        try {
            $registry->register(new ExtensionDefinition('disabled', false, [
                new ContributionDefinition('hidden', 'navigation.items', new class implements ContributorContract {}),
            ]));
            $this->fail('Disabled extensions must fail closed.');
        } catch (ExtensionRegistrationFailed $exception) {
            $this->assertStringContainsString('Disabled extension', $exception->getMessage());
        }
        $this->assertSame([], $registry->contributors('navigation.items'));
    }

    public function test_single_contribution_point_rejects_two_contributions_atomically(): void
    {
        $registry = new InMemoryExtensionRegistry;
        $registry->registerPoint(new ExtensionPoint('single', ExtensionPoint::CONTRIBUTOR, ContributorContract::class, false));
        try {
            $registry->register(new ExtensionDefinition('invalid', true, [
                new ContributionDefinition('first', 'single', new class implements ContributorContract {}),
                new ContributionDefinition('second', 'single', new class implements ContributorContract {}),
            ]));
            $this->fail('Single-contribution points must fail closed.');
        } catch (ExtensionRegistrationFailed $exception) {
            $this->assertStringContainsString('accepts only one contribution', $exception->getMessage());
        }
        $this->assertSame([], $registry->contributors('single'));
    }

    private function contributorRegistry(): InMemoryExtensionRegistry
    {
        $registry = new InMemoryExtensionRegistry;
        $registry->registerPoint(new ExtensionPoint('navigation.items', ExtensionPoint::CONTRIBUTOR, ContributorContract::class));

        return $registry;
    }
}
