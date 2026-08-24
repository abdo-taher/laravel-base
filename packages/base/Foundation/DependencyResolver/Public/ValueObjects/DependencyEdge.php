<?php

declare(strict_types=1);

namespace Base\Foundation\DependencyResolver\Public\ValueObjects;

final readonly class DependencyEdge
{
    public function __construct(
        public DependencyNode $consumer,
        public DependencyNode $provider,
        public string $targetType,
        public string $versionConstraint,
        public bool $required,
    ) {}
}
