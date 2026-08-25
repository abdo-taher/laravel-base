<?php

declare(strict_types=1);

namespace Base\Platform\FeatureFlags\Public\Exceptions;

final class InvalidFeatureFlagKey extends FeatureFlagException
{
    public static function emptyOrWhitespace(): self
    {
        return new self('Feature flag key cannot be empty or whitespace.');
    }

    public static function invalidCharacters(): self
    {
        return new self('Feature flag key contains invalid characters. Only lowercase alphanumeric, dashes, and dots are allowed.');
    }
}
