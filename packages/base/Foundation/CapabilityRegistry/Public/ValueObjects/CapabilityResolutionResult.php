<?php

declare(strict_types=1);

namespace Base\Foundation\CapabilityRegistry\Public\ValueObjects;

final readonly class CapabilityResolutionResult
{
    public function __construct(
        public CapabilityName $name,
        public string $versionConstraint,
        public bool $required,
        public ?CapabilityProviderDefinition $provider,
    ) {}

    public function isResolved(): bool
    {
        return $this->provider !== null;
    }
}
