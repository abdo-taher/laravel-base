<?php

declare(strict_types=1);

namespace Base\Platform\Settings\Public\Contracts;

use Base\Platform\Settings\Public\Exceptions\SettingNotDefined;
use Base\Platform\Settings\Public\Exceptions\SettingPersistenceFailed;
use Base\Platform\Settings\Public\Exceptions\SettingTypeMismatch;
use Base\Platform\Settings\Public\ValueObjects\SettingKey;

interface SettingsWriter
{
    /**
     * Write a setting value, validating it against its definition.
     *
     * @throws SettingNotDefined if the setting key is not registered
     * @throws SettingTypeMismatch if the value does not match the definition's type
     * @throws SettingPersistenceFailed on storage error
     */
    public function set(SettingKey|string $key, mixed $value): void;

    /**
     * Delete the persisted setting override, restoring it to its default value on next read.
     *
     * @throws SettingPersistenceFailed on storage error
     */
    public function reset(SettingKey|string $key): void;
}
