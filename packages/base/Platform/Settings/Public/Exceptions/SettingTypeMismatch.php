<?php

declare(strict_types=1);

namespace Base\Platform\Settings\Public\Exceptions;

use Base\Platform\Settings\Public\ValueObjects\SettingKey;

final class SettingTypeMismatch extends SettingException
{
    public static function forKey(SettingKey|string $key, string $expected, string $actual): self
    {
        return new self("Type mismatch for setting '{$key}'. Expected {$expected}, got {$actual}.");
    }
}
