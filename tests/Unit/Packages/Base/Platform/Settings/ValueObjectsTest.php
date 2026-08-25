<?php

declare(strict_types=1);

namespace Tests\Unit\Packages\Base\Platform\Settings;

use Base\Platform\Settings\Public\ValueObjects\SettingDefinition;
use Base\Platform\Settings\Public\ValueObjects\SettingKey;
use Base\Platform\Settings\Public\ValueObjects\SettingType;
use PHPUnit\Framework\TestCase;

final class ValueObjectsTest extends TestCase
{
    public function test_setting_key_accepts_valid_string(): void
    {
        $key = new SettingKey('app.name-123_test');
        $this->assertSame('app.name-123_test', $key->value);
        $this->assertSame('app.name-123_test', (string) $key);
    }

    public function test_setting_key_rejects_empty_string(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Setting key cannot be empty.');
        new SettingKey('   ');
    }

    public function test_setting_key_rejects_invalid_characters(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Setting key can only contain alphanumeric characters, dots, dashes, and underscores.');
        new SettingKey('invalid key @!');
    }

    public function test_setting_definition_validates_default_type(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage("Value for setting 'my.key' must be of type string, got integer.");

        new SettingDefinition(
            key: new SettingKey('my.key'),
            type: SettingType::STRING,
            default: 123
        );
    }

    public function test_setting_definition_validates_type_dynamically(): void
    {
        $def = new SettingDefinition(
            key: new SettingKey('my.int'),
            type: SettingType::INTEGER
        );

        $def->validateType(42); // Should pass

        $this->expectException(\InvalidArgumentException::class);
        $def->validateType('42');
    }

    public function test_setting_definition_accepts_int_for_float(): void
    {
        $def = new SettingDefinition(
            key: new SettingKey('my.float'),
            type: SettingType::FLOAT
        );

        $def->validateType(42); // Int for float is valid
        $def->validateType(42.5); // Float for float is valid
        $this->expectNotToPerformAssertions();
    }
}
