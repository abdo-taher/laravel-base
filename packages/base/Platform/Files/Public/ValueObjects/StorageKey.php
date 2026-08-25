<?php

declare(strict_types=1);

namespace Base\Platform\Files\Public\ValueObjects;

use Base\Platform\Files\Public\Exceptions\InvalidStorageKey;

final readonly class StorageKey
{
    public string $value;

    public function __construct(string $value)
    {
        if (trim($value) === '') {
            throw InvalidStorageKey::dueTo('Key cannot be empty or whitespace.');
        }

        if (str_contains($value, "\0")) {
            throw InvalidStorageKey::dueTo('Key cannot contain null bytes.');
        }

        if (str_contains($value, '../') || str_contains($value, '..\\')) {
            throw InvalidStorageKey::dueTo('Key cannot contain directory traversal segments.');
        }

        if (str_starts_with($value, '/') || str_starts_with($value, '\\')) {
            throw InvalidStorageKey::dueTo('Key must not be an absolute path.');
        }

        if (preg_match('/^[a-zA-Z]:[\\\\\/]/', $value)) {
            throw InvalidStorageKey::dueTo('Key must not be an absolute Windows path.');
        }

        $this->value = $value;
    }

    public function __toString(): string
    {
        return $this->value;
    }
}
