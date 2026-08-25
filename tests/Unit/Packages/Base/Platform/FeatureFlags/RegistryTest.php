<?php

declare(strict_types=1);

namespace Tests\Unit\Packages\Base\Platform\FeatureFlags;

use Base\Platform\FeatureFlags\Application\InMemoryFeatureFlagRegistry;
use Base\Platform\FeatureFlags\Public\Exceptions\DuplicateFeatureFlagDefinition;
use Base\Platform\FeatureFlags\Public\Exceptions\UnknownFeatureFlag;
use Base\Platform\FeatureFlags\Public\ValueObjects\FeatureFlagDefinition;
use Base\Platform\FeatureFlags\Public\ValueObjects\FeatureFlagKey;
use PHPUnit\Framework\TestCase;

final class RegistryTest extends TestCase
{
    public function test_register_and_retrieve_definition(): void
    {
        $registry = new InMemoryFeatureFlagRegistry;
        $key = new FeatureFlagKey('test.flag');
        $def = new FeatureFlagDefinition($key, true);

        $registry->register($def);

        $retrieved = $registry->get($key);
        $this->assertSame($def, $retrieved);
    }

    public function test_duplicate_registration_throws_duplicate_exception(): void
    {
        $registry = new InMemoryFeatureFlagRegistry;
        $key = new FeatureFlagKey('test.flag');
        $def1 = new FeatureFlagDefinition($key, true);
        $def2 = new FeatureFlagDefinition($key, false);

        $registry->register($def1);

        $this->expectException(DuplicateFeatureFlagDefinition::class);
        $registry->register($def2);
    }

    public function test_unknown_lookup_throws_unknown_exception(): void
    {
        $registry = new InMemoryFeatureFlagRegistry;
        $key = new FeatureFlagKey('test.flag');

        $this->expectException(UnknownFeatureFlag::class);
        $registry->get($key);
    }
}
