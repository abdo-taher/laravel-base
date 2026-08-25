<?php

declare(strict_types=1);

namespace Base\Platform\FeatureFlags\Public\Exceptions;

use Base\Platform\FeatureFlags\Public\ValueObjects\FeatureFlagKey;

final class DuplicateFeatureFlagDefinition extends FeatureFlagException
{
    public static function forKey(FeatureFlagKey $key): self
    {
        return new self("A feature flag definition is already registered for key: {$key->value}");
    }
}
