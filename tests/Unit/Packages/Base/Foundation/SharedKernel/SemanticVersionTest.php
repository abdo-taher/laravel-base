<?php

declare(strict_types=1);

namespace Tests\Unit\Packages\Base\Foundation\SharedKernel;

use Base\Foundation\SharedKernel\Public\Exceptions\InvalidSemanticVersion;
use Base\Foundation\SharedKernel\Public\ValueObjects\SemanticVersion;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class SemanticVersionTest extends TestCase
{
    // ── Positive: parsing ────────────────────────────────────────────────────

    public function test_parses_basic_version(): void
    {
        $v = SemanticVersion::from('1.2.3');

        self::assertSame(1, $v->major);
        self::assertSame(2, $v->minor);
        self::assertSame(3, $v->patch);
        self::assertNull($v->preRelease);
        self::assertNull($v->buildMetadata);
        self::assertSame('1.2.3', $v->value);
    }

    public function test_parses_zero_version(): void
    {
        $v = SemanticVersion::from('0.0.0');

        self::assertSame(0, $v->major);
        self::assertSame(0, $v->minor);
        self::assertSame(0, $v->patch);
    }

    public function test_parses_large_version_numbers(): void
    {
        $v = SemanticVersion::from('100.200.300');

        self::assertSame(100, $v->major);
        self::assertSame(200, $v->minor);
        self::assertSame(300, $v->patch);
    }

    public function test_parses_pre_release_identifier(): void
    {
        $v = SemanticVersion::from('1.0.0-alpha.1');

        self::assertSame(1, $v->major);
        self::assertSame(0, $v->minor);
        self::assertSame(0, $v->patch);
        self::assertSame('alpha.1', $v->preRelease);
        self::assertNull($v->buildMetadata);
    }

    public function test_parses_build_metadata(): void
    {
        $v = SemanticVersion::from('1.0.0+build.42');

        self::assertSame('build.42', $v->buildMetadata);
        self::assertNull($v->preRelease);
    }

    public function test_parses_pre_release_and_build_metadata_together(): void
    {
        $v = SemanticVersion::from('1.0.0-beta.2+exp.sha.5114f85');

        self::assertSame('beta.2', $v->preRelease);
        self::assertSame('exp.sha.5114f85', $v->buildMetadata);
    }

    public function test_from_returns_same_object_as_constructor(): void
    {
        $fromFactory = SemanticVersion::from('2.3.4');
        $fromNew = new SemanticVersion('2.3.4');

        self::assertSame($fromFactory->value, $fromNew->value);
        self::assertSame($fromFactory->major, $fromNew->major);
        self::assertSame($fromFactory->minor, $fromNew->minor);
        self::assertSame($fromFactory->patch, $fromNew->patch);
    }

    // ── Positive: isValid ────────────────────────────────────────────────────

    #[DataProvider('validVersionProvider')]
    public function test_is_valid_returns_true_for_valid_versions(string $version): void
    {
        self::assertTrue(SemanticVersion::isValid($version));
    }

    /** @return array<string, array{string}> */
    public static function validVersionProvider(): array
    {
        return [
            'basic' => ['1.0.0'],
            'zeros' => ['0.0.0'],
            'large' => ['10.20.30'],
            'pre-release' => ['1.0.0-alpha'],
            'pre-release-dot' => ['1.0.0-alpha.1'],
            'build' => ['1.0.0+build.1'],
            'pre+build' => ['1.0.0-rc.1+sha.abc'],
        ];
    }

    // ── Positive: comparison ─────────────────────────────────────────────────

    public function test_compare_to_returns_zero_for_equal_versions(): void
    {
        $a = SemanticVersion::from('1.2.3');
        $b = SemanticVersion::from('1.2.3');

        self::assertSame(0, $a->compareTo($b));
    }

    public function test_compare_to_returns_negative_when_less_than(): void
    {
        $a = SemanticVersion::from('1.0.0');
        $b = SemanticVersion::from('2.0.0');

        self::assertLessThan(0, $a->compareTo($b));
    }

    public function test_compare_to_returns_positive_when_greater_than(): void
    {
        $a = SemanticVersion::from('2.0.0');
        $b = SemanticVersion::from('1.9.9');

        self::assertGreaterThan(0, $a->compareTo($b));
    }

    public function test_equals_returns_true_for_identical_versions(): void
    {
        self::assertTrue(
            SemanticVersion::from('1.0.0')->equals(SemanticVersion::from('1.0.0')),
        );
    }

    public function test_is_greater_than(): void
    {
        self::assertTrue(
            SemanticVersion::from('1.1.0')->isGreaterThan(SemanticVersion::from('1.0.0')),
        );
    }

    public function test_is_less_than(): void
    {
        self::assertTrue(
            SemanticVersion::from('0.9.0')->isLessThan(SemanticVersion::from('1.0.0')),
        );
    }

    public function test_to_string_returns_original_value(): void
    {
        self::assertSame('3.14.15', SemanticVersion::from('3.14.15')->toString());
    }

    // ── Negative: invalid versions ───────────────────────────────────────────

    #[DataProvider('invalidVersionProvider')]
    public function test_constructor_throws_for_invalid_version(string $version): void
    {
        $this->expectException(InvalidSemanticVersion::class);

        new SemanticVersion($version);
    }

    /** @return array<string, array{string}> */
    public static function invalidVersionProvider(): array
    {
        return [
            'empty string' => [''],
            'two parts' => ['1.0'],
            'one part' => ['1'],
            'leading zero major' => ['01.0.0'],
            'leading zero minor' => ['1.01.0'],
            'leading zero patch' => ['1.0.01'],
            'negative major' => ['-1.0.0'],
            'text only' => ['bad'],
            'semver with spaces' => ['1. 0.0'],
            'v prefix' => ['v1.0.0'],
            'four parts' => ['1.0.0.0'],
        ];
    }

    public function test_is_valid_returns_false_for_invalid_versions(): void
    {
        self::assertFalse(SemanticVersion::isValid(''));
        self::assertFalse(SemanticVersion::isValid('1.0'));
        self::assertFalse(SemanticVersion::isValid('v1.0.0'));
        self::assertFalse(SemanticVersion::isValid('01.0.0'));
    }

    public function test_exception_message_contains_the_invalid_value(): void
    {
        try {
            SemanticVersion::from('not-semver');
            self::fail('Expected InvalidSemanticVersion');
        } catch (InvalidSemanticVersion $e) {
            self::assertStringContainsString('not-semver', $e->getMessage());
        }
    }

    public function test_exception_message_describes_empty_string_clearly(): void
    {
        try {
            SemanticVersion::from('');
            self::fail('Expected InvalidSemanticVersion');
        } catch (InvalidSemanticVersion $e) {
            self::assertStringContainsString('empty string', $e->getMessage());
        }
    }

    // ── Immutability ─────────────────────────────────────────────────────────

    public function test_semantic_version_is_readonly(): void
    {
        $reflection = new \ReflectionClass(SemanticVersion::class);

        self::assertTrue($reflection->isReadOnly(), 'SemanticVersion must be a readonly class');
    }

    // ── Framework independence ────────────────────────────────────────────────

    public function test_no_framework_imports(): void
    {
        $fileName = (new \ReflectionClass(SemanticVersion::class))->getFileName();

        self::assertNotFalse($fileName, 'SemanticVersion source file must be resolvable');

        $file = file_get_contents($fileName);

        self::assertIsString($file);
        self::assertStringNotContainsString(
            'use Illuminate',
            $file,
            'SemanticVersion must not import any Illuminate/Laravel class',
        );
    }
}
