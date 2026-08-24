<?php

declare(strict_types=1);

namespace Base\Foundation\ModuleManager\Public\Exceptions;

use RuntimeException;

/**
 * Thrown when manifest discovery cannot be completed for a search path.
 *
 * Fails closed: any unreadable or invalid manifest in a scanned path
 * surfaces immediately rather than being silently skipped.
 */
final class ModuleDiscoveryFailed extends RuntimeException
{
    public static function unreadablePath(string $path): self
    {
        return new self(sprintf('Module search path is not readable: %s', $path));
    }

    public static function invalidManifest(string $path, string $reason): self
    {
        return new self(sprintf('Invalid manifest at %s: %s', $path, $reason));
    }
}
