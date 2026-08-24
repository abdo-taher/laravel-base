<?php

declare(strict_types=1);

namespace Tests\Unit\Packages\Base\Foundation\ModuleManager;

use Base\Foundation\CapabilityRegistry\Application\InMemoryCapabilityRegistry;
use Base\Foundation\CapabilityRegistry\Application\VersionConstraintMatcher;
use Base\Foundation\DependencyResolver\Application\ManifestDependencyResolver;
use Base\Foundation\Manifest\Application\ManifestFactory;
use Base\Foundation\Manifest\Infrastructure\JsonManifestReader;
use Base\Foundation\ModuleManager\Application\FilesystemModuleDiscovery;
use Base\Foundation\ModuleManager\Application\OrchestrationModuleManager;
use Base\Foundation\ModuleManager\Public\Exceptions\ModuleBootPlanFailed;
use Base\Foundation\ModuleManager\Public\Exceptions\ModuleDiscoveryFailed;
use Base\Foundation\ModuleManager\Public\ValueObjects\ModuleIdentifier;
use Base\Foundation\ModuleManager\Public\ValueObjects\ModuleState;
use PHPUnit\Framework\TestCase;

final class ModuleManagerTest extends TestCase
{
    private OrchestrationModuleManager $manager;

    private string $tempDir;

    protected function setUp(): void
    {
        parent::setUp();

        $this->manager = new OrchestrationModuleManager(
            discovery: new FilesystemModuleDiscovery(
                new JsonManifestReader(new ManifestFactory),
            ),
            dependencyResolver: new ManifestDependencyResolver,
            capabilityResolver: new InMemoryCapabilityRegistry(new VersionConstraintMatcher),
        );

        $this->tempDir = sys_get_temp_dir().'/module_manager_test_'.uniqid('', true);
        mkdir($this->tempDir, 0755, true);
    }

    protected function tearDown(): void
    {
        $this->removeDirectory($this->tempDir);
        parent::tearDown();
    }

    // ── Positive ─────────────────────────────────────────────────────────────

    public function test_boots_with_single_module_and_returns_plan(): void
    {
        $this->writeManifest('Foundation/Manifest', $this->validManifest('Manifest', 'Foundation'));

        $plan = $this->manager->boot([$this->tempDir]);

        $identifiers = $plan->orderedIdentifiers();
        self::assertCount(1, $identifiers);
        self::assertSame('Manifest', $identifiers[0]->name);
        self::assertSame('Foundation', $identifiers[0]->category);
    }

    public function test_boot_plan_states_are_all_ready(): void
    {
        $this->writeManifest('Foundation/Alpha', $this->validManifest('Alpha', 'Foundation'));
        $this->writeManifest('Foundation/Beta', $this->validManifest('Beta', 'Foundation'));

        $plan = $this->manager->boot([$this->tempDir]);

        foreach ($plan->allStates() as $state) {
            self::assertSame(ModuleState::READY, $state->state);
            self::assertTrue($state->isReady());
        }
    }

    public function test_boot_plan_exposes_state_for_identifier(): void
    {
        $this->writeManifest('Foundation/Alpha', $this->validManifest('Alpha', 'Foundation'));

        $plan = $this->manager->boot([$this->tempDir]);
        $identifier = $plan->orderedIdentifiers()[0];

        $state = $plan->stateFor($identifier);
        self::assertNotNull($state);
        self::assertSame('Alpha', $state->identifier->name);
    }

    public function test_boot_plan_returns_null_for_unknown_identifier(): void
    {
        $this->writeManifest('Foundation/Alpha', $this->validManifest('Alpha', 'Foundation'));

        $plan = $this->manager->boot([$this->tempDir]);

        $unknown = new ModuleIdentifier('Unknown', 'Foundation');
        self::assertNull($plan->stateFor($unknown));
    }

    public function test_creates_valid_boot_plan_for_dependent_modules(): void
    {
        // Manifest provides manifest.read; DependencyResolver depends on it.
        $this->writeManifest('Foundation/Manifest', $this->validManifest(
            name: 'Manifest',
            category: 'Foundation',
            provides: [['capability' => 'manifest.read', 'version' => '1.0.0']],
        ));
        $this->writeManifest('Foundation/DependencyResolver', $this->validManifest(
            name: 'DependencyResolver',
            category: 'Foundation',
            requiredDeps: [['capability' => 'manifest.read', 'version' => '^1.0']],
        ));

        $plan = $this->manager->boot([$this->tempDir]);

        $names = array_map(static fn ($id) => $id->name, $plan->orderedIdentifiers());
        self::assertSame(['Manifest', 'DependencyResolver'], $names);
    }

