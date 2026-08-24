<?php

declare(strict_types=1);

namespace Base\Foundation\CapabilityRegistry\Public\Contracts;

use Base\Foundation\CapabilityRegistry\Public\ValueObjects\CapabilityName;
use Base\Foundation\CapabilityRegistry\Public\ValueObjects\CapabilityProviderDefinition;
use Base\Foundation\CapabilityRegistry\Public\ValueObjects\CapabilityResolutionResult;

interface CapabilityResolver
{
    public function register(CapabilityProviderDefinition $definition): void;

    public function resolve(
        CapabilityName $name,
        string $versionConstraint,
        bool $required = true,
        ?string $strategy = null,
    ): CapabilityResolutionResult;
}
