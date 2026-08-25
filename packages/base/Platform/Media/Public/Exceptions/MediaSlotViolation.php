<?php

declare(strict_types=1);

namespace Base\Platform\Media\Public\Exceptions;

use RuntimeException;

final class MediaSlotViolation extends RuntimeException
{
    public static function invalidMime(string $reference, string $expected): self
    {
        return new self("Media reference {$reference} violates slot MIME constraints. Expected: {$expected}");
    }

    public static function invalidSize(string $reference, int $max): self
    {
        return new self("Media reference {$reference} violates slot size limit of {$max} bytes.");
    }

    public static function invalidCardinality(string $slot, string $reason): self
    {
        return new self("Slot {$slot} cardinality violation: {$reason}");
    }

    public static function duplicateReference(string $reference): self
    {
        return new self("Duplicate media reference {$reference} in sync payload.");
    }

    public static function scopeMismatch(string $reference): self
    {
        return new self("Media reference {$reference} cannot be attached. Scope mismatch or not TEMPORARY.");
    }
}
