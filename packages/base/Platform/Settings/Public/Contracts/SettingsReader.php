<?php

declare(strict_types=1);

namespace Base\Platform\Settings\Public\Contracts;

use Base\Platform\Settings\Public\Exceptions\SettingNotDefined;
use Base\Platform\Settings\Public\Exceptions\SettingPersistenceFailed;
use Base\Platform\Settings\Public\Exceptions\SettingValueMissing;
use Base\Platform\Settings\Public\ValueObjects\SettingKey;

interface SettingsReader
{
    /**
     * Read a setting value.
     * Returns the persisted value, or the definition's default if not persisted.
     *
     * @throws SettingNotDefined if the setting key is not registered
     * @throws SettingValueMissing if the setting is required, not persisted, and has no default
     * @throws SettingPersistenceFailed on storage error
     */
    public function get(SettingKey|string $key): mixed;
}
