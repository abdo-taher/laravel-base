<?php

declare(strict_types=1);

namespace Base\Platform\FeatureFlags\Public\Exceptions;

use Base\Platform\FeatureFlags\Public\ValueObjects\FeatureFlagKey;

final class UnknownFeatureFlag extends FeatureFlagException
{
    public static function forUnregisteredKey(FeatureFlagKey $key): self
    {
        return new self("Unknown feature flag key requested: {$key->value}");
    }
}
