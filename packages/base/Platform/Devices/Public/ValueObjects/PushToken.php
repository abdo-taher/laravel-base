<?php

declare(strict_types=1);

namespace Base\Platform\Devices\Public\ValueObjects;

final readonly class PushToken
{
    public function __construct(
        public string $value,
    ) {
        if (trim($this->value) === '') {
            throw new \InvalidArgumentException('PushToken cannot be empty');
        }
    }
}
