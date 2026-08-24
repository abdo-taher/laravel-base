<?php

declare(strict_types=1);

namespace Base\Foundation\SharedKernel\Public\ValueObjects;

use Base\Foundation\SharedKernel\Public\Exceptions\InvalidSemanticVersion;

/**
 * Canonical semantic version value object for the Base platform.
 *
 * Parses and validates a version string conforming to semver.org:
 *   MAJOR.MINOR.PATCH[-prerelease][+build]
 *
 * This is the single source of truth for semver validation across all
 * Base Foundation packages. Keeping it here prevents divergence between
 * Manifest validation and CapabilityRegistry constraint matching.
 *
 * No framework dependencies. Fully domain-neutral.
 */
final readonly class SemanticVersion
{
    /**
     * Semver 2.0 compliant regex.
     *
     * Captures:
     *   [1] MAJOR
     *   [2] MINOR
     *   [3] PATCH
     *   [4] pre-release identifier (without leading dash), optional
     *   [5] build metadata (without leading plus), optional
     */
    private const string PATTERN = '/^(0|[1-9]\d*)\.(0|[1-9]\d*)\.(0|[1-9]\d*)(?:-([\w][\w.-]*))?(?:\+([\w][\w.-]*))?$/';

    public int $major;

    public int $minor;

    public int $patch;

    public ?string $preRelease;

    public ?string $buildMetadata;

    /**
     * @throws InvalidSemanticVersion
     */
    public function __construct(public string $value)
    {
        if (preg_match(self::PATTERN, $value, $parts) !== 1) {
            throw InvalidSemanticVersion::forValue($value);
        }

        $this->major = (int) $parts[1];
        $this->minor = (int) $parts[2];
        $this->patch = (int) $parts[3];
        $this->preRelease = ($parts[4] ?? '') !== '' ? $parts[4] : null;
        $this->buildMetadata = ($parts[5] ?? '') !== '' ? $parts[5] : null;
    }

    /**
     * Named constructor — identical to `new SemanticVersion($value)`.
     *
     * @throws InvalidSemanticVersion
     */
    public static function from(string $value): self
    {
        return new self($value);
    }

    /**
     * Returns true when $value is a valid semantic version string.
     * Does not throw; safe to use for boolean validation.
     */
    public static function isValid(string $value): bool
    {
        return preg_match(self::PATTERN, $value) === 1;
    }

    /**
     * Compares this version to another using version_compare semantics.
     *
     * Returns:
     *   negative  — this < other
     *   0         — this == other
     *   positive  — this > other
     *
     * Build metadata is ignored during comparison per semver spec.
     */
    public function compareTo(self $other): int
    {
        return version_compare($this->value, $other->value);
    }

    public function equals(self $other): bool
    {
        return $this->compareTo($other) === 0;
    }

    public function isGreaterThan(self $other): bool
    {
        return $this->compareTo($other) > 0;
    }

    public function isLessThan(self $other): bool
    {
        return $this->compareTo($other) < 0;
    }

    public function toString(): string
    {
        return $this->value;
    }
}
