<?php

declare(strict_types=1);

namespace Base\Platform\FeatureFlags\Public\ValueObjects;

final readonly class FeatureFlagDefinition
{
    public function __construct(
        public FeatureFlagKey $key,
        public bool $defaultState
    ) {}
}
