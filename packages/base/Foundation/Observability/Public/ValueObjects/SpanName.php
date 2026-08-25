<?php

declare(strict_types=1);

namespace Base\Foundation\Observability\Public\ValueObjects;

use InvalidArgumentException;

/**
 * Ensures trace span names are well-formed strings.
 */
final readonly class SpanName
{
    public function __construct(public string $value)
    {
        if (trim($value) === '') {
            throw new InvalidArgumentException('SpanName cannot be empty.');
        }
    }

    public function toString(): string
    {
        return $this->value;
    }
}
