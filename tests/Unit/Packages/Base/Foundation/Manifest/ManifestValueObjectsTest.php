<?php

declare(strict_types=1);

namespace Tests\Unit\Packages\Base\Foundation\Manifest;

use Base\Foundation\Manifest\Public\ValueObjects\Manifest;
use Base\Foundation\Manifest\Public\ValueObjects\ManifestCapability;
use Base\Foundation\Manifest\Public\ValueObjects\ManifestDependency;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

final class ManifestValueObjectsTest extends TestCase
{
    public function test_manifest_value_objects_are_readonly(): void
    {
        self::assertTrue((new ReflectionClass(Manifest::class))->isReadOnly());
        self::assertTrue((new ReflectionClass(ManifestDependency::class))->isReadOnly());
        self::assertTrue((new ReflectionClass(ManifestCapability::class))->isReadOnly());
    }

    public function test_dependency_value_object_preserves_target_and_requirement_state(): void
    {
        $dependency = new ManifestDependency(
            targetType: 'capability',
            target: 'identity.current-principal',
            version: '^1.0',
            required: true,
        );

        self::assertSame('capability', $dependency->targetType);
        self::assertSame('identity.current-principal', $dependency->target);
        self::assertSame('^1.0', $dependency->version);
        self::assertTrue($dependency->required);
    }
}
