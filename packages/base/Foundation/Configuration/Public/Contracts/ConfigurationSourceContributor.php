<?php

declare(strict_types=1);

namespace Base\Foundation\Configuration\Public\Contracts;

/**
 * Extension hook: contributes additional ConfigurationSource instances
 * to the repository without modifying Base package internals.
 *
 * Project extensions and modules implement this interface and return
 * one or more sources. The service provider collects all registered
 * contributors and adds their sources to the LayeredConfigurationRepository.
 *
 * Compatible with the ExtensionRegistry contributor model but does not
 * depend on ExtensionRegistry. Wiring to the full extension runtime is
 * deferred to post-B3.
 *
 * No framework dependencies.
 */
interface ConfigurationSourceContributor
{
    /**
     * @return list<ConfigurationSource>
     */
    public function sources(): array;
}
