<?php

declare(strict_types=1);

namespace Base\Platform\FeatureFlags\Public\Contracts;

use Base\Platform\FeatureFlags\Public\Exceptions\UnknownFeatureFlag;
use Base\Platform\FeatureFlags\Public\ValueObjects\FeatureFlagKey;

interface FeatureFlagEvaluator
{
    /**
     * @throws UnknownFeatureFlag
     */
    public function isEnabled(FeatureFlagKey $flag): bool;
}
