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
                DirectoryConfig::create('^app/Http/.*'),
            ),
            $hostModels = Layer::withName('HostModels')->collectors(
                DirectoryConfig::create('^app/Models/.*'),
            ),
            $hostProviders = Layer::withName('HostProviders')->collectors(
                DirectoryConfig::create('^app/Providers/.*'),
            ),
            $bootstrap = Layer::withName('Bootstrap')->collectors(
                DirectoryConfig::create('^bootstrap/.*'),
            ),
            $database = Layer::withName('Database')->collectors(
                DirectoryConfig::create('^database/.*'),
            ),
            $routes = Layer::withName('Routes')->collectors(
                DirectoryConfig::create('^routes/.*'),
            ),
            $tests = Layer::withName('Tests')->collectors(
                DirectoryConfig::create('^tests/.*'),
            ),
            // ── Base Foundation layers ──────────────────────────────────────
            $baseSharedKernel = Layer::withName('Base.Foundation.SharedKernel')->collectors(
                DirectoryConfig::create('^packages/base/Foundation/SharedKernel/.*'),
            ),
            $baseConfiguration = Layer::withName('Base.Foundation.Configuration')->collectors(
                DirectoryConfig::create('^packages/base/Foundation/Configuration/.*'),
            ),
            $baseIdentity = Layer::withName('Base.Foundation.Identity')->collectors(
                DirectoryConfig::create('^packages/base/Foundation/Identity/.*'),
            ),
            $baseModuleManager = Layer::withName('Base.Foundation.ModuleManager')->collectors(
                DirectoryConfig::create('^packages/base/Foundation/ModuleManager/.*'),
            ),
            $baseManifest = Layer::withName('Base.Foundation.Manifest')->collectors(
                DirectoryConfig::create('^packages/base/Foundation/Manifest/.*'),
            ),
            $baseCapabilityRegistry = Layer::withName('Base.Foundation.CapabilityRegistry')->collectors(
                DirectoryConfig::create('^packages/base/Foundation/CapabilityRegistry/.*'),
            ),
            $baseDependencyResolver = Layer::withName('Base.Foundation.DependencyResolver')->collectors(
                DirectoryConfig::create('^packages/base/Foundation/DependencyResolver/.*'),
            ),
            $baseExtensionRegistry = Layer::withName('Base.Foundation.ExtensionRegistry')->collectors(
                DirectoryConfig::create('^packages/base/Foundation/ExtensionRegistry/.*'),
            ),
            $baseSecurity = Layer::withName('Base.Foundation.Security')->collectors(
                DirectoryConfig::create('^packages/base/Foundation/Security/.*'),
            ),
            $baseHealth = Layer::withName('Base.Foundation.Health')->collectors(
                DirectoryConfig::create('^packages/base/Foundation/Health/.*'),
            ),
            $baseObservability = Layer::withName('Base.Foundation.Observability')->collectors(
                DirectoryConfig::create('^packages/base/Foundation/Observability/.*'),
            ),
            $baseAudit = Layer::withName('Base.Foundation.Audit')->collectors(
                DirectoryConfig::create('^packages/base/Foundation/Audit/.*'),
            ),
            $baseAccessControl = Layer::withName('Base.Foundation.AccessControl')->collectors(
                DirectoryConfig::create('^packages/base/Foundation/AccessControl/.*'),
            ),
            // ── Base Platform layers ────────────────────────────────────────
            $baseSettings = Layer::withName('Base.Platform.Settings')->collectors(
                DirectoryConfig::create('^packages/base/Platform/Settings/.*'),
            ),
            $baseFiles = Layer::withName('Base.Platform.Files')->collectors(
                DirectoryConfig::create('^packages/base/Platform/Files/.*'),
            ),
            $baseNotifications = Layer::withName('Base.Platform.Notifications')->collectors(
                DirectoryConfig::create('^packages/base/Platform/Notifications/.*'),
            ),
            $baseFeatureFlags = Layer::withName('Base.Platform.FeatureFlags')->collectors(
                DirectoryConfig::create('^packages/base/Platform/FeatureFlags/.*'),
            ),
            // ── Base Specialized layers ──────────────────────────────────────
            $baseOutboundWebhooks = Layer::withName('Base.Specialized.OutboundWebhooks')->collectors(
                DirectoryConfig::create('^packages/base/Specialized/OutboundWebhooks/.*'),
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
                    $baseSharedKernel,
                    $baseConfiguration,
                    $baseIdentity,
                    $baseModuleManager,
                    $baseManifest,
                    $baseCapabilityRegistry,
                    $baseDependencyResolver,
                    $baseExtensionRegistry,
                    $baseAccessControl,
                    $baseAudit,
                    $baseObservability,
                    $baseHealth,
                    $baseSecurity,
                    $baseSettings,
                    $baseFiles,
                    $baseNotifications,
                    $baseFeatureFlags,
                    $baseOutboundWebhooks,
                ),

            // SharedKernel is the lowest layer — no dependencies on other Foundation packages.
            Ruleset::forLayer($baseSharedKernel),

            // Configuration has no Foundation package dependencies.
            // Infrastructure layer uses Illuminate\Contracts only (not tracked by Deptrac
            // since vendor is excluded).
            Ruleset::forLayer($baseConfiguration),

            // Identity has no Foundation package dependencies.
            // Infrastructure layer uses Illuminate\Contracts\Auth only (vendor excluded).
            Ruleset::forLayer($baseIdentity),

            // AccessControl depends on Identity Public contracts (Principal value objects).
            // Audit depends on Identity Public contracts (Principal value objects).
            // Observability has no Foundation package dependencies.
            // Health has no Foundation package dependencies.
            // Security has no Foundation package dependencies.
            Ruleset::forLayer($baseSecurity),

            Ruleset::forLayer($baseHealth),

            Ruleset::forLayer($baseObservability),

            Ruleset::forLayer($baseAudit)
                ->accesses($baseIdentity),

            Ruleset::forLayer($baseAccessControl)
                ->accesses($baseIdentity),

            Ruleset::forLayer($baseSettings),

            Ruleset::forLayer($baseFiles),

            Ruleset::forLayer($baseNotifications),

            Ruleset::forLayer($baseFeatureFlags),

            Ruleset::forLayer($baseOutboundWebhooks),

            // ModuleManager orchestrates across all other Foundation packages.
            // It may only access their Public contracts, not internal layers.
            Ruleset::forLayer($baseModuleManager)
                ->accesses(
                    $baseManifest,
                    $baseCapabilityRegistry,
                    $baseDependencyResolver,
                    $baseExtensionRegistry,
                ),
            Ruleset::forLayer($baseManifest)
                ->accesses($baseSharedKernel),
            Ruleset::forLayer($baseCapabilityRegistry)
                ->accesses($baseSharedKernel),
            Ruleset::forLayer($baseDependencyResolver)
                ->accesses($baseManifest),
            Ruleset::forLayer($baseExtensionRegistry),
        );
};
