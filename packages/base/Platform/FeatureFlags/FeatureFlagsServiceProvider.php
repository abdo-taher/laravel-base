<?php

declare(strict_types=1);

namespace Base\Platform\FeatureFlags;

use Base\Platform\FeatureFlags\Application\DefaultFeatureFlagEvaluator;
use Base\Platform\FeatureFlags\Application\InMemoryFeatureFlagOverrideProvider;
use Base\Platform\FeatureFlags\Application\InMemoryFeatureFlagRegistry;
use Base\Platform\FeatureFlags\Public\Contracts\FeatureFlagEvaluator;
use Base\Platform\FeatureFlags\Public\Contracts\FeatureFlagOverrideProvider;
use Base\Platform\FeatureFlags\Public\Contracts\FeatureFlagRegistry;
use Illuminate\Support\ServiceProvider;

final class FeatureFlagsServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(FeatureFlagRegistry::class, InMemoryFeatureFlagRegistry::class);
        $this->app->singleton(FeatureFlagOverrideProvider::class, InMemoryFeatureFlagOverrideProvider::class);
        $this->app->bind(FeatureFlagEvaluator::class, DefaultFeatureFlagEvaluator::class);
    }
}
