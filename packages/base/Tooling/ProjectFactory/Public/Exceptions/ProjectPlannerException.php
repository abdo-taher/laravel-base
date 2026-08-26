<?php

declare(strict_types=1);

namespace Base\Tooling\ProjectFactory\Public\Exceptions;

use RuntimeException;

class ProjectPlannerException extends RuntimeException
{
    public static function unknownModule(string $module): self
    {
        return new self(sprintf('Unknown explicit module requested: %s', $module));
    }
}
