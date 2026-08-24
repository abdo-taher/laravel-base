<?php

declare(strict_types=1);

namespace Tests\Unit\Packages\Base\Foundation;

use Base\Foundation\CapabilityRegistry\Public\Exceptions\InvalidCapabilityDefinition;
use Base\Foundation\CapabilityRegistry\Public\ValueObjects\CapabilityVersion;
use Base\Foundation\Manifest\Application\ManifestFactory;
use Base\Foundation\Manifest\Public\Exceptions\InvalidManifest;
use PHPUnit\Framework\TestCase;

/**
 * Cross-package regression tests proving that Manifest and CapabilityRegistry
 * continue to enforce semantic version validation correctly after delegating to
 * SharedKernel\SemanticVersion.
 *
 * This file lives one level above any package-specific test directory so it
 * belongs only to the Tests Deptrac layer and may access all Foundation layers.
 */
final class SharedKernelConsumerIntegrationTest extends TestCase
{
    // ── ManifestFactory regression ───────────────────────────────────────────

    public function test_manifest_factory_accepts_valid_semver(): void
    {
        $manifest = (new ManifestFactory)->create($this->validManifestData('1.2.3'));

        self::assertSame('1.2.3', $manifest->version);
    }

    public function test_manifest_factory_rejects_invalid_version(): void
    {
        $this->expectException(InvalidManifest::class);
        $this->expectExceptionMessage('semantic versioning');

        (new ManifestFactory)->create($this->validManifestData('bad-version'));
    }

    public function test_manifest_factory_rejects_two_part_version(): void
    {
        $this->expectException(InvalidManifest::class);

        (new ManifestFactory)->create($this->validManifestData('1.0'));
    }

    public function test_manifest_factory_rejects_invalid_capability_version(): void
    {
        $data = $this->validManifestData('1.0.0');
        $data['provides'] = [['capability' => 'test.cap', 'version' => 'not-semver']];

        $this->expectException(InvalidManifest::class);
        $this->expectExceptionMessage('semantic versioning');

        (new ManifestFactory)->create($data);
    }

    public function test_manifest_factory_accepts_pre_release_version(): void
    {
        $manifest = (new ManifestFactory)->create($this->validManifestData('1.0.0-alpha.1'));

        self::assertSame('1.0.0-alpha.1', $manifest->version);
    }

    // ── CapabilityVersion regression ─────────────────────────────────────────

    public function test_capability_version_accepts_valid_semver(): void
    {
        $v = new CapabilityVersion('2.5.1');

        self::assertSame(2, $v->major);
        self::assertSame(5, $v->minor);
        self::assertSame(1, $v->patch);
        self::assertSame('2.5.1', $v->value);
    }

    public function test_capability_version_rejects_invalid_version(): void
    {
        $this->expectException(InvalidCapabilityDefinition::class);
        $this->expectExceptionMessage('Invalid capability version');

        new CapabilityVersion('not-a-version');
    }

    public function test_capability_version_rejects_two_part_version(): void
    {
        $this->expectException(InvalidCapabilityDefinition::class);

        new CapabilityVersion('1.0');
    }

    public function test_capability_version_compare_to_still_works(): void
    {
        $lower = new CapabilityVersion('1.0.0');
        $higher = new CapabilityVersion('2.0.0');

        self::assertLessThan(0, $lower->compareTo($higher));
        self::assertGreaterThan(0, $higher->compareTo($lower));
        self::assertSame(0, $lower->compareTo(new CapabilityVersion('1.0.0')));
    }

    // ── Helpers ──────────────────────────────────────────────────────────────

    /** @return array<string, mixed> */
    private function validManifestData(string $version): array
    {
        return [
            'name' => 'TestModule',
            'category' => 'Foundation',
            'version' => $version,
            'namespace' => 'Base\\TestModule',
            'ownership' => 'base-owned',
            'dependencies' => ['required' => [], 'optional' => []],
            'provides' => [],
        ];
    }
}
