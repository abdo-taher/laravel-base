<?php

declare(strict_types=1);

namespace Base\Tooling\ProjectFactory\Public\ValueObjects;

use Base\Tooling\ProjectFactory\Public\Exceptions\InvalidPlannedPath;

final readonly class ProjectDestination
{
    public function __construct(public string $value)
    {
        if ($value === '' || str_contains($value, "\0")) {
            throw InvalidPlannedPath::unsafe($value);
        }

        // MVP: only absolute paths allowed for final destination.
        // Simplified unix absolute check (or Windows drive root).
        if (! str_starts_with($value, '/') && ! preg_match('/^[A-Za-z]:\\\\/', $value)) {
            throw InvalidPlannedPath::unsafe($value);
        }

        if (str_contains($value, '..')) {
            throw InvalidPlannedPath::unsafe($value);
        }
    }

    public static function fromString(string $value): self
    {
        return new self($value);
    }
}
