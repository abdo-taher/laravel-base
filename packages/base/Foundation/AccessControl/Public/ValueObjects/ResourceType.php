<?php

declare(strict_types=1);

namespace Base\Foundation\AccessControl\Public\ValueObjects;

use InvalidArgumentException;

/**
 * Identifies the type of resource being accessed.
 *
 * Represents a resource category (e.g. 'wallet', 'order', 'report'),
 * not a specific resource instance. Instance-level authorization
 * (e.g. "can user 5 edit order 42") is a business concern implemented
 * in business module policies that receive the resource identifier
 * through their own internal mechanisms.
 *
 * No framework dependencies. Instantiable without a container.
 */
final readonly class ResourceType
{
    public function __construct(public string $value)
    {
        if (trim($value) === '') {
            throw new InvalidArgumentException('ResourceType must be a non-empty string.');
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
