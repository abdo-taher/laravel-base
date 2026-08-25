<?php

declare(strict_types=1);

namespace Tests\Unit\Packages\Base\Platform\FeatureFlags;

use Base\Platform\FeatureFlags\Public\Exceptions\InvalidFeatureFlagKey;
use Base\Platform\FeatureFlags\Public\ValueObjects\FeatureFlagDefinition;
use Base\Platform\FeatureFlags\Public\ValueObjects\FeatureFlagKey;
use PHPUnit\Framework\TestCase;

final class ValueObjectsTest extends TestCase
{
    public function test_valid_feature_flag_keys(): void
    {
        $validKeys = [
            'feature.enabled',
            'feature-enabled',
            'feature1',
            'feature.new-checkout.123',
        ];

        foreach ($validKeys as $keyString) {
            $key = new FeatureFlagKey($keyString);
            $this->assertSame($keyString, $key->value);
        }
    }

    public function test_empty_key_rejected(): void
    {
        $this->expectException(InvalidFeatureFlagKey::class);
        $this->expectExceptionMessage('empty or whitespace');
        new FeatureFlagKey('');
    }

    public function test_whitespace_key_rejected(): void
    {
        $this->expectException(InvalidFeatureFlagKey::class);
        $this->expectExceptionMessage('empty or whitespace');
        new FeatureFlagKey('   ');
    }

    public function test_invalid_key_format_rejected(): void
    {
        $invalidKeys = [
            'FEATURE.ENABLED', // uppercase
            'feature_enabled', // underscore
            'feature/enabled', // slash
            'feature\\enabled', // backslash
            'feature enabled', // space inside
            "feature\nenabled", // control character
            'feature@enabled', // special character
        ];

        foreach ($invalidKeys as $invalidKey) {
            try {
                new FeatureFlagKey($invalidKey);
                $this->fail("Expected InvalidFeatureFlagKey for '{$invalidKey}'");
            } catch (InvalidFeatureFlagKey $e) {
                $this->assertStringContainsString('lowercase alphanumeric, dashes, and dots', $e->getMessage());
            }
        }
    }

    public function test_feature_flag_definition_is_readonly(): void
    {
        $key = new FeatureFlagKey('test.flag');
        $def = new FeatureFlagDefinition($key, true);

        $this->assertSame($key, $def->key);
        $this->assertTrue($def->defaultState);
    }

    public function test_feature_flag_definition_default_false(): void
    {
        $key = new FeatureFlagKey('test.flag');
        $def = new FeatureFlagDefinition($key, false);

        $this->assertFalse($def->defaultState);
    }
}
