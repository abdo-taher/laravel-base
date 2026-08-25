<?php

declare(strict_types=1);

namespace Base\Foundation\Configuration\Public\Contracts;

use Base\Foundation\Configuration\Public\Exceptions\ConfigurationKeyMissing;
use Base\Foundation\Configuration\Public\Exceptions\ConfigurationTypeMismatch;
use Base\Foundation\Configuration\Public\ValueObjects\ConfigurationKey;

/**
 * The primary typed configuration read contract.
 *
 * Consumers inject this interface and use typed ConfigurationKey objects
 * to retrieve configuration values. No framework coupling, no global
 * state, no config() or Config:: calls cross this boundary.
 *
 * Precedence is determined by the registered ConfigurationSource
 * priorities. Consumers do not need to know which source wins.
 */
interface ConfigurationRepository
{
    /**
     * Retrieve a typed configuration value.
     *
     * @throws ConfigurationKeyMissing When required=true and no source
     *                                 provides the key.
     * @throws ConfigurationTypeMismatch When a value is present but does
     *                                   not match the key's declared type.
     */
    public function get(ConfigurationKey $key): mixed;

    /**
     * Returns true when any registered source provides the key.
     * Never throws.
     */
    public function has(ConfigurationKey $key): bool;

    /**
     * Retrieve a value, returning $fallback when absent.
     *
     * The $fallback is the caller's responsibility and is not
     * type-checked against the key's declared type.
     * Never throws for absent keys; may throw ConfigurationTypeMismatch
     * if a value is present but type-mismatched.
     *
     * @throws ConfigurationTypeMismatch
     */
    public function getOrDefault(ConfigurationKey $key, mixed $fallback): mixed;
}
