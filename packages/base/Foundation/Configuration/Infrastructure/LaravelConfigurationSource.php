<?php

declare(strict_types=1);

namespace Base\Foundation\Configuration\Infrastructure;

use Base\Foundation\Configuration\Public\Contracts\ConfigurationSource;
use Base\Foundation\Configuration\Public\ValueObjects\ConfigurationDefinition;
use Base\Foundation\Configuration\Public\ValueObjects\ConfigurationKey;
use Illuminate\Contracts\Config\Repository as LaravelConfig;

/**
 * Adapts Laravel's Config repository into a ConfigurationSource.
 *
 * Reads a declared set of keys from Laravel's configuration store and
 * exposes them at the project-configuration priority level (default: 10).
 *
 * Only keys explicitly declared in $declarations are read — this source
 * does not expose the full Laravel config to the Configuration Foundation.
 *
 * Lives in Infrastructure intentionally: Laravel coupling must not cross
 * into Public contracts or Application logic.
 */
final readonly class LaravelConfigurationSource implements ConfigurationSource
{
    /**
     * @param  list<ConfigurationKey>  $declarations  Keys to read from Laravel config.
     * @param  int  $sourcePriority  Defaults to 10 (project config level).
     */
    public function __construct(
        private LaravelConfig $laravel,
        private array $declarations,
        private int $sourcePriority = 10,
    ) {}

    public function priority(): int
    {
        return $this->sourcePriority;
    }

    /** @return list<ConfigurationDefinition> */
    public function definitions(): array
    {
        $definitions = [];

        foreach ($this->declarations as $key) {
            if ($this->laravel->has($key->path)) {
                $definitions[] = new ConfigurationDefinition(
                    key: $key,
                    value: $this->laravel->get($key->path),
                );
            }
        }

        return $definitions;
    }
}
