<?php

declare(strict_types=1);

namespace Base\Foundation\Manifest\Public\ValueObjects;

final readonly class ManifestDependency
{
    public function __construct(
        public string $targetType,
        public string $target,
        public string $version,
        public bool $required,
    ) {}
}
