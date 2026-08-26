<?php

declare(strict_types=1);

namespace Base\Tooling\ProjectFactory\Public\ValueObjects;

use Base\Tooling\ProjectFactory\Public\ValueObjects\Operations\PlannedOperation;

final readonly class GenerationPlan
{
    /**
     * @param  list<GenerationNode>  $resolvedGraph
     * @param  list<PlannedOperation>  $filesystemOperations
     */
    public function __construct(
        public ProjectIdentity $identity,
        public array $resolvedGraph,
        public array $filesystemOperations,
    ) {}
}
