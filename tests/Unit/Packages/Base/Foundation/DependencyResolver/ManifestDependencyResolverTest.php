<?php

declare(strict_types=1);

namespace Tests\Unit\Packages\Base\Foundation\DependencyResolver;

use Base\Foundation\DependencyResolver\Application\ManifestDependencyResolver;
use Base\Foundation\DependencyResolver\Public\Exceptions\DependencyResolutionFailed;
use Base\Foundation\Manifest\Public\ValueObjects\Manifest;
use Base\Foundation\Manifest\Public\ValueObjects\ManifestCapability;
use Base\Foundation\Manifest\Public\ValueObjects\ManifestDependency;
use PHPUnit\Framework\TestCase;

final class ManifestDependencyResolverTest extends TestCase
{
    public function test_it_builds_a_valid_dependency_graph(): void
    {
        $manifest = $this->manifest(
            'Manifest',
            'Foundation',
            capabilities: [new ManifestCapability('manifest.read', '1.0.0')],
        );
        $notifications = $this->manifest(
            'Notifications',
            'Platform',
            [new ManifestDependency('capability', 'manifest.read', '^1.0', true)],
        );
        $orders = $this->manifest(
            'Orders',
            'Product',
            [new ManifestDependency('package', 'Notifications', '^1.0', true)],
        );

        $result = (new ManifestDependencyResolver)->resolve([$orders, $notifications, $manifest]);

        self::assertSame(
            ['Manifest', 'Notifications', 'Orders'],
            array_map(static fn ($node): string => $node->name(), $result->graph->nodes()),
        );
        self::assertCount(2, $result->graph->edges());
        self::assertSame('Notifications', $result->graph->edges()[0]->consumer->name());
        self::assertSame('Manifest', $result->graph->edges()[0]->provider->name());
        self::assertSame(
            ['Manifest', 'Notifications', 'Orders'],
            array_map(static fn ($node): string => $node->name(), $result->orderedNodes),
        );
    }

    public function test_ordering_is_deterministic_for_different_input_orders(): void
    {
        $alpha = $this->manifest('Alpha', 'Foundation');
        $beta = $this->manifest('Beta', 'Foundation');
        $consumer = $this->manifest(
            'Consumer',
            'Platform',
            [new ManifestDependency('package', 'Alpha', '^1.0', true)],
        );
        $resolver = new ManifestDependencyResolver;

        $first = $resolver->resolve([$consumer, $beta, $alpha]);
        $second = $resolver->resolve([$alpha, $consumer, $beta]);
        $names = static fn ($result): array => array_map(
            static fn ($node): string => $node->name(),
            $result->orderedNodes,
        );

        self::assertSame(['Alpha', 'Beta', 'Consumer'], $names($first));
        self::assertSame($names($first), $names($second));
    }

    public function test_missing_optional_dependency_does_not_fail(): void
    {
        $consumer = $this->manifest(
            'Consumer',
            'Platform',
            [new ManifestDependency('package', 'OptionalPackage', '^1.0', false)],
        );

        $result = (new ManifestDependencyResolver)->resolve([$consumer]);

        self::assertSame(['Consumer'], array_map(
            static fn ($node): string => $node->name(),
            $result->orderedNodes,
        ));
        self::assertSame([], $result->graph->edges());
    }

    public function test_it_rejects_a_missing_required_dependency(): void
    {
        $consumer = $this->manifest(
            'Consumer',
            'Platform',
            [new ManifestDependency('package', 'MissingPackage', '^1.0', true)],
        );

        $this->expectException(DependencyResolutionFailed::class);
        $this->expectExceptionMessage('Consumer requires missing package dependency MissingPackage');

        (new ManifestDependencyResolver)->resolve([$consumer]);
    }

    public function test_it_rejects_a_circular_dependency(): void
    {
        $alpha = $this->manifest(
            'Alpha',
            'Foundation',
            [new ManifestDependency('package', 'Beta', '^1.0', true)],
        );
        $beta = $this->manifest(
            'Beta',
            'Foundation',
            [new ManifestDependency('package', 'Alpha', '^1.0', true)],
        );

        $this->expectException(DependencyResolutionFailed::class);
        $this->expectExceptionMessage('circular dependency detected involving: Alpha, Beta');

        (new ManifestDependencyResolver)->resolve([$alpha, $beta]);
    }

