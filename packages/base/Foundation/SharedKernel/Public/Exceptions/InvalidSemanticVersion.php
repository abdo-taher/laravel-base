<?php

declare(strict_types=1);

namespace Base\Foundation\SharedKernel\Public\Exceptions;

use InvalidArgumentException;

/**
 * Thrown when a string cannot be parsed as a valid semantic version.
 */
final class InvalidSemanticVersion extends InvalidArgumentException
{
    public static function forValue(string $value): self
    {
        return new self(sprintf(
            'The value %s is not a valid semantic version (expected MAJOR.MINOR.PATCH[-prerelease][+build]).',
            $value === '' ? '(empty string)' : $value,
        ));
    }
}
