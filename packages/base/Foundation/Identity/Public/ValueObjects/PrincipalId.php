<?php

declare(strict_types=1);

namespace Base\Foundation\Identity\Public\ValueObjects;

use InvalidArgumentException;

/**
 * Typed identity identifier.
 *
 * A non-empty string that uniquely identifies a principal within the
 * system. The format (UUID, integer string, ULID, etc.) is not
 * enforced here — the source of truth is the underlying persistence.
 *
 * Domain-owned. Not promoted to SharedKernel: identifiers are
 * inherently domain-specific and carry different semantics in each
 * owning package.
 *
 * No framework dependencies.
 */
final readonly class PrincipalId
{
    public function __construct(public string $value)
    {
        if (trim($value) === '') {
            throw new InvalidArgumentException('PrincipalId must be a non-empty string.');
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
