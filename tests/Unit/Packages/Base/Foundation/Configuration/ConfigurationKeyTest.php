<?php

declare(strict_types=1);

namespace Tests\Unit\Packages\Base\Foundation\Configuration;

use Base\Foundation\Configuration\Public\ValueObjects\ConfigurationDefinition;
use Base\Foundation\Configuration\Public\ValueObjects\ConfigurationKey;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class ConfigurationKeyTest extends TestCase
{
    // ── Positive ─────────────────────────────────────────────────────────────

    public function test_required_key_exposes_path_and_type(): void
    {
        $key = new ConfigurationKey('manifest.cache_ttl', ConfigurationKey::TYPE_INT);

        self::assertSame('manifest.cache_ttl', $key->path);
        self::assertSame(ConfigurationKey::TYPE_INT, $key->type);
        self::assertTrue($key->required);
        self::assertFalse($key->isOptional());
    }

    public function test_optional_key_with_default(): void
    {
        $key = new ConfigurationKey(
            path: 'pkg.timeout',
            type: ConfigurationKey::TYPE_INT,
            required: false,
            default: 30,
        );

        self::assertFalse($key->required);
        self::assertTrue($key->isOptional());
        self::assertTrue($key->hasDefault());
        self::assertSame(30, $key->default);
    }

    public function test_optional_key_without_default(): void
    {
        $key = new ConfigurationKey('pkg.name', ConfigurationKey::TYPE_STRING, required: false);

        self::assertTrue($key->isOptional());
        self::assertFalse($key->hasDefault());
        self::assertNull($key->default);
    }

    /**
     * @dataProvider validTypeProvider
     */
    #[DataProvider('validTypeProvider')]
    public function test_all_type_constants_are_accepted(string $type): void
    {
        $key = new ConfigurationKey('some.key', $type);

        self::assertSame($type, $key->type);
    }

    /** @return array<string, array{string}> */
    public static function validTypeProvider(): array
    {
        return [
            'string' => [ConfigurationKey::TYPE_STRING],
            'int' => [ConfigurationKey::TYPE_INT],
            'float' => [ConfigurationKey::TYPE_FLOAT],
            'bool' => [ConfigurationKey::TYPE_BOOL],
            'array' => [ConfigurationKey::TYPE_ARRAY],
        ];
    }

    // ── Negative ─────────────────────────────────────────────────────────────

    public function test_empty_path_is_rejected(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('non-empty');

        new ConfigurationKey('', ConfigurationKey::TYPE_STRING);
    }

    public function test_whitespace_only_path_is_rejected(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new ConfigurationKey('   ', ConfigurationKey::TYPE_STRING);
    }

    public function test_invalid_type_is_rejected(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('object');

        new ConfigurationKey('some.key', 'object');
    }

    // ── Immutability ─────────────────────────────────────────────────────────

    public function test_configuration_key_is_readonly(): void
    {
        $reflection = new \ReflectionClass(ConfigurationKey::class);

        self::assertTrue($reflection->isReadOnly());
    }

    // ── ConfigurationDefinition ───────────────────────────────────────────────

    public function test_configuration_definition_exposes_key_and_value(): void
    {
        $key = new ConfigurationKey('pkg.limit', ConfigurationKey::TYPE_INT);
        $def = new ConfigurationDefinition($key, 100);

        self::assertSame($key, $def->key);
        self::assertSame(100, $def->value);
    }

    public function test_configuration_definition_is_readonly(): void
    {
        $reflection = new \ReflectionClass(ConfigurationDefinition::class);

        self::assertTrue($reflection->isReadOnly());
    }
}
