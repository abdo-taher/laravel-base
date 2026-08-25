<?php

declare(strict_types=1);

namespace Base\Platform\Media\Public\ValueObjects;

use Base\Platform\Media\Public\Exceptions\InvalidMediaReference;

final class MediaReference
{
    private function __construct(public readonly string $value)
    {
        if (! preg_match('/^med_[a-zA-Z0-9]+$/', $value)) {
            throw InvalidMediaReference::fromString($value);
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
