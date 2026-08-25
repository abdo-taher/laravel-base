<?php

declare(strict_types=1);

namespace Base\Platform\FeatureFlags\Public\Contracts;

use Base\Platform\FeatureFlags\Public\Exceptions\DuplicateFeatureFlagDefinition;
use Base\Platform\FeatureFlags\Public\Exceptions\UnknownFeatureFlag;
use Base\Platform\FeatureFlags\Public\ValueObjects\FeatureFlagDefinition;
use Base\Platform\FeatureFlags\Public\ValueObjects\FeatureFlagKey;

interface FeatureFlagRegistry
{
    /**
     * @throws DuplicateFeatureFlagDefinition
     */
    public function register(FeatureFlagDefinition $definition): void;

    /**
     * @throws UnknownFeatureFlag
     */
    public function get(FeatureFlagKey $key): FeatureFlagDefinition;
}
