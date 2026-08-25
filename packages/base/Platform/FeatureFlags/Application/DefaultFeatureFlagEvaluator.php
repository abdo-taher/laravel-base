<?php

declare(strict_types=1);

namespace Base\Platform\FeatureFlags\Application;

use Base\Platform\FeatureFlags\Public\Contracts\FeatureFlagEvaluator;
use Base\Platform\FeatureFlags\Public\Contracts\FeatureFlagOverrideProvider;
use Base\Platform\FeatureFlags\Public\Contracts\FeatureFlagRegistry;
use Base\Platform\FeatureFlags\Public\ValueObjects\FeatureFlagKey;

final readonly class DefaultFeatureFlagEvaluator implements FeatureFlagEvaluator
{
    public function __construct(
        private FeatureFlagRegistry $registry,
        private FeatureFlagOverrideProvider $overrideProvider
    ) {}

    public function isEnabled(FeatureFlagKey $flag): bool
    {
        $definition = $this->registry->get($flag);

        $override = $this->overrideProvider->overrideFor($flag);

        if ($override !== null) {
            return $override;
        }

        return $definition->defaultState;
    }
}
