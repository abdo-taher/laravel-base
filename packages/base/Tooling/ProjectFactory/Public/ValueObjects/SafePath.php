<?php

declare(strict_types=1);

namespace Base\Tooling\ProjectFactory\Public\ValueObjects;

use Base\Tooling\ProjectFactory\Public\Exceptions\InvalidPlannedPath;

final readonly class SafePath
{
    public function __construct(public string $value)
    {
        if ($value === '') {
            throw InvalidPlannedPath::unsafe($value);
        }

        if (str_starts_with($value, '/') || str_starts_with($value, '\\') || preg_match('/^[a-zA-Z]:\\\\/', $value)) {
            throw InvalidPlannedPath::unsafe($value);
        }

        if (str_contains($value, '..') || str_contains($value, "\0") || str_contains($value, '\\')) {
            throw InvalidPlannedPath::unsafe($value);
        }
    }

    public static function fromString(string $value): self
    {
        return new self($value);
    }
}
