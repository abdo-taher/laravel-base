<?php

declare(strict_types=1);

namespace Base\Foundation\Audit\Public\ValueObjects;

use InvalidArgumentException;

/**
 * Identifies the action that was audited.
 *
 * Business modules own their action names (e.g. 'module.action').
 * Audit Foundation does not define product-specific actions.
 *
 * No framework dependencies.
 */
final readonly class Action
{
    public function __construct(public string $value)
    {
        if (trim($value) === '') {
            throw new InvalidArgumentException('Action must be a non-empty string.');
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
