<?php

declare(strict_types=1);

namespace Tests\Unit\Packages\Base\Platform\FeatureFlags;

use Base\Platform\FeatureFlags\FeatureFlagsServiceProvider;
use PHPUnit\Framework\TestCase;

final class NamespaceTest extends TestCase
{
    public function test_feature_flags_service_provider_class_exists(): void
    {
        $this->assertTrue(class_exists(FeatureFlagsServiceProvider::class));
    }
}
