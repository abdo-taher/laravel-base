<?php

declare(strict_types=1);

namespace Base\Foundation\DependencyResolver\Public\ValueObjects;

use Base\Foundation\DependencyResolver\Public\Contracts\DependencyGraph;

final readonly class ResolutionResult
{
    /** @param list<DependencyNode> $orderedNodes */
    public function __construct(
        public DependencyGraph $graph,
        public array $orderedNodes,
    ) {}
}
