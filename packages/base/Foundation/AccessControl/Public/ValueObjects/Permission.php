<?php

declare(strict_types=1);

namespace Base\Foundation\AccessControl\Public\ValueObjects;

use InvalidArgumentException;

/**
 * A named permission.
 *
 * Represents an authorization permission as a non-empty string value.
 * Business modules own their permission names (e.g. 'module.action',
 * 'feature.create'). AccessControl owns the Permission type and the
 * evaluation infrastructure.
 *
 * Convention: dot-notated namespacing (e.g. 'module.action') is
 * recommended but not enforced by this value object.
 *
 * No framework dependencies. Instantiable without a container.
 */
final readonly class Permission
{
    public function __construct(public string $value)
    {
        if (trim($value) === '') {
            throw new InvalidArgumentException('Permission must be a non-empty string.');
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
