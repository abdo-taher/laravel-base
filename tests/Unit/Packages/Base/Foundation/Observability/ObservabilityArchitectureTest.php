<?php

declare(strict_types=1);

namespace Tests\Unit\Packages\Base\Foundation\Observability;

use Base\Foundation\Observability\Public\Contracts\Logger;
use Base\Foundation\Observability\Public\Contracts\Metrics;
use Base\Foundation\Observability\Public\Contracts\Span;
use Base\Foundation\Observability\Public\Contracts\Tracer;
use Base\Foundation\Observability\Public\ValueObjects\CorrelationId;
use Base\Foundation\Observability\Public\ValueObjects\LogContext;
use Base\Foundation\Observability\Public\ValueObjects\MetricName;
use Base\Foundation\Observability\Public\ValueObjects\MetricTags;
use Base\Foundation\Observability\Public\ValueObjects\ObservabilityContext;
use Base\Foundation\Observability\Public\ValueObjects\SpanName;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

final class ObservabilityArchitectureTest extends TestCase
{
    /** @return array<string, array{class-string}> */
    public static function publicContractClassProvider(): array
    {
        return [
            'Logger' => [Logger::class],
            'Metrics' => [Metrics::class],
            'Span' => [Span::class],
            'Tracer' => [Tracer::class],
            'CorrelationId' => [CorrelationId::class],
            'LogContext' => [LogContext::class],
            'MetricName' => [MetricName::class],
            'MetricTags' => [MetricTags::class],
            'ObservabilityContext' => [ObservabilityContext::class],
            'SpanName' => [SpanName::class],
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
    public function test_public_contract_has_no_monolog_import(string $class): void
    {
        $file = (new ReflectionClass($class))->getFileName();
        self::assertNotFalse($file);
        $source = file_get_contents($file);
        self::assertIsString($source);

        self::assertStringNotContainsString(
            'use Monolog',
            $source,
            "Public contract {$class} must not import any Monolog class."
        );
        self::assertStringNotContainsString(
            'use Psr\Log',
            $source,
            "Public contract {$class} must not import PSR-3 directly."
        );
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
