<?php

declare(strict_types=1);

namespace Base\Foundation\Configuration\Public\Contracts;

use Base\Foundation\Configuration\Public\ValueObjects\ConfigurationDefinition;

/**
 * A single configuration source.
 *
 * Sources are registered with an explicit integer priority. The
 * Configuration Foundation composes all registered sources in priority
 * order (higher priority wins).
 *
 * Accepted priority conventions (not enforced as constants):
 *   1   — package defaults
 *   10  — project configuration
 *   50  — extension overrides
 *   100 — environment / runtime overrides
 *
 * A source must not read secrets directly. Secret retrieval is a
 * separate future capability.
 *
 * No framework dependencies.
 */
interface ConfigurationSource
{
    /**
     * The priority of this source. Higher value = higher precedence.
     */
    public function priority(): int;

    /**
     * The configuration definitions this source provides.
     *
     * Each definition pairs a typed key with a concrete value.
     * Return only the keys this source explicitly owns.
     *
     * @return list<ConfigurationDefinition>
     */
    public function definitions(): array;
}
