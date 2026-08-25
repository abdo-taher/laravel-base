<?php

declare(strict_types=1);

namespace Base\Platform\FeatureFlags\Application;

use Base\Platform\FeatureFlags\Public\Contracts\FeatureFlagRegistry;
use Base\Platform\FeatureFlags\Public\Exceptions\DuplicateFeatureFlagDefinition;
use Base\Platform\FeatureFlags\Public\Exceptions\UnknownFeatureFlag;
use Base\Platform\FeatureFlags\Public\ValueObjects\FeatureFlagDefinition;
use Base\Platform\FeatureFlags\Public\ValueObjects\FeatureFlagKey;

final class InMemoryFeatureFlagRegistry implements FeatureFlagRegistry
{
    /** @var array<string, FeatureFlagDefinition> */
    private array $definitions = [];

    public function register(FeatureFlagDefinition $definition): void
    {
        $keyString = $definition->key->value;

        if (array_key_exists($keyString, $this->definitions)) {
            throw DuplicateFeatureFlagDefinition::forKey($definition->key);
        }

        $this->definitions[$keyString] = $definition;
    }

    public function get(FeatureFlagKey $key): FeatureFlagDefinition
    {
        if (! array_key_exists($key->value, $this->definitions)) {
            throw UnknownFeatureFlag::forUnregisteredKey($key);
        }

        return $this->definitions[$key->value];
    }
}
