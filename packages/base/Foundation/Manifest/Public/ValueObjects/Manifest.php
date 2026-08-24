<?php

declare(strict_types=1);

namespace Base\Foundation\Manifest\Public\ValueObjects;

final readonly class Manifest
{
    /**
     * @param  list<ManifestDependency>  $dependencies
     * @param  list<ManifestCapability>  $capabilities
     */
    public function __construct(
        public string $name,
        public string $category,
        public string $version,
        public string $namespace,
        public string $ownership,
        public array $dependencies,
        public array $capabilities,
    ) {}
}
