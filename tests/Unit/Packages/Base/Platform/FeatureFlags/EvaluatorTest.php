<?php

declare(strict_types=1);

namespace Tests\Unit\Packages\Base\Platform\FeatureFlags;

use Base\Platform\FeatureFlags\Application\DefaultFeatureFlagEvaluator;
use Base\Platform\FeatureFlags\Application\InMemoryFeatureFlagOverrideProvider;
use Base\Platform\FeatureFlags\Application\InMemoryFeatureFlagRegistry;
use Base\Platform\FeatureFlags\Public\Exceptions\UnknownFeatureFlag;
use Base\Platform\FeatureFlags\Public\ValueObjects\FeatureFlagDefinition;
use Base\Platform\FeatureFlags\Public\ValueObjects\FeatureFlagKey;
use PHPUnit\Framework\TestCase;

final class EvaluatorTest extends TestCase
{
    private InMemoryFeatureFlagRegistry $registry;

    protected function setUp(): void
    {
        $this->registry = new InMemoryFeatureFlagRegistry;
    }

    public function test_default_false(): void
    {
        $key = new FeatureFlagKey('test.flag');
        $this->registry->register(new FeatureFlagDefinition($key, false));

        $evaluator = new DefaultFeatureFlagEvaluator($this->registry, new InMemoryFeatureFlagOverrideProvider([]));

        $this->assertFalse($evaluator->isEnabled($key));
    }

    public function test_default_true(): void
    {
        $key = new FeatureFlagKey('test.flag');
        $this->registry->register(new FeatureFlagDefinition($key, true));

        $evaluator = new DefaultFeatureFlagEvaluator($this->registry, new InMemoryFeatureFlagOverrideProvider([]));

        $this->assertTrue($evaluator->isEnabled($key));
    }

    public function test_false_overridden_to_true(): void
    {
        $key = new FeatureFlagKey('test.flag');
        $this->registry->register(new FeatureFlagDefinition($key, false));

        $evaluator = new DefaultFeatureFlagEvaluator(
            $this->registry,
            new InMemoryFeatureFlagOverrideProvider(['test.flag' => true])
        );

        $this->assertTrue($evaluator->isEnabled($key));
    }

    public function test_true_overridden_to_false(): void
    {
        $key = new FeatureFlagKey('test.flag');
        $this->registry->register(new FeatureFlagDefinition($key, true));

        $evaluator = new DefaultFeatureFlagEvaluator(
            $this->registry,
            new InMemoryFeatureFlagOverrideProvider(['test.flag' => false])
        );

        $this->assertFalse($evaluator->isEnabled($key));
    }

    public function test_unknown_key_throws_unknown_exception(): void
    {
        $key = new FeatureFlagKey('test.flag');
        $evaluator = new DefaultFeatureFlagEvaluator($this->registry, new InMemoryFeatureFlagOverrideProvider([]));

        $this->expectException(UnknownFeatureFlag::class);
        $evaluator->isEnabled($key);
    }

    public function test_evaluator_does_not_silently_convert_unknown_to_false(): void
    {
        $key = new FeatureFlagKey('test.typo');
        // Register 'test.flag'
        $this->registry->register(new FeatureFlagDefinition(new FeatureFlagKey('test.flag'), false));

        $evaluator = new DefaultFeatureFlagEvaluator($this->registry, new InMemoryFeatureFlagOverrideProvider([]));

        $this->expectException(UnknownFeatureFlag::class);
        $evaluator->isEnabled($key);
    }

    public function test_deterministic_repeated_evaluation(): void
    {
        $key = new FeatureFlagKey('test.flag');
        $this->registry->register(new FeatureFlagDefinition($key, false));

        $evaluator = new DefaultFeatureFlagEvaluator(
            $this->registry,
            new InMemoryFeatureFlagOverrideProvider(['test.flag' => true])
        );

        $this->assertTrue($evaluator->isEnabled($key));
        $this->assertTrue($evaluator->isEnabled($key));
        $this->assertTrue($evaluator->isEnabled($key));
    }

    public function test_unknown_override_key_does_not_make_flag_valid(): void
    {
        $key = new FeatureFlagKey('unknown.flag');

        $evaluator = new DefaultFeatureFlagEvaluator(
            $this->registry,
            new InMemoryFeatureFlagOverrideProvider(['unknown.flag' => true])
        );

        $this->expectException(UnknownFeatureFlag::class);
        $evaluator->isEnabled($key);
    }
}
