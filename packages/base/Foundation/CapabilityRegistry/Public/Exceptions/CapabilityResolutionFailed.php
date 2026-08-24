<?php

declare(strict_types=1);

namespace Base\Foundation\CapabilityRegistry\Public\Exceptions;

use RuntimeException;

final class CapabilityResolutionFailed extends RuntimeException
{
    public static function missing(string $name): self
    {
        return new self(sprintf('Required capability is unavailable: %s', $name));
    }

    public static function incompatible(string $name, string $constraint): self
    {
        return new self(sprintf(
            'No provider for capability %s satisfies version constraint %s',
            $name,
            $constraint,
        ));
    }

    public static function ambiguous(string $name, string $constraint): self
    {
        return new self(sprintf(
            'Capability %s has multiple providers satisfying %s and requires explicit strategy selection',
            $name,
            $constraint,
        ));
    }

    public static function strategyUnavailable(string $name, string $strategy): self
    {
        return new self(sprintf(
            'Capability %s has no provider for strategy %s',
            $name,
            $strategy,
        ));
    }
}
