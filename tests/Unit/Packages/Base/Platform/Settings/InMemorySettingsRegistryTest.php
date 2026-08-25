<?php

declare(strict_types=1);

namespace Tests\Unit\Packages\Base\Platform\Settings;

use Base\Platform\Settings\Application\InMemorySettingsRegistry;
use Base\Platform\Settings\Public\ValueObjects\SettingDefinition;
use Base\Platform\Settings\Public\ValueObjects\SettingKey;
use Base\Platform\Settings\Public\ValueObjects\SettingType;
use PHPUnit\Framework\TestCase;

final class InMemorySettingsRegistryTest extends TestCase
{
    public function test_it_registers_and_retrieves_definitions(): void
    {
        $registry = new InMemorySettingsRegistry;

        $def = new SettingDefinition(
            key: new SettingKey('test.key'),
            type: SettingType::STRING
        );

        $registry->register($def);

        $this->assertSame($def, $registry->getDefinition('test.key'));
        $this->assertSame($def, $registry->getDefinition(new SettingKey('test.key')));
        $this->assertNull($registry->getDefinition('unknown.key'));

        $all = $registry->all();
        $this->assertCount(1, $all);
        $this->assertArrayHasKey('test.key', $all);
    }
}
