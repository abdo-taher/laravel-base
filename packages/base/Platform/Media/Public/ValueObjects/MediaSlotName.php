<?php

declare(strict_types=1);

namespace Base\Platform\Media\Public\ValueObjects;

final class MediaSlotName
{
    private function __construct(public readonly string $value)
    {
        if (trim($value) === '') {
            throw new \InvalidArgumentException('MediaSlotName cannot be empty.');
        }
    }

    public static function fromString(string $value): self
    {
        return new self($value);
    }
}
