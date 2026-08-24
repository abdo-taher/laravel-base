<?php

declare(strict_types=1);

use Deptrac\Deptrac\Contract\Config\Collector\DirectoryConfig;
use Deptrac\Deptrac\Contract\Config\DeptracConfig;
use Deptrac\Deptrac\Contract\Config\Layer;
use Deptrac\Deptrac\Contract\Config\Ruleset;

return static function (DeptracConfig $config): void {
    $config
        ->paths(
            './app',
            './bootstrap',
            './database',
            './packages/base',
            './routes',
            './tests',
        )
        ->excludeFiles(
            '#/vendor/#',
            '#/storage/#',
            '#/bootstrap/cache/#',
        )
        ->layers(
            $hostHttp = Layer::withName('HostHttp')->collectors(
                DirectoryConfig::create('app/Http/.*'),
            ),
            $hostModels = Layer::withName('HostModels')->collectors(
                DirectoryConfig::create('app/Models/.*'),
            ),
            $hostProviders = Layer::withName('HostProviders')->collectors(
                DirectoryConfig::create('app/Providers/.*'),
            ),
            $bootstrap = Layer::withName('Bootstrap')->collectors(
                DirectoryConfig::create('bootstrap/.*'),
            ),
            $database = Layer::withName('Database')->collectors(
                DirectoryConfig::create('database/.*'),
            ),
            $routes = Layer::withName('Routes')->collectors(
                DirectoryConfig::create('routes/.*'),
            ),
            $tests = Layer::withName('Tests')->collectors(
                DirectoryConfig::create('tests/.*'),
            ),
            // ── Base Foundation layers ──────────────────────────────────────
            $baseModuleManager = Layer::withName('Base.Foundation.ModuleManager')->collectors(
                DirectoryConfig::create('packages/base/Foundation/ModuleManager/.*'),
            ),
            $baseManifest = Layer::withName('Base.Foundation.Manifest')->collectors(
                DirectoryConfig::create('packages/base/Foundation/Manifest/.*'),
            ),
            $baseCapabilityRegistry = Layer::withName('Base.Foundation.CapabilityRegistry')->collectors(
                DirectoryConfig::create('packages/base/Foundation/CapabilityRegistry/.*'),
            ),
            $baseDependencyResolver = Layer::withName('Base.Foundation.DependencyResolver')->collectors(
                DirectoryConfig::create('packages/base/Foundation/DependencyResolver/.*'),
            ),
            $baseExtensionRegistry = Layer::withName('Base.Foundation.ExtensionRegistry')->collectors(
                DirectoryConfig::create('packages/base/Foundation/ExtensionRegistry/.*'),
            ),
        )
        ->rulesets(
            Ruleset::forLayer($hostHttp)
                ->accesses($hostModels),

            Ruleset::forLayer($hostModels),

            Ruleset::forLayer($hostProviders),

            Ruleset::forLayer($bootstrap)
                ->accesses($hostProviders),

            Ruleset::forLayer($database)
                ->accesses($hostModels),

            Ruleset::forLayer($routes)
                ->accesses(
                    $hostHttp,
                    $hostModels,
                ),

            Ruleset::forLayer($tests)
                ->accesses(
                    $hostHttp,
                    $hostModels,
                    $hostProviders,
                    $bootstrap,
                    $database,
                    $routes,
                    $baseModuleManager,
                    $baseManifest,
                    $baseCapabilityRegistry,
                    $baseDependencyResolver,
                    $baseExtensionRegistry,
                ),

            // ModuleManager orchestrates across all other Foundation packages.
            // It may only access their Public contracts, not internal layers.
            Ruleset::forLayer($baseModuleManager)
                ->accesses(
                    $baseManifest,
                    $baseCapabilityRegistry,
                    $baseDependencyResolver,
                    $baseExtensionRegistry,
                ),
            Ruleset::forLayer($baseManifest),
            Ruleset::forLayer($baseCapabilityRegistry),
            Ruleset::forLayer($baseDependencyResolver)
                ->accesses($baseManifest),
            Ruleset::forLayer($baseExtensionRegistry),
        );
};
