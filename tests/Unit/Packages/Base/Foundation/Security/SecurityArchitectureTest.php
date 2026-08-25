<?php

declare(strict_types=1);

namespace Tests\Unit\Packages\Base\Foundation\Security;

use Base\Foundation\Security\Public\Contracts\SecureTokenGenerator;
use Base\Foundation\Security\Public\Utilities\SecureCompare;
use Base\Foundation\Security\Public\ValueObjects\SensitiveValue;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

final class SecurityArchitectureTest extends TestCase
{
    /** @return array<string, array{class-string}> */
    public static function publicContractClassProvider(): array
    {
        return [
            'SecureTokenGenerator' => [SecureTokenGenerator::class],
            'SecureCompare' => [SecureCompare::class],
            'SensitiveValue' => [SensitiveValue::class],
        ];
    }

    /** @param class-string $class */
    #[DataProvider('publicContractClassProvider')]
    public function test_public_contract_has_no_framework_import(string $class): void
    {
        $file = (new ReflectionClass($class))->getFileName();
        self::assertNotFalse($file);
        $source = file_get_contents($file);
        self::assertIsString($source);

        self::assertStringNotContainsString('use Illuminate', $source, "Public contract {$class} must not import any Illuminate class.");
        self::assertStringNotContainsString('use Monolog', $source, "Public contract {$class} must not import any Monolog class.");
        self::assertStringNotContainsString('use Symfony', $source, "Public contract {$class} must not import any Symfony class.");
    }
}
