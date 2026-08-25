<?php

declare(strict_types=1);

namespace Base\Platform\Media\Public\ValueObjects;

final class MediaAccessScope
{
    private function __construct(public readonly string $value)
    {
        if (trim($value) === '') {
            throw new \InvalidArgumentException('MediaAccessScope cannot be empty.');
        }
    }

    public static function fromString(string $value): self
    {
        return new self($value);
    }

    public function equals(self $other): bool
    {
        return $this->value === $other->value;
    }
}
