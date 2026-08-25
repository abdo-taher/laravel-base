<?php

declare(strict_types=1);

namespace Base\Platform\Settings\Public\ValueObjects;

use InvalidArgumentException;

final readonly class SettingKey
{
    public string $value;

    public function __construct(string $value)
    {
        $value = trim($value);
        if ($value === '') {
            throw new InvalidArgumentException('Setting key cannot be empty.');
        }

        if (preg_match('/^[a-zA-Z0-9_.-]+$/', $value) !== 1) {
            throw new InvalidArgumentException('Setting key can only contain alphanumeric characters, dots, dashes, and underscores.');
        }

        $this->value = $value;
    }

    public function __toString(): string
    {
        return $this->value;
    }
}
