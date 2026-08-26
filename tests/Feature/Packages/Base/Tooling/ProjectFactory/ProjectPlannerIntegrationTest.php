<?php

declare(strict_types=1);

namespace Tests\Feature\Packages\Base\Tooling\ProjectFactory;

use Base\Foundation\DependencyResolver\Application\ManifestDependencyResolver;
use Base\Foundation\Manifest\Application\ManifestFactory;
use Base\Foundation\Manifest\Infrastructure\JsonManifestReader;
use Base\Foundation\Manifest\Public\ValueObjects\ManifestDependency;
use Base\Foundation\ModuleManager\Application\FilesystemModuleDiscovery;
use Base\Tooling\ProjectFactory\Application\DefaultProjectPlanner;
use Base\Tooling\ProjectFactory\Public\ValueObjects\ProjectDefinition;
use Base\Tooling\ProjectFactory\Public\ValueObjects\ProjectIdentity;
use Base\Tooling\ProjectFactory\Public\ValueObjects\SelectionReason;
use Tests\TestCase;

final class ProjectPlannerIntegrationTest extends TestCase
{
    private DefaultProjectPlanner $planner;

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
    }

    public function test_explicit_module_selection_resolves_transitively(): void
    {
        $def = new ProjectDefinition(
            identity: new ProjectIdentity('Test', 'test-app', 'App'),
            explicitModules: [
                new ManifestDependency('package', 'Modules.ReferenceCatalog', '^0.1.0', true),
            ],
        );

        $plan = $this->planner->plan($def);
        $names = array_map(fn ($node) => $node->manifest->name, $plan->resolvedGraph);

        self::assertContains('Base.Platform.Files', $names);
        self::assertContains('Base.Platform.Media', $names);
        self::assertContains('Modules.ReferenceCatalog', $names);

        // Assert execution order matches topological dependency
        $filesIdx = array_search('Base.Platform.Files', $names, true);
        $mediaIdx = array_search('Base.Platform.Media', $names, true);
        $catalogIdx = array_search('Modules.ReferenceCatalog', $names, true);

        self::assertLessThan($mediaIdx, $filesIdx);
        self::assertLessThan($catalogIdx, $mediaIdx);

        // Check reasons
        $catalogNode = array_values(array_filter($plan->resolvedGraph, fn ($n) => $n->manifest->name === 'Modules.ReferenceCatalog'))[0];
        $mediaNode = array_values(array_filter($plan->resolvedGraph, fn ($n) => $n->manifest->name === 'Base.Platform.Media'))[0];

        self::assertEquals(SelectionReason::DIRECT_MODULE, $catalogNode->reason);
        self::assertEquals(SelectionReason::AUTO_RESOLVED, $mediaNode->reason);
    }

    public function test_explicit_capability_selection(): void
    {
        $def = new ProjectDefinition(
            identity: new ProjectIdentity('Test', 'test-app', 'App'),
            explicitCapabilities: [
                new ManifestDependency('capability', 'media.attachments', '^1.0', true),
            ],
        );

        $plan = $this->planner->plan($def);
        $names = array_map(fn ($node) => $node->manifest->name, $plan->resolvedGraph);

        self::assertContains('Base.Platform.Files', $names);
        self::assertContains('Base.Platform.Media', $names);
        self::assertNotContains('Modules.ReferenceCatalog', $names);

        $mediaNode = array_values(array_filter($plan->resolvedGraph, fn ($n) => $n->manifest->name === 'Base.Platform.Media'))[0];
        self::assertEquals(SelectionReason::DIRECT_CAPABILITY, $mediaNode->reason);
    }

    public function test_duplicate_selection_is_normalized(): void
    {
        $def1 = new ProjectDefinition(
            identity: new ProjectIdentity('Test', 'test-app', 'App'),
            explicitModules: [
                new ManifestDependency('package', 'Modules.ReferenceCatalog', '^0.1.0', true),
                new ManifestDependency('package', 'Modules.ReferenceCatalog', '^0.1.0', true),
            ],
        );
        $def2 = new ProjectDefinition(
            identity: new ProjectIdentity('Test', 'test-app', 'App'),
            explicitModules: [
                new ManifestDependency('package', 'Modules.ReferenceCatalog', '^0.1.0', true),
            ],
        );

        $plan1 = $this->planner->plan($def1);
        $plan2 = $this->planner->plan($def2);

        self::assertEquals($plan2->resolvedGraph, $plan1->resolvedGraph);
    }
}
