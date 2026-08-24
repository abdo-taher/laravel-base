<?php

declare(strict_types=1);

namespace Base\Foundation\DependencyResolver\Public\Contracts;

use Base\Foundation\DependencyResolver\Public\ValueObjects\DependencyEdge;
use Base\Foundation\DependencyResolver\Public\ValueObjects\DependencyNode;

interface DependencyGraph
{
    /** @return list<DependencyNode> */
    public function nodes(): array;

    /** @return list<DependencyEdge> */
    public function edges(): array;
}