    public function test_it_rejects_a_forbidden_dependency_direction(): void
    {
        $foundation = $this->manifest(
            'FoundationConsumer',
            'Foundation',
            [new ManifestDependency('package', 'PlatformProvider', '^1.0', true)],
        );
        $platform = $this->manifest('PlatformProvider', 'Platform');

        $this->expectException(DependencyResolutionFailed::class);
        $this->expectExceptionMessage(
            'forbidden dependency direction: FoundationConsumer (Foundation) -> PlatformProvider (Platform)',
        );

        (new ManifestDependencyResolver)->resolve([$foundation, $platform]);
    }

    public function test_it_allows_platform_to_platform_dependency(): void
    {
        $platform1 = $this->manifest(
            'Platform1',
            'Platform',
            [new ManifestDependency('package', 'Platform2', '^1.0', true)],
        );
        $platform2 = $this->manifest('Platform2', 'Platform');

        $result = (new ManifestDependencyResolver)->resolve([$platform1, $platform2]);
        self::assertCount(2, $result->orderedNodes);
    }

    public function test_it_rejects_platform_to_product_dependency(): void
    {
        $platform = $this->manifest(
            'PlatformConsumer',
            'Platform',
            [new ManifestDependency('package', 'ProductProvider', '^1.0', true)],
        );
        $product = $this->manifest('ProductProvider', 'Product');

        $this->expectException(DependencyResolutionFailed::class);
        $this->expectExceptionMessage(
            'forbidden dependency direction: PlatformConsumer (Platform) -> ProductProvider (Product)',
        );

        (new ManifestDependencyResolver)->resolve([$platform, $product]);
    }

    public function test_it_rejects_a_duplicate_dependency_declaration(): void
    {
        $provider = $this->manifest('Provider', 'Foundation');
        $consumer = $this->manifest(
            'Consumer',
            'Platform',
            [
                new ManifestDependency('package', 'Provider', '^1.0', true),
                new ManifestDependency('package', 'Provider', '^1.0', true),
            ],
        );

        $this->expectException(DependencyResolutionFailed::class);
        $this->expectExceptionMessage('Consumer declares dependency package:Provider more than once');

        (new ManifestDependencyResolver)->resolve([$provider, $consumer]);
    }

    public function test_it_rejects_an_empty_version_constraint(): void
    {
        $provider = $this->manifest('Provider', 'Foundation');
        $consumer = $this->manifest(
            'Consumer',
            'Platform',
            [new ManifestDependency('package', 'Provider', '', true)],
        );

        $this->expectException(DependencyResolutionFailed::class);
        $this->expectExceptionMessage('Consumer dependency package:Provider must declare a version constraint');

        (new ManifestDependencyResolver)->resolve([$provider, $consumer]);
    }

    public function test_it_rejects_ambiguous_capability_providers(): void
    {
        $first = $this->manifest(
            'FirstProvider',
            'Foundation',
            capabilities: [new ManifestCapability('shared.read', '1.0.0')],
        );
        $second = $this->manifest(
            'SecondProvider',
            'Foundation',
            capabilities: [new ManifestCapability('shared.read', '1.0.0')],
        );
        $consumer = $this->manifest(
            'Consumer',
            'Platform',
            [new ManifestDependency('capability', 'shared.read', '^1.0', true)],
        );

        $this->expectException(DependencyResolutionFailed::class);
        $this->expectExceptionMessage('capability shared.read has multiple providers');

        (new ManifestDependencyResolver)->resolve([$first, $second, $consumer]);
    }

    /**
     * @param  list<ManifestDependency>  $dependencies
     * @param  list<ManifestCapability>  $capabilities
     */
    private function manifest(
        string $name,
        string $category,
        array $dependencies = [],
        array $capabilities = [],
    ): Manifest {
        $namespaceRoot = $category === 'Product' ? 'Modules' : 'Base';

        return new Manifest(
            name: $name,
            category: $category,
            version: '1.0.0',
            namespace: $namespaceRoot.'\\'.$name,
            ownership: $category === 'Product' ? 'project-owned' : 'base-owned',
            dependencies: $dependencies,
            capabilities: $capabilities,
        );
    }
}
