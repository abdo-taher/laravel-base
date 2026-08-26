<?php

declare(strict_types=1);

namespace Base\Platform\Devices\Public\ValueObjects;

final readonly class DeviceId
{
    public function __construct(
        public string $value,
    ) {
        if (trim($this->value) === '') {
            throw new \InvalidArgumentException('DeviceId cannot be empty');
        }
    }
}
