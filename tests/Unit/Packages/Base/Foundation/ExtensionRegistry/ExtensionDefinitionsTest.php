<?php

declare(strict_types=1);

namespace Tests\Unit\Packages\Base\Foundation\ExtensionRegistry;

use Base\Foundation\ExtensionRegistry\Public\Attributes\ExtensionMetadata;
use Base\Foundation\ExtensionRegistry\Public\Contracts\StrategyContract;
use Base\Foundation\ExtensionRegistry\Public\ValueObjects\ContributionDefinition;
use Base\Foundation\ExtensionRegistry\Public\ValueObjects\ExtensionDefinition;
use Base\Foundation\ExtensionRegistry\Public\ValueObjects\ExtensionPoint;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

final class ExtensionDefinitionsTest extends TestCase
{
    public function test_attribute_exposes_scanner_independent_metadata(): void
    {
        $metadata = new ExtensionMetadata('project.navigation', 'navigation.items', 'primary', 50);
        $this->assertSame('project.navigation', $metadata->extensionId());
        $this->assertSame('navigation.items', $metadata->extensionPoint());
        $this->assertSame('primary', $metadata->contributionId());
        $this->assertSame(50, $metadata->priority());
    }

    /** @param class-string<object> $class */
    #[DataProvider('readonlyDefinitions')]
    public function test_definitions_are_immutable(string $class): void
    {
        $this->assertTrue((new ReflectionClass($class))->isReadOnly());
    }

    /** @return iterable<string, array{class-string}> */
    public static function readonlyDefinitions(): iterable
    {
        yield 'extension definition' => [ExtensionDefinition::class];
        yield 'extension point' => [ExtensionPoint::class];
        yield 'contribution definition' => [ContributionDefinition::class];
        yield 'attribute metadata' => [ExtensionMetadata::class];
    }

    public function test_extension_point_contract_must_match_its_kind(): void
    {
        $this->expectException(InvalidArgumentException::class);
        new ExtensionPoint('invalid', ExtensionPoint::CONTRIBUTOR, StrategyContract::class);
    }
}
