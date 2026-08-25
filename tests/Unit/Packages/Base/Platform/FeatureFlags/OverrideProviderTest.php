<?php

declare(strict_types=1);

namespace Tests\Unit\Packages\Base\Platform\FeatureFlags;

use Base\Platform\FeatureFlags\Application\InMemoryFeatureFlagOverrideProvider;
use Base\Platform\FeatureFlags\Public\ValueObjects\FeatureFlagKey;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

final class OverrideProviderTest extends TestCase
{
    public function test_no_override_returns_null(): void
    {
        $provider = new InMemoryFeatureFlagOverrideProvider([]);
        $key = new FeatureFlagKey('test.flag');

        $this->assertNull($provider->overrideFor($key));
    }

    public function test_explicit_true(): void
    {
        $provider = new InMemoryFeatureFlagOverrideProvider(['test.flag' => true]);
        $key = new FeatureFlagKey('test.flag');

        $this->assertTrue($provider->overrideFor($key));
    }

    public function test_explicit_false(): void
    {
        $provider = new InMemoryFeatureFlagOverrideProvider(['test.flag' => false]);
        $key = new FeatureFlagKey('test.flag');

        $this->assertFalse($provider->overrideFor($key));
    }

    public function test_invalid_constructor_value_rejected(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('boolean');
        $provider = new InMemoryFeatureFlagOverrideProvider(['test.flag' => 'true']);
    }

    public function test_invalid_constructor_key_rejected(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('strings');
        // This simulates a non-string key.
        $provider = new InMemoryFeatureFlagOverrideProvider([123 => true]);
    }
}
