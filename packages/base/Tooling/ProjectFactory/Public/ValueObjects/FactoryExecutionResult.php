<?php

declare(strict_types=1);

namespace Base\Tooling\ProjectFactory\Public\ValueObjects;

final readonly class FactoryExecutionResult
{
    public function __construct(
        public ProjectIdentity $identity,
        public ProjectDestination $destination,
        public int $operationsExecuted,
        public bool $published,
    ) {}
}
