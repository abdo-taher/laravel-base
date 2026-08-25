<?php

declare(strict_types=1);

namespace Tests\Unit\Packages\Base\Foundation\Configuration;

use Base\Foundation\Configuration\Public\Contracts\ConfigurationRepository;
use Base\Foundation\Configuration\Public\Contracts\ConfigurationSource;
use Base\Foundation\Configuration\Public\Contracts\ConfigurationSourceContributor;
use Base\Foundation\Configuration\Public\Exceptions\ConfigurationKeyMissing;
use Base\Foundation\Configuration\Public\Exceptions\ConfigurationTypeMismatch;
use Base\Foundation\Configuration\Public\ValueObjects\ConfigurationDefinition;
use Base\Foundation\Configuration\Public\ValueObjects\ConfigurationKey;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/**
 * Architecture tests for the Configuration Foundation package.
 *
 * Proves:
 * - Public contracts contain no Laravel/Illuminate dependency.
 * - Public contracts contain no product-specific configuration vocabulary.
 * - Key and definition value objects are readonly.
 */
final class ConfigurationArchitectureTest extends TestCase
{
    /** @return array<string, array{class-string}> */
    public static function publicContractClassProvider(): array
    {
        return [
            'ConfigurationRepository' => [ConfigurationRepository::class],
            'ConfigurationSource' => [ConfigurationSource::class],
            'ConfigurationSourceContributor' => [ConfigurationSourceContributor::class],
            'ConfigurationKey' => [ConfigurationKey::class],
            'ConfigurationDefinition' => [ConfigurationDefinition::class],
            'ConfigurationKeyMissing' => [ConfigurationKeyMissing::class],
            'ConfigurationTypeMismatch' => [ConfigurationTypeMismatch::class],
        ];
    }

    /**
     * @param  class-string  $class
     */
    #[DataProvider('publicContractClassProvider')]
    public function test_public_contract_has_no_illuminate_import(string $class): void
    {
        $file = (new \ReflectionClass($class))->getFileName();

        self::assertNotFalse($file, "Could not resolve file for {$class}");

        $source = file_get_contents($file);

        self::assertIsString($source);
        self::assertStringNotContainsString(
            'use Illuminate',
            $source,
            "Public contract {$class} must not import any Illuminate/Laravel class.",
        );
    }

    /**
     * @param  class-string  $class
     */
    #[DataProvider('publicContractClassProvider')]
    public function test_public_contract_has_no_product_specific_vocabulary(string $class): void
    {
        $file = (new \ReflectionClass($class))->getFileName();

        self::assertNotFalse($file);

        $source = file_get_contents($file);

        self::assertIsString($source);

        $forbidden = ['wallet.', 'identity.', 'notification.', 'orders.', 'cart.', 'payment.'];

        foreach ($forbidden as $term) {
            self::assertStringNotContainsString(
                $term,
                strtolower($source),
                "Public contract {$class} must not contain product-specific vocabulary '{$term}'.",
            );
        }
    }

    public function test_configuration_key_is_readonly_class(): void
    {
        self::assertTrue(
            (new \ReflectionClass(ConfigurationKey::class))->isReadOnly(),
            'ConfigurationKey must be a readonly class.',
        );
    }

    public function test_configuration_definition_is_readonly_class(): void
    {
        self::assertTrue(
            (new \ReflectionClass(ConfigurationDefinition::class))->isReadOnly(),
            'ConfigurationDefinition must be a readonly class.',
        );
    }

    public function test_configuration_repository_contract_is_an_interface(): void
    {
        self::assertTrue(
            (new \ReflectionClass(ConfigurationRepository::class))->isInterface(),
        );
    }

    public function test_configuration_source_contract_is_an_interface(): void
    {
        self::assertTrue(
            (new \ReflectionClass(ConfigurationSource::class))->isInterface(),
        );
    }

    public function test_configuration_source_contributor_is_an_interface(): void
    {
        self::assertTrue(
            (new \ReflectionClass(ConfigurationSourceContributor::class))->isInterface(),
        );
    }
}
