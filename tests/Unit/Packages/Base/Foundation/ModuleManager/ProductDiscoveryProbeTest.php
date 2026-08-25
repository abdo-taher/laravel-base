<?php

declare(strict_types=1);

namespace Tests\Unit\Packages\Base\Foundation\ModuleManager;

use Base\Foundation\Manifest\Application\ManifestFactory;
use Base\Foundation\Manifest\Infrastructure\JsonManifestReader;
use Base\Foundation\ModuleManager\Application\FilesystemModuleDiscovery;
use PHPUnit\Framework\TestCase;

final class ProductDiscoveryProbeTest extends TestCase
{
    private string $tempDir;

    protected function setUp(): void
    {
        $this->tempDir = sys_get_temp_dir().'/b6-probe-'.bin2hex(random_bytes(4));
        mkdir($this->tempDir, 0700, true);
        mkdir($this->tempDir.'/TestModule', 0700, true);

        file_put_contents($this->tempDir.'/TestModule/module.json', json_encode([
            'name' => 'Modules.TestModule',
            'version' => '1.0.0',
            'category' => 'Product',
            'ownership' => 'project-owned',
            'namespace' => 'Modules\\TestModule',
        ]));
    }

    protected function tearDown(): void
    {
        unlink($this->tempDir.'/TestModule/module.json');
        rmdir($this->tempDir.'/TestModule');
        rmdir($this->tempDir);
    }

    public function test_discovers_project_owned_product_module(): void
    {
        $factory = new ManifestFactory;
        $reader = new JsonManifestReader($factory);
        $discovery = new FilesystemModuleDiscovery($reader);

        $manifests = $discovery->discover([$this->tempDir]);

        $this->assertCount(1, $manifests);
        $this->assertSame('Modules.TestModule', $manifests[0]->name);
        $this->assertSame('Product', $manifests[0]->category);
        $this->assertSame('project-owned', $manifests[0]->ownership);
    }
}
