<?php

declare(strict_types=1);

namespace Base\Platform\Verification\Public\ValueObjects;

final readonly class VerificationPurpose
{
    public function __construct(
        public string $value,
    ) {
        if (trim($this->value) === '') {
            throw new \InvalidArgumentException('Purpose cannot be empty');
        }
        if (preg_match('/[[:cntrl:]]/', $this->value)) {
            throw new \InvalidArgumentException('Purpose cannot contain control characters');
        }
    }
}
