<?php

declare(strict_types=1);

namespace Tests\Feature\Packages\Base\Tooling\ProjectFactory;

use Base\Foundation\DependencyResolver\Application\ManifestDependencyResolver;
use Base\Foundation\Manifest\Application\ManifestFactory;
use Base\Foundation\Manifest\Infrastructure\JsonManifestReader;
use Base\Foundation\Manifest\Public\ValueObjects\ManifestDependency;
use Base\Foundation\ModuleManager\Application\FilesystemModuleDiscovery;
use Base\Tooling\ProjectFactory\Application\DefaultProjectMaterializer;
use Base\Tooling\ProjectFactory\Application\DefaultProjectPlanner;
use Base\Tooling\ProjectFactory\Infrastructure\MaterializationSourceResolver;
use Base\Tooling\ProjectFactory\Public\Exceptions\ProjectMaterializationFailed;
use Base\Tooling\ProjectFactory\Public\ValueObjects\GenerationPlan;
use Base\Tooling\ProjectFactory\Public\ValueObjects\Operations\CopyTemplateOperation;
use Base\Tooling\ProjectFactory\Public\ValueObjects\ProjectDefinition;
use Base\Tooling\ProjectFactory\Public\ValueObjects\ProjectDestination;
use Base\Tooling\ProjectFactory\Public\ValueObjects\ProjectIdentity;
use Base\Tooling\ProjectFactory\Public\ValueObjects\SafePath;
use Base\Tooling\ProjectFactory\Public\ValueObjects\TemplateReference;
use FilesystemIterator;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use Tests\TestCase;

final class ProjectMaterializerIntegrationTest extends TestCase
{
    private DefaultProjectPlanner $planner;

    private DefaultProjectMaterializer $materializer;

    protected function setUp(): void
    {
        parent::setUp();

        $reader = new JsonManifestReader(new ManifestFactory);
        $discovery = new FilesystemModuleDiscovery($reader);
        $manifests = $discovery->discover([base_path('packages/base/Foundation'), base_path('packages/base/Platform'), base_path('modules')]);

        $this->planner = new DefaultProjectPlanner(
            new ManifestDependencyResolver,
            $manifests,
        );

        $this->materializer = new DefaultProjectMaterializer(
            new MaterializationSourceResolver(base_path())
        );
    }

    public function test_transitive_materialization(): void
    {
        $def = new ProjectDefinition(
            identity: new ProjectIdentity('Test Project', 'test-project', 'App'),
            explicitModules: [
                new ManifestDependency('package', 'Modules.ReferenceCatalog', '^0.1.0', true),
            ],
        );
        $plan = $this->planner->plan($def);

        $dest = sys_get_temp_dir().'/base-test-'.bin2hex(random_bytes(4));
        $result = $this->materializer->materialize($plan, new ProjectDestination($dest));

        self::assertTrue($result->published);
        self::assertDirectoryExists($dest.'/packages/base/Platform/Files');
        self::assertDirectoryExists($dest.'/packages/base/Platform/Media');
        self::assertDirectoryExists($dest.'/modules/ReferenceCatalog');

        $this->cleanup($dest);
    }

    public function test_destination_exists_rejected(): void
    {
        $dest = sys_get_temp_dir().'/base-test-'.bin2hex(random_bytes(4));
        mkdir($dest);

        $def = new ProjectDefinition(
            identity: new ProjectIdentity('Test', 't', 'App'),
            explicitModules: [],
        );
        $plan = $this->planner->plan($def);

        $this->expectException(ProjectMaterializationFailed::class);
        $this->expectExceptionMessage('Destination already exists');

        $this->materializer->materialize($plan, new ProjectDestination($dest));

        rmdir($dest);
    }

