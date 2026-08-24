<?php

declare(strict_types=1);

namespace Base\Foundation\ModuleManager\Public\Exceptions;

use RuntimeException;

/**
 * Thrown when a boot plan cannot be produced from the discovered manifests.
 *
 * Fails closed: cycles, missing dependencies, duplicate identities, and
 * resolution ambiguities all produce this exception rather than a
 * degraded partial plan.
 */
final class ModuleBootPlanFailed extends RuntimeException
{
    public static function fromResolutionErrors(string $detail): self
    {
        return new self(sprintf('Module boot plan failed — dependency resolution error: %s', $detail));
    }

    public static function duplicateIdentity(string $name): self
    {
        return new self(sprintf('Module boot plan failed — duplicate module identity: %s', $name));
    }
}
