<?php

declare(strict_types=1);

namespace Base\Platform\FeatureFlags\Public\Contracts;

use Base\Platform\FeatureFlags\Public\ValueObjects\FeatureFlagKey;

interface FeatureFlagOverrideProvider
{
    /**
     * Return null if no override is configured for the key.
     */
    public function overrideFor(FeatureFlagKey $key): ?bool;
}
