<?php

declare(strict_types=1);

namespace Tests\Unit\Packages\Base\Foundation\SharedKernel;

use Base\Foundation\SharedKernel\Public\Exceptions\InvalidSemanticVersion;
use Base\Foundation\SharedKernel\Public\ValueObjects\SemanticVersion;
use PHPUnit\Framework\TestCase;

/**
 * Proves the SharedKernel package namespace is autoloaded correctly.
 *
 * No Laravel dependency — pure Composer autoload probe.
 */
final class NamespaceTest extends TestCase
{
    public function test_semantic_version_class_exists(): void
    {
        self::assertTrue(
            class_exists(SemanticVersion::class),
            'SemanticVersion must be discoverable via PSR-4 autoloading.',
        );
    }

    public function test_semantic_version_has_correct_namespace(): void
    {
        self::assertSame(
            'Base\Foundation\SharedKernel\Public\ValueObjects\SemanticVersion',
            SemanticVersion::class,
        );
    }

    public function test_invalid_semantic_version_class_exists(): void
    {
        self::assertTrue(
            class_exists(InvalidSemanticVersion::class),
            'InvalidSemanticVersion must be discoverable via PSR-4 autoloading.',
        );
    }

    public function test_invalid_semantic_version_has_correct_namespace(): void
    {
        self::assertSame(
            'Base\Foundation\SharedKernel\Public\Exceptions\InvalidSemanticVersion',
            InvalidSemanticVersion::class,
        );
    }
}
