<?php

declare(strict_types=1);

namespace Base\Tooling\ProjectFactory\Public\ValueObjects\Operations;

use Base\Tooling\ProjectFactory\Public\ValueObjects\SafePath;

final readonly class CopyTreeOperation implements PlannedOperation
{
    public function __construct(
        public string $sourcePackageName,
        public SafePath $targetPath,
    ) {}

    public function description(): string
    {
        return sprintf('Copy package tree %s to %s', $this->sourcePackageName, $this->targetPath->value);
    }
}
