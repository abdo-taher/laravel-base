<?php

declare(strict_types=1);

namespace Base\Platform\Files\Public\Contracts;

use Base\Platform\Files\Public\Exceptions\FileStorageFailed;
use Base\Platform\Files\Public\ValueObjects\FileVisibility;
use Base\Platform\Files\Public\ValueObjects\StorageKey;

interface FileWriter
{
    /**
     * @param  string|resource  $contents
     *
     * @throws FileStorageFailed
     */
    public function write(
        StorageKey|string $key,
        $contents,
        FileVisibility $visibility = FileVisibility::PRIVATE
    ): void;

    /**
     * Delete a file. Must be idempotent (succeed if file doesn't exist).
     *
     * @throws FileStorageFailed
     */
    public function delete(StorageKey|string $key): void;
}
