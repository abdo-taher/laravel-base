<?php

declare(strict_types=1);

namespace Base\Foundation\CapabilityRegistry\Public\ValueObjects;

use Base\Foundation\CapabilityRegistry\Public\Contracts\CapabilityProviderContract;
use Base\Foundation\CapabilityRegistry\Public\Exceptions\InvalidCapabilityDefinition;

final readonly class CapabilityProviderDefinition
{
    /** @param array<string, mixed> $metadata */
    public function __construct(
        public CapabilityName $name,
        public CapabilityVersion $version,
        public CapabilityProviderContract $provider,
        public array $metadata = [],
        public int $priority = 0,
        public ?string $strategy = null,
    ) {
        if ($strategy !== null && trim($strategy) === '') {
            throw new InvalidCapabilityDefinition('Provider strategy must be null or a non-empty string.');
        }

        foreach (array_keys($metadata) as $key) {
            if (trim($key) === '') {
                throw new InvalidCapabilityDefinition('Provider metadata keys must be non-empty strings.');
            }
        }
    }
}
