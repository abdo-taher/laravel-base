<?php

declare(strict_types=1);

namespace Base\Platform\FeatureFlags\Public\ValueObjects;

use Base\Platform\FeatureFlags\Public\Exceptions\InvalidFeatureFlagKey;

final readonly class FeatureFlagKey
{
    public string $value;

    public function __construct(string $value)
    {
        $trimmed = trim($value);

        if ($trimmed === '') {
            throw InvalidFeatureFlagKey::emptyOrWhitespace();
        }

        // Canonical Base identifier rule: lowercase alphanumeric, dashes, and dots.
        if (preg_match('/^[a-z0-9\-\.]+$/', $trimmed) !== 1) {
            throw InvalidFeatureFlagKey::invalidCharacters();
        }

        $this->value = $trimmed;
    }
}
