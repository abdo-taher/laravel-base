<?php

declare(strict_types=1);

namespace Base\Platform\Files\Public\Exceptions;

use Base\Platform\Files\Public\ValueObjects\StorageKey;

final class FileNotFound extends FileException
{
    public static function forKey(StorageKey|string $key): self
    {
        return new self("File not found for key: {$key}");
    }
}
