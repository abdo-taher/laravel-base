<?php

declare(strict_types=1);

namespace Base\Tooling\ProjectFactory\Public\ValueObjects\Operations;

use Base\Tooling\ProjectFactory\Public\ValueObjects\SafePath;

final readonly class CreateDirectoryOperation implements PlannedOperation
{
    public function __construct(
        public SafePath $targetPath,
    ) {}

    public function description(): string
    {
        return sprintf('Create directory %s', $this->targetPath->value);
    }
}
