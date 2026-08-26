<?php

declare(strict_types=1);

namespace Base\Platform\Verification\Public\ValueObjects;

final readonly class VerificationTarget
{
    public function __construct(
        public string $type,
        public string $key,
    ) {
        if (trim($this->type) === '') {
            throw new \InvalidArgumentException('Target type cannot be empty');
        }
        if (trim($this->key) === '') {
            throw new \InvalidArgumentException('Target key cannot be empty');
        }
    }
}
