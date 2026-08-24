<?php

declare(strict_types=1);

namespace Base\Foundation\ModuleManager\Application;

use Base\Foundation\CapabilityRegistry\Public\Contracts\CapabilityContract;

/**
 * Structural capability token produced by ManifestCapabilityProvider.
 *
 * Carries the owning manifest name and version as metadata.
 * No service instantiation occurs at this foundation stage.
 */
final readonly class ManifestCapabilityToken implements CapabilityContract
{
    public function __construct(
        public string $ownerName,
        public string $ownerVersion,
    ) {}
}
