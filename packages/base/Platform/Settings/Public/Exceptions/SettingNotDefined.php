<?php

declare(strict_types=1);

namespace Base\Platform\Settings\Public\Exceptions;

use Base\Platform\Settings\Public\ValueObjects\SettingKey;

final class SettingNotDefined extends SettingException
{
    public static function forKey(SettingKey|string $key): self
    {
        return new self("Setting '{$key}' is not defined in the registry.");
    }
}
