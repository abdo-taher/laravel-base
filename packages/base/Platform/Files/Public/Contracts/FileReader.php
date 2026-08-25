<?php

declare(strict_types=1);

namespace Base\Platform\Files\Public\Contracts;

use Base\Platform\Files\Public\Exceptions\FileNotFound;
use Base\Platform\Files\Public\Exceptions\FileStorageFailed;
use Base\Platform\Files\Public\ValueObjects\StorageKey;

interface FileReader
{
    /**
     * @throws FileNotFound
     * @throws FileStorageFailed
     */
    public function read(StorageKey|string $key): string;

    /**
     * @return resource
     *
     * @throws FileNotFound
     * @throws FileStorageFailed
     */
    public function readStream(StorageKey|string $key);

    /**
     * @throws FileStorageFailed
     */
    public function exists(StorageKey|string $key): bool;
}
