<?php

declare(strict_types=1);

namespace Base\Platform\Files\Public\Exceptions;

final class InvalidStorageKey extends FileException
{
    public static function dueTo(string $reason): self
    {
        return new self("Invalid storage key: {$reason}");
    }
}
