<?php

declare(strict_types=1);

namespace Base\Platform\Settings\Public\Exceptions;

use Base\Platform\Settings\Public\ValueObjects\SettingKey;

final class SettingValueMissing extends SettingException
{
    public static function forRequired(SettingKey|string $key): self
    {
        return new self("Setting '{$key}' is required but has no value and no default.");
    }
}
