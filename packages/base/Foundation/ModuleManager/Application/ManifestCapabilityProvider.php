<?php

declare(strict_types=1);

namespace Base\Foundation\ModuleManager\Application;

use Base\Foundation\CapabilityRegistry\Public\Contracts\CapabilityContract;
use Base\Foundation\CapabilityRegistry\Public\Contracts\CapabilityProviderContract;
use Base\Foundation\Manifest\Public\ValueObjects\Manifest;

/**
 * Adapts a manifest's capability declaration into a CapabilityProviderContract.
 *
 * This is a structural provider that carries manifest identity metadata.
 * At this foundation stage no concrete service is instantiated — the
 * manifest declaration is the capability contract.
 */
final readonly class ManifestCapabilityProvider implements CapabilityProviderContract
{
    public function __construct(private Manifest $manifest) {}

    public function provide(): CapabilityContract
    {
        return new ManifestCapabilityToken($this->manifest->name, $this->manifest->version);
    }
}
