<?php

declare(strict_types=1);

namespace Base\Tooling\ProjectFactory\Public\Exceptions;

use RuntimeException;

final class InvalidPlannedPath extends RuntimeException
{
    public static function unsafe(string $path): self
    {
        return new self(sprintf('Path "%s" is unsafe. Must be relative, contain no traversals, and use canonical separators.', $path));
    }
}
