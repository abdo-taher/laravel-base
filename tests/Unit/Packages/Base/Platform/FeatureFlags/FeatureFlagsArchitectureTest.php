<?php

declare(strict_types=1);

namespace Tests\Unit\Packages\Base\Platform\FeatureFlags;

use Base\Platform\FeatureFlags\Public\Contracts\FeatureFlagEvaluator;
use Base\Platform\FeatureFlags\Public\Contracts\FeatureFlagOverrideProvider;
use Base\Platform\FeatureFlags\Public\Contracts\FeatureFlagRegistry;
use Base\Platform\FeatureFlags\Public\Exceptions\DuplicateFeatureFlagDefinition;
use Base\Platform\FeatureFlags\Public\Exceptions\FeatureFlagException;
use Base\Platform\FeatureFlags\Public\Exceptions\InvalidFeatureFlagKey;
use Base\Platform\FeatureFlags\Public\Exceptions\UnknownFeatureFlag;
use Base\Platform\FeatureFlags\Public\ValueObjects\FeatureFlagDefinition;
use Base\Platform\FeatureFlags\Public\ValueObjects\FeatureFlagKey;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

final class FeatureFlagsArchitectureTest extends TestCase
{
    public function test_public_contracts_have_no_framework_dependencies(): void
    {
        $contracts = [
            FeatureFlagRegistry::class,
            FeatureFlagOverrideProvider::class,
            FeatureFlagEvaluator::class,
            InvalidFeatureFlagKey::class,
            DuplicateFeatureFlagDefinition::class,
            FeatureFlagException::class,
            UnknownFeatureFlag::class,
            FeatureFlagKey::class,
            FeatureFlagDefinition::class,
        ];

        foreach ($contracts as $contract) {
            $reflection = new ReflectionClass($contract);
            $fileName = $reflection->getFileName();

            if ($fileName === false) {
                $this->fail("Could not find file for contract {$contract}");
            }

            $content = file_get_contents($fileName);
            if ($content === false) {
                $this->fail("Could not read file for contract {$contract}");
            }

            $this->assertStringNotContainsString(
                'Illuminate\\',
                $content,
                "Contract {$contract} must not depend on Laravel framework."
            );

            $this->assertStringNotContainsString(
                'DB',
                $content,
                "Contract {$contract} must not depend on Database."
            );

            $this->assertStringNotContainsString(
                'Redis',
                $content,
                "Contract {$contract} must not depend on Redis."
            );
        }
    }
}
