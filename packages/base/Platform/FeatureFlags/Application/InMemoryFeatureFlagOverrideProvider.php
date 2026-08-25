<?php

declare(strict_types=1);

namespace Base\Platform\FeatureFlags\Application;

use Base\Platform\FeatureFlags\Public\Contracts\FeatureFlagOverrideProvider;
use Base\Platform\FeatureFlags\Public\ValueObjects\FeatureFlagKey;
use InvalidArgumentException;

final readonly class InMemoryFeatureFlagOverrideProvider implements FeatureFlagOverrideProvider
{
    /**
     * @param  array<mixed, mixed>  $overrides
     */
    public function __construct(
        private array $overrides = []
    ) {
        foreach ($overrides as $key => $value) {
            if (! is_string($key)) {
                throw new InvalidArgumentException('Override keys must be strings.');
            }
            if (! is_bool($value)) {
                throw new InvalidArgumentException('Override values must be boolean.');
            }
        }
    }

    public function overrideFor(FeatureFlagKey $key): ?bool
    {
        return $this->overrides[$key->value] ?? null;
    }
}
