<?php

declare(strict_types=1);

namespace Tests\Unit\Packages\Base\Foundation\Health;

use Base\Foundation\Health\Public\Contracts\HealthCheck;
use Base\Foundation\Health\Public\Contracts\HealthRegistry;
use Base\Foundation\Health\Public\Contracts\HealthReporter;
use Base\Foundation\Health\Public\ValueObjects\HealthCheckResult;
use Base\Foundation\Health\Public\ValueObjects\HealthMetadata;
use Base\Foundation\Health\Public\ValueObjects\HealthReport;
use Base\Foundation\Health\Public\ValueObjects\HealthStatus;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

final class HealthArchitectureTest extends TestCase
{
    /** @return array<string, array{class-string}> */
    public static function publicContractClassProvider(): array
    {
        return [
            'HealthCheck' => [HealthCheck::class],
            'HealthRegistry' => [HealthRegistry::class],
            'HealthReporter' => [HealthReporter::class],
            'HealthCheckResult' => [HealthCheckResult::class],
            'HealthMetadata' => [HealthMetadata::class],
            'HealthReport' => [HealthReport::class],
            'HealthStatus' => [HealthStatus::class],
        ];
    }

    /** @param class-string $class */
    #[DataProvider('publicContractClassProvider')]
    public function test_public_contract_has_no_illuminate_import(string $class): void
    {
        $file = (new ReflectionClass($class))->getFileName();
        self::assertNotFalse($file);
        $source = file_get_contents($file);
        self::assertIsString($source);

        self::assertStringNotContainsString(
            'use Illuminate',
            $source,
            "Public contract {$class} must not import any Illuminate/Laravel class."
        );
    }

    /** @param class-string $class */
    #[DataProvider('publicContractClassProvider')]
    public function test_public_contract_has_no_eloquent_reference(string $class): void
    {
        $file = (new ReflectionClass($class))->getFileName();
        self::assertNotFalse($file);
        $source = file_get_contents($file);
        self::assertIsString($source);

        foreach (['Eloquent', 'Builder', 'HasFactory', 'Authenticatable', 'Model'] as $term) {
            self::assertStringNotContainsString(
                $term,
                $source,
                "Public contract {$class} must not reference Eloquent type '{$term}'."
            );
        }
    }

    /** @param class-string $class */
    #[DataProvider('publicContractClassProvider')]
    public function test_public_contract_has_no_product_vocabulary(string $class): void
    {
        $file = (new ReflectionClass($class))->getFileName();
        self::assertNotFalse($file);
        $source = file_get_contents($file);
        self::assertIsString($source);

        foreach (['wallet.', 'order.', 'cart.', 'payment.', 'vendor.', 'customer.', 'product.'] as $term) {
            self::assertStringNotContainsString(
                $term,
                strtolower($source),
                "Public contract {$class} must not contain product vocabulary '{$term}'."
            );
        }
    }
}
