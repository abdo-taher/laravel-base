<?php

declare(strict_types=1);

namespace Base\Foundation\Configuration\Public\ValueObjects;

/**
 * Pairs a ConfigurationKey with a concrete default value for use in
 * a ConfigurationSource declaration.
 *
 * Sources register their owned keys together with their default values
 * so that the repository can satisfy optional keys even when no
 * higher-priority source provides them.
 *
 * No framework dependencies.
 */
final readonly class ConfigurationDefinition
{
    public function __construct(
        public ConfigurationKey $key,
        public mixed $value,
    ) {}
}
