<?php

declare(strict_types=1);

namespace Base\Platform\Verification\Public\ValueObjects;

final readonly class VerificationReference
{
    public function __construct(
        public string $value,
    ) {
        if (! str_starts_with($this->value, 'ver_')) {
            throw new \InvalidArgumentException('Invalid reference format');
        }
    }

    public static function generate(): self
    {
        return new self('ver_'.bin2hex(random_bytes(16)));
    }
}
