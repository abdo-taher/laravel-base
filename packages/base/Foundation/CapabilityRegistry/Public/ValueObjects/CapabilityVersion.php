<?php

declare(strict_types=1);

namespace Base\Foundation\CapabilityRegistry\Public\ValueObjects;

use Base\Foundation\CapabilityRegistry\Public\Exceptions\InvalidCapabilityDefinition;

final readonly class CapabilityVersion
{
    public int $major;

    public int $minor;

    public int $patch;

    public function __construct(public string $value)
    {
        if (preg_match(
            '/^(0|[1-9]\d*)\.(0|[1-9]\d*)\.(0|[1-9]\d*)(?:-[0-9A-Za-z.-]+)?(?:\+[0-9A-Za-z.-]+)?$/',
            $value,
            $parts,
        ) !== 1) {
            throw new InvalidCapabilityDefinition(sprintf('Invalid capability version: %s', $value));
        }

        $this->major = (int) $parts[1];
        $this->minor = (int) $parts[2];
        $this->patch = (int) $parts[3];
    }

    public function compareTo(self $other): int
    {
        return version_compare($this->value, $other->value);
    }
}
