<?php

declare(strict_types=1);

namespace Base\Platform\Settings\Public\Exceptions;

use Throwable;

final class SettingPersistenceFailed extends SettingException
{
    public static function writeFailed(string $key, Throwable $previous): self
    {
        return new self("Failed to persist setting '{$key}'.", 0, $previous);
    }

    public static function readFailed(string $key, Throwable $previous): self
    {
        return new self("Failed to read setting '{$key}' from persistence.", 0, $previous);
    }
}
