<?php

declare(strict_types=1);

namespace Base\Platform\Devices\Public\ValueObjects;

final readonly class DevicePlatform
{
    public string $value;

    public function __construct(string $value)
    {
        $val = trim(strtolower($value));
        if ($val === '') {
            throw new \InvalidArgumentException('DevicePlatform cannot be empty');
        }
        $this->value = $val;
    }
}
