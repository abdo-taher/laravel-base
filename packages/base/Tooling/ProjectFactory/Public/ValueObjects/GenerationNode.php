<?php

declare(strict_types=1);

namespace Base\Tooling\ProjectFactory\Public\ValueObjects;

use Base\Foundation\Manifest\Public\ValueObjects\Manifest;

final readonly class GenerationNode
{
    public function __construct(
        public Manifest $manifest,
        public SelectionReason $reason,
        public ?string $explanation = null,
    ) {}
}
