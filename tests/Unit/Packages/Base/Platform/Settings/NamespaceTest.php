<?php

declare(strict_types=1);

namespace Tests\Unit\Packages\Base\Platform\Settings;

use Base\Platform\Settings\SettingsServiceProvider;
use PHPUnit\Framework\TestCase;

final class NamespaceTest extends TestCase
{
    public function test_settings_service_provider_class_exists(): void
    {
        $this->assertTrue(class_exists(SettingsServiceProvider::class));
    }
}
