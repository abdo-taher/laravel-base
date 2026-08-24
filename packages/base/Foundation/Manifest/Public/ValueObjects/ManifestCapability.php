<?php

declare(strict_types=1);

namespace Base\Foundation\Manifest\Public\ValueObjects;

final readonly class ManifestCapability
{
    public function __construct(
        public string $name,
        public string $version,
    ) {}
}
