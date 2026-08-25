<?php

declare(strict_types=1);

namespace Base\Platform\Files\Public\Exceptions;

use Base\Platform\Files\Public\ValueObjects\StorageKey;
use Throwable;

final class FileStorageFailed extends FileException
{
    public static function onRead(StorageKey|string $key, Throwable $previous): self
    {
        return new self("Failed to read file: {$key}", 0, $previous);
    }

    public static function onWrite(StorageKey|string $key, Throwable $previous): self
    {
        return new self("Failed to write file: {$key}", 0, $previous);
    }

    public static function onDelete(StorageKey|string $key, Throwable $previous): self
    {
        return new self("Failed to delete file: {$key}", 0, $previous);
    }

    public static function onCheck(StorageKey|string $key, Throwable $previous): self
    {
        return new self("Failed to check file existence: {$key}", 0, $previous);
    }
}
