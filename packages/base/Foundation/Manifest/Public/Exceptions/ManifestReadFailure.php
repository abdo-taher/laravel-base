<?php

declare(strict_types=1);

namespace Base\Foundation\Manifest\Public\Exceptions;

use JsonException;
use RuntimeException;

final class ManifestReadFailure extends RuntimeException
{
    public static function unreadable(string $path): self
    {
        return new self(sprintf('Manifest file is not readable: %s', $path));
    }

    public static function invalidJson(string $path, JsonException $previous): self
    {
        return new self(
            sprintf('Manifest file contains invalid JSON: %s', $path),
            previous: $previous,
        );
    }
}
