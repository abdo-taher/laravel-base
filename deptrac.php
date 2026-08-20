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
                ),
        );
};
