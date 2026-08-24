<?php

declare(strict_types=1);

namespace Base\Foundation\CapabilityRegistry\Public\ValueObjects;

use Base\Foundation\CapabilityRegistry\Public\Exceptions\InvalidCapabilityDefinition;
use Base\Foundation\SharedKernel\Public\Exceptions\InvalidSemanticVersion;
use Base\Foundation\SharedKernel\Public\ValueObjects\SemanticVersion;

/**
 * Semantic version for a capability provider.
 *
 * Delegates parsing and validation to the SharedKernel SemanticVersion
 * primitive, which is the single source of truth for semver across all
 * Base Foundation packages.
 *
 * Public API is unchanged from B2: major, minor, patch, compareTo().
 */
final readonly class CapabilityVersion
{
    public int $major;

    public int $minor;

    public int $patch;

    public function __construct(public string $value)
    {
        try {
            $parsed = SemanticVersion::from($value);
        } catch (InvalidSemanticVersion) {
            throw new InvalidCapabilityDefinition(sprintf('Invalid capability version: %s', $value));
        }

        $this->major = $parsed->major;
        $this->minor = $parsed->minor;
        $this->patch = $parsed->patch;
    }

    public function compareTo(self $other): int
    {
        return version_compare($this->value, $other->value);
    }
}
