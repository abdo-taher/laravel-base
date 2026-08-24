<?php

declare(strict_types=1);

namespace Base\Foundation\ModuleManager\Public\ValueObjects;

/**
 * Immutable identifier for a discovered package or module.
 *
 * Equality is by name. A name must be unique within a resolved boot plan.
 */
final readonly class ModuleIdentifier
{
    public function __construct(
        public string $name,
        public string $category,
    ) {}

    public function equals(self $other): bool
    {
        return $this->name === $other->name;
    }

    public function toString(): string
    {
        return $this->category.'/'.$this->name;
    }
}
