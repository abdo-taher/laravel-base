<?php

declare(strict_types=1);

namespace Base\Platform\Settings\Public\Contracts;

use Base\Platform\Settings\Public\ValueObjects\SettingDefinition;
use Base\Platform\Settings\Public\ValueObjects\SettingKey;

interface SettingsRegistry
{
    /**
     * Register a setting definition.
     */
    public function register(SettingDefinition $definition): void;

    /**
     * Get a registered setting definition by key.
     */
    public function getDefinition(SettingKey|string $key): ?SettingDefinition;

    /**
     * Get all registered definitions.
     *
     * @return array<string, SettingDefinition>
     */
    public function all(): array;
}
