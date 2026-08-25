<?php

declare(strict_types=1);

namespace Base\Foundation\Identity\Public\ValueObjects;

/**
 * An authenticated identity at a single point in time.
 *
 * Carries only what is universally true of every authenticated actor:
 *   - a typed identifier
 *   - a principal type
 *
 * No roles. No permissions. No profile fields. Those belong in
 * AccessControl and project-owned extension contributions respectively.
 *
 * No framework dependencies. Instantiable without a container.
 */
final readonly class Principal
{
    public function __construct(
        public PrincipalId $id,
        public PrincipalType $type,
    ) {}

    public function equals(self $other): bool
    {
        return $this->id->equals($other->id) && $this->type->equals($other->type);
    }
}