    public function test_operation_failure_cleans_up_staging(): void
    {
        $def = new ProjectDefinition(
            identity: new ProjectIdentity('Test', 't', 'App'),
            explicitModules: [],
        );
        $plan = clone $this->planner->plan($def);

        // Add a fake copy template operation that will fail (template doesn't exist)
        $ops = $plan->filesystemOperations;
        $ops[] = new CopyTemplateOperation(
            new TemplateReference('product-module/does_not_exist.template'),
            new SafePath('foo.php')
        );

        $brokenPlan = new GenerationPlan($plan->identity, $plan->resolvedGraph, $ops);
        $dest = sys_get_temp_dir().'/base-test-'.bin2hex(random_bytes(4));

        try {
            $this->materializer->materialize($brokenPlan, new ProjectDestination($dest));
            $this->fail('Expected materialization to fail.');
        } catch (ProjectMaterializationFailed $e) {
            self::assertFileDoesNotExist($dest);
            // Staging should be cleaned up. Hard to check exact random staging path, but no orphaned dirs left.
            $orphans = glob(dirname($dest).'/.base-factory-*');
            self::assertEmpty($orphans, 'Found orphaned staging directories: '.implode(',', $orphans ?: []));
        }
    }

    public function test_template_rendering(): void
    {
        $def = new ProjectDefinition(identity: new ProjectIdentity('Test Project', 'test-project', 'App'));
        $plan = $this->planner->plan($def);
        $ops = $plan->filesystemOperations;
        $ops[] = new CopyTemplateOperation(
            new TemplateReference('product-module/module.json.template'),
            new SafePath('module.json'),
            ['MODULE_NAME' => 'test-project']
        );
        $renderPlan = new GenerationPlan($plan->identity, $plan->resolvedGraph, $ops);

        $dest = sys_get_temp_dir().'/base-test-'.bin2hex(random_bytes(4));
        $this->materializer->materialize($renderPlan, new ProjectDestination($dest));

        $content = (string) file_get_contents($dest.'/module.json');
        self::assertStringContainsString('test-project', $content);

        $this->cleanup($dest);
    }

    public function test_deterministic_output(): void
    {
        $def = new ProjectDefinition(
            identity: new ProjectIdentity('Test Project', 'test-project', 'App'),
            explicitModules: [
                new ManifestDependency('package', 'Modules.ReferenceCatalog', '^0.1.0', true),
            ],
        );
        $plan = $this->planner->plan($def);

        $dest1 = sys_get_temp_dir().'/base-test-d1-'.bin2hex(random_bytes(4));
        $dest2 = sys_get_temp_dir().'/base-test-d2-'.bin2hex(random_bytes(4));

        $this->materializer->materialize($plan, new ProjectDestination($dest1));
        $this->materializer->materialize($plan, new ProjectDestination($dest2));

        $files1 = $this->getTree($dest1);
        $files2 = $this->getTree($dest2);

        self::assertEquals(array_keys($files1), array_keys($files2));
        foreach ($files1 as $path => $content) {
            self::assertEquals($content, $files2[$path], "Content mismatch for $path");
        }

        $this->cleanup($dest1);
        $this->cleanup($dest2);
    }

    /** @return array<string, string> */
    private function getTree(string $dir): array
    {
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($dir, FilesystemIterator::SKIP_DOTS),
            RecursiveIteratorIterator::SELF_FIRST
        );
        $tree = [];
        /** @var \SplFileInfo $item */
        foreach ($iterator as $item) {
            if ($item->isFile()) {
                $subPath = $iterator->getSubPathName();
                $tree[$subPath] = (string) file_get_contents($item->getPathname());
            }
        }
        ksort($tree);

        return $tree;
    }

    private function cleanup(string $dir): void
    {
        if (! is_dir($dir)) {
            return;
        }
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($dir, FilesystemIterator::SKIP_DOTS),
            RecursiveIteratorIterator::CHILD_FIRST
        );
        /** @var \SplFileInfo $item */
        foreach ($iterator as $item) {
            if ($item->isDir()) {
                rmdir($item->getPathname());
            } else {
                unlink($item->getPathname());
            }
        }
        rmdir($dir);
    }
}
