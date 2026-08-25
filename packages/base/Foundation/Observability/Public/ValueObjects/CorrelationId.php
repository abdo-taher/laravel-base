<?php

declare(strict_types=1);

namespace Base\Foundation\Observability\Public\ValueObjects;

use InvalidArgumentException;

/**
 * An immutable, non-empty correlation identifier used to tie logs,
 * metrics, and spans together across boundaries.
 *
 * No framework dependencies.
 */
final readonly class CorrelationId
{
    public function __construct(public string $value)
    {
        if (trim($value) === '') {
            throw new InvalidArgumentException('CorrelationId cannot be empty or whitespace.');
        }
    }

    public function equals(self $other): bool
    {
        return $this->value === $other->value;
    }

    public function toString(): string
    {
        return $this->value;
    }
}
