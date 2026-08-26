<?php

declare(strict_types=1);

namespace Base\Tooling\ProjectFactory\Public\Exceptions;

use RuntimeException;

final class InvalidProjectIdentity extends RuntimeException
{
    public static function invalidName(): self
    {
        return new self('Project name must be a non-empty human-readable string.');
    }

    public static function invalidSlug(string $slug): self
    {
        return new self(sprintf('Project slug "%s" is invalid. Must be lowercase alphanumeric with dashes.', $slug));
    }

    public static function invalidNamespace(string $namespace): self
    {
        return new self(sprintf('Project namespace "%s" is not a valid PHP namespace.', $namespace));
    }
}