    public function test_boot_order_is_deterministic_regardless_of_discovery_order(): void
    {
        // Two independent Foundation modules — order is alphabetical by name.
        $this->writeManifest('Foundation/Zebra', $this->validManifest('Zebra', 'Foundation'));
        $this->writeManifest('Foundation/Alpha', $this->validManifest('Alpha', 'Foundation'));
        $this->writeManifest('Foundation/Mango', $this->validManifest('Mango', 'Foundation'));

        $plan = $this->manager->boot([$this->tempDir]);

        $names = array_map(static fn ($id) => $id->name, $plan->orderedIdentifiers());
        self::assertSame(['Alpha', 'Mango', 'Zebra'], $names);
    }

    public function test_boot_returns_empty_plan_when_no_manifests_found(): void
    {
        $plan = $this->manager->boot([$this->tempDir]);

        self::assertSame([], $plan->orderedIdentifiers());
        self::assertSame([], $plan->allStates());
    }

    // ── Negative ─────────────────────────────────────────────────────────────

    public function test_throws_discovery_failed_on_unreadable_path(): void
    {
        $this->expectException(ModuleDiscoveryFailed::class);

        $this->manager->boot(['/nonexistent/path/xyz']);
    }

    public function test_throws_discovery_failed_on_invalid_manifest(): void
    {
        mkdir($this->tempDir.'/Bad', 0755, true);
        file_put_contents($this->tempDir.'/Bad/module.json', '{invalid json}');

        $this->expectException(ModuleDiscoveryFailed::class);

        $this->manager->boot([$this->tempDir]);
    }

    public function test_throws_boot_plan_failed_on_missing_required_dependency(): void
    {
        $this->writeManifest('Foundation/Consumer', $this->validManifest(
            name: 'Consumer',
            category: 'Foundation',
            requiredDeps: [['capability' => 'missing.capability', 'version' => '^1.0']],
        ));

        $this->expectException(ModuleBootPlanFailed::class);

        $this->manager->boot([$this->tempDir]);
    }

    public function test_throws_boot_plan_failed_on_dependency_cycle(): void
    {
        $this->writeManifest('Foundation/Alpha', $this->validManifest(
            name: 'Alpha',
            category: 'Foundation',
            requiredDeps: [['package' => 'Beta', 'version' => '^1.0']],
        ));
        $this->writeManifest('Foundation/Beta', $this->validManifest(
            name: 'Beta',
            category: 'Foundation',
            requiredDeps: [['package' => 'Alpha', 'version' => '^1.0']],
        ));

        $this->expectException(ModuleBootPlanFailed::class);
        $this->expectExceptionMessage('dependency resolution error');

        $this->manager->boot([$this->tempDir]);
    }

    public function test_throws_boot_plan_failed_on_duplicate_module_identity(): void
    {
        // Two separate directories each containing a manifest named "Alpha".
        $dirA = $this->tempDir.'/pathA';
        $dirB = $this->tempDir.'/pathB';

        $this->writeManifest('pathA/Foundation/Alpha', $this->validManifest('Alpha', 'Foundation'));
        $this->writeManifest('pathB/Foundation/Alpha', $this->validManifest('Alpha', 'Foundation'));

        $this->expectException(ModuleBootPlanFailed::class);
        $this->expectExceptionMessage('duplicate module identity');

        $this->manager->boot([$this->tempDir]);
    }

    // ── Helpers ──────────────────────────────────────────────────────────────

    /**
     * @param  list<array<string, string>>  $provides
     * @param  list<array<string, string>>  $requiredDeps
     * @return array<string, mixed>
     */
    private function validManifest(
        string $name,
        string $category,
        array $provides = [],
        array $requiredDeps = [],
    ): array {
        $nsRoot = $category === 'Product' ? 'Modules' : 'Base';

        return [
            'name' => $name,
            'category' => $category,
            'version' => '1.0.0',
            'namespace' => $nsRoot.'\\'.$name,
            'ownership' => $category === 'Product' ? 'project-owned' : 'base-owned',
            'dependencies' => [
                'required' => $requiredDeps,
                'optional' => [],
            ],
            'provides' => $provides,
        ];
    }

    /** @param array<string, mixed> $data */
    private function writeManifest(string $relPath, array $data): void
    {
        $dir = $this->tempDir.'/'.$relPath;

        if (! is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        file_put_contents($dir.'/module.json', json_encode($data, JSON_PRETTY_PRINT));
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
