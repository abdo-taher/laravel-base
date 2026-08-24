<?php

declare(strict_types=1);

namespace Tests\Unit\Packages\Base\Foundation\ModuleManager;

use Base\Foundation\Manifest\Application\ManifestFactory;
use Base\Foundation\Manifest\Infrastructure\JsonManifestReader;
use Base\Foundation\ModuleManager\Application\FilesystemModuleDiscovery;
use Base\Foundation\ModuleManager\Public\Exceptions\ModuleDiscoveryFailed;
use PHPUnit\Framework\TestCase;

final class ModuleDiscoveryTest extends TestCase
{
    private FilesystemModuleDiscovery $discovery;

    private string $tempDir;

    protected function setUp(): void
    {
        parent::setUp();

        $this->discovery = new FilesystemModuleDiscovery(
            new JsonManifestReader(new ManifestFactory),
        );

        $this->tempDir = sys_get_temp_dir().'/module_discovery_test_'.uniqid('', true);
        mkdir($this->tempDir, 0755, true);
    }

    protected function tearDown(): void
    {
        $this->removeDirectory($this->tempDir);
        parent::tearDown();
    }

    // ── Positive ─────────────────────────────────────────────────────────────

    public function test_discovers_single_valid_manifest(): void
    {
        $this->writeManifest($this->tempDir.'/Foundation/Alpha', $this->validManifest('Alpha', 'Foundation'));

        $manifests = $this->discovery->discover([$this->tempDir]);

        self::assertCount(1, $manifests);
        self::assertSame('Alpha', $manifests[0]->name);
        self::assertSame('Foundation', $manifests[0]->category);
    }

    public function test_discovers_multiple_manifests_in_nested_paths(): void
    {
        $this->writeManifest($this->tempDir.'/Foundation/Alpha', $this->validManifest('Alpha', 'Foundation'));
        $this->writeManifest($this->tempDir.'/Foundation/Beta', $this->validManifest('Beta', 'Foundation'));
        $this->writeManifest($this->tempDir.'/Platform/Gamma', $this->validManifest('Gamma', 'Platform'));

        $manifests = $this->discovery->discover([$this->tempDir]);

        self::assertCount(3, $manifests);
        $names = array_map(static fn ($m) => $m->name, $manifests);
        sort($names);
        self::assertSame(['Alpha', 'Beta', 'Gamma'], $names);
    }

    public function test_discovers_manifests_from_multiple_search_paths(): void
    {
        $dirA = $this->tempDir.'/pathA';
        $dirB = $this->tempDir.'/pathB';
        mkdir($dirA.'/Mod', 0755, true);
        mkdir($dirB.'/Mod', 0755, true);

        $this->writeManifest($dirA.'/Mod', $this->validManifest('Alpha', 'Foundation'));
        $this->writeManifest($dirB.'/Mod', $this->validManifest('Beta', 'Platform'));

        $manifests = $this->discovery->discover([$dirA, $dirB]);

        self::assertCount(2, $manifests);
        $names = array_map(static fn ($m) => $m->name, $manifests);
        sort($names);
        self::assertSame(['Alpha', 'Beta'], $names);
    }

    public function test_returns_empty_list_when_no_manifests_exist(): void
    {
        $manifests = $this->discovery->discover([$this->tempDir]);

        self::assertSame([], $manifests);
    }

    public function test_ignores_non_manifest_json_files(): void
    {
        mkdir($this->tempDir.'/SomeModule', 0755, true);
        file_put_contents($this->tempDir.'/SomeModule/config.json', '{}');
        file_put_contents($this->tempDir.'/SomeModule/README.md', '# readme');

        $manifests = $this->discovery->discover([$this->tempDir]);

        self::assertSame([], $manifests);
    }

    // ── Negative ─────────────────────────────────────────────────────────────

    public function test_throws_when_search_path_does_not_exist(): void
    {
        $this->expectException(ModuleDiscoveryFailed::class);
        $this->expectExceptionMessage('not readable');

        $this->discovery->discover(['/nonexistent/path/xyz']);
    }

    public function test_throws_when_manifest_contains_invalid_json(): void
    {
        mkdir($this->tempDir.'/Bad', 0755, true);
        file_put_contents($this->tempDir.'/Bad/module.json', '{invalid json}');

        $this->expectException(ModuleDiscoveryFailed::class);

        $this->discovery->discover([$this->tempDir]);
    }

    public function test_throws_when_manifest_fails_validation(): void
    {
        mkdir($this->tempDir.'/Bad', 0755, true);
        file_put_contents($this->tempDir.'/Bad/module.json', '{"name":"","category":"","version":"","namespace":"","ownership":""}');

        $this->expectException(ModuleDiscoveryFailed::class);
        $this->expectExceptionMessage('Invalid manifest');

        $this->discovery->discover([$this->tempDir]);
    }

    // ── Helpers ──────────────────────────────────────────────────────────────

    /** @param array<string, mixed> $data */
    private function writeManifest(string $dir, array $data): void
    {
        if (! is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        file_put_contents($dir.'/module.json', json_encode($data, JSON_PRETTY_PRINT));
    }

    /** @return array<string, mixed> */
    private function validManifest(string $name, string $category): array
    {
        $nsRoot = $category === 'Product' ? 'Modules' : 'Base';

        return [
            'name' => $name,
            'category' => $category,
            'version' => '1.0.0',
            'namespace' => $nsRoot.'\\'.$name,
            'ownership' => $category === 'Product' ? 'project-owned' : 'base-owned',
            'dependencies' => ['required' => [], 'optional' => []],
            'provides' => [],
        ];
    }

    private function removeDirectory(string $dir): void
    {
        if (! is_dir($dir)) {
            return;
        }

        $items = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($dir, \RecursiveDirectoryIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::CHILD_FIRST,
        );

        foreach ($items as $item) {
            $item->isDir() ? rmdir($item->getRealPath()) : unlink($item->getRealPath());
        }

        rmdir($dir);
    }
}
