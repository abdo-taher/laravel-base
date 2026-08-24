<?php

declare(strict_types=1);

namespace Tests\Unit\Packages\Base\Foundation\DependencyResolver;

use Base\Foundation\DependencyResolver\Application\ImmutableDependencyGraph;
use Base\Foundation\DependencyResolver\Public\ValueObjects\DependencyEdge;
use Base\Foundation\DependencyResolver\Public\ValueObjects\DependencyNode;
use Base\Foundation\DependencyResolver\Public\ValueObjects\ResolutionResult;
use Base\Foundation\Manifest\Public\ValueObjects\Manifest;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

final class DependencyValueObjectsTest extends TestCase
{
    public function test_dependency_values_and_graph_are_immutable(): void
    {
        self::assertTrue((new ReflectionClass(DependencyNode::class))->isReadOnly());
        self::assertTrue((new ReflectionClass(DependencyEdge::class))->isReadOnly());
        self::assertTrue((new ReflectionClass(ResolutionResult::class))->isReadOnly());
        self::assertTrue((new ReflectionClass(ImmutableDependencyGraph::class))->isReadOnly());
    }

    public function test_resolution_result_exposes_graph_and_order(): void
    {
        $node = new DependencyNode(new Manifest(
            name: 'Manifest',
            category: 'Foundation',
            version: '1.0.0',
            namespace: 'Base\Foundation\Manifest',
            ownership: 'base-owned',
            dependencies: [],
            capabilities: [],
        ));
        $graph = new ImmutableDependencyGraph([$node], []);
        $result = new ResolutionResult($graph, [$node]);

        self::assertSame([$node], $result->graph->nodes());
        self::assertSame([], $result->graph->edges());
        self::assertSame([$node], $result->orderedNodes);
    }
}
