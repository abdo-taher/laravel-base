<?php

declare(strict_types=1);

namespace Base\Foundation\CapabilityRegistry\Public\ValueObjects;

use Base\Foundation\CapabilityRegistry\Public\Exceptions\InvalidCapabilityDefinition;

final readonly class CapabilityName
{
    public function __construct(public string $value)
    {
        if (preg_match('/^[a-z][a-z0-9]*(?:[.-][a-z0-9]+)*$/', $value) !== 1) {
            throw new InvalidCapabilityDefinition(sprintf('Invalid capability name: %s', $value));
        }
    }
}
