<?php

declare(strict_types=1);

namespace Base\Platform\Settings\Application;

use Base\Platform\Settings\Public\Contracts\SettingsRegistry;
use Base\Platform\Settings\Public\ValueObjects\SettingDefinition;
use Base\Platform\Settings\Public\ValueObjects\SettingKey;

final class InMemorySettingsRegistry implements SettingsRegistry
{
    /** @var array<string, SettingDefinition> */
    private array $definitions = [];

    public function register(SettingDefinition $definition): void
    {
        $this->definitions[$definition->key->value] = $definition;
    }

    public function getDefinition(SettingKey|string $key): ?SettingDefinition
    {
        $keyString = $key instanceof SettingKey ? $key->value : $key;

        return $this->definitions[$keyString] ?? null;
    }

    public function all(): array
    {
        return $this->definitions;
    }
}
