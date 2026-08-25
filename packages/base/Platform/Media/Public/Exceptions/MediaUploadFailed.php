<?php

declare(strict_types=1);

namespace Base\Platform\Media\Public\Exceptions;

use RuntimeException;
use Throwable;

final class MediaUploadFailed extends RuntimeException
{
    public static function validation(string $reason): self
    {
        return new self("Upload failed: {$reason}");
    }

    public static function transport(Throwable $previous): self
    {
        return new self('Media upload failed due to underlying storage error.', 0, $previous);
    }
}
