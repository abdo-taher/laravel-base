<?php

declare(strict_types=1);

namespace Tests\Unit\Packages\Base\Foundation\Audit;

use Base\Foundation\Audit\Public\Contracts\AuditRecorder;
use Base\Foundation\Audit\Public\Contracts\Clock;
use Base\Foundation\Audit\Public\Exceptions\AuditRecordingFailed;
use Base\Foundation\Audit\Public\ValueObjects\Action;
use Base\Foundation\Audit\Public\ValueObjects\AuditEvent;
use Base\Foundation\Audit\Public\ValueObjects\Metadata;
use Base\Foundation\Audit\Public\ValueObjects\SubjectRef;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

/**
 * Architecture tests for the Audit Foundation package.
 *
 * Proves:
 * - Public contracts contain no Laravel/Illuminate dependency.
 * - Public contracts expose no Eloquent model types.
 * - Public contracts expose no product-specific vocabulary.
 * - Value objects are readonly.
 * - Contracts are interfaces.
 */
final class AuditArchitectureTest extends TestCase
{
    /** @return array<string, array{class-string}> */
    public static function publicContractClassProvider(): array
    {
        return [
            'AuditRecorder' => [AuditRecorder::class],
            'Clock' => [Clock::class],
            'AuditRecordingFailed' => [AuditRecordingFailed::class],
            'AuditEvent' => [AuditEvent::class],
            'Action' => [Action::class],
            'SubjectRef' => [SubjectRef::class],
            'Metadata' => [Metadata::class],
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

    public function test_contracts_are_interfaces(): void
    {
        foreach ([
            AuditRecorder::class,
            Clock::class,
        ] as $class) {
            self::assertTrue(
                (new ReflectionClass($class))->isInterface(),
                "{$class} must be an interface."
            );
        }
    }

    public function test_value_objects_are_readonly(): void
    {
        foreach ([
            AuditEvent::class,
            Action::class,
            SubjectRef::class,
            Metadata::class,
        ] as $class) {
            self::assertTrue(
                (new ReflectionClass($class))->isReadOnly(),
                "{$class} must be a readonly class."
            );
        }
    }
}
