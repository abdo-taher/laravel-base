<?php

declare(strict_types=1);

namespace Base\Foundation\DependencyResolver\Application;

use Base\Foundation\DependencyResolver\Public\Contracts\DependencyGraph;
use Base\Foundation\DependencyResolver\Public\ValueObjects\DependencyEdge;
use Base\Foundation\DependencyResolver\Public\ValueObjects\DependencyNode;

final readonly class ImmutableDependencyGraph implements DependencyGraph
{
    /**
     * @param  list<DependencyNode>  $nodes
     * @param  list<DependencyEdge>  $edges
     */
    public function __construct(
        private array $nodes,
        private array $edges,
    ) {}

    public function nodes(): array
    {
        return $this->nodes;
    }

    public function edges(): array
    {
        return $this->edges;
    }
}
