<?php

declare(strict_types=1);

namespace Base\Foundation\Configuration;

use Base\Foundation\Configuration\Application\LayeredConfigurationRepository;
use Base\Foundation\Configuration\Public\Contracts\ConfigurationRepository;
use Base\Foundation\Configuration\Public\Contracts\ConfigurationSourceContributor;
use Illuminate\Support\ServiceProvider;

/**
 * Configuration Foundation package service provider.
 *
 * Ownership: base-owned
 * Category:  Foundation
 *
 * Responsibilities:
 *   - Bind ConfigurationRepository to LayeredConfigurationRepository.
 *   - Collect all registered ConfigurationSourceContributor instances
 *     and add their sources to the repository.
 *
 * Package defaults are registered by each package's own service
 * provider after this one boots.
 *
 * No automatic source discovery — contributors are registered
 * explicitly via the Laravel service container tag
 * "configuration.source.contributor".
 */
final class ConfigurationServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(
            ConfigurationRepository::class,
            LayeredConfigurationRepository::class,
        );
    }

    public function boot(): void
    {
        $repository = $this->app->make(ConfigurationRepository::class);

        assert($repository instanceof LayeredConfigurationRepository);

        /** @var list<ConfigurationSourceContributor> $contributors */
        $contributors = $this->app->tagged('configuration.source.contributor');

        foreach ($contributors as $contributor) {
            foreach ($contributor->sources() as $source) {
                $repository->addSource($source);
            }
        }
    }
}
