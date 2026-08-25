<?php

declare(strict_types=1);

namespace Base\Platform\Media\Public\Exceptions;

use RuntimeException;

final class MediaReferenceNotFound extends RuntimeException
{
    public static function fromString(string $reference): self
    {
        return new self("Media reference {$reference} not found.");
    }
}
