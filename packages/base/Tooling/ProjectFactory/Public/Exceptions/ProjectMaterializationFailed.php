<?php

declare(strict_types=1);

namespace Base\Tooling\ProjectFactory\Public\Exceptions;

use RuntimeException;

class ProjectMaterializationFailed extends RuntimeException
{
    public static function destinationExists(string $path): self
    {
        return new self(sprintf('Destination already exists: %s', $path));
    }

    public static function destinationRace(string $path): self
    {
        return new self(sprintf('Destination was created during staging execution: %s', $path));
    }

    public static function outputConflict(string $path): self
    {
        return new self(sprintf('Planned operations conflict at output path: %s', $path));
    }

    public static function unsafeSource(string $reason): self
    {
        return new self(sprintf('Unsafe materialization source: %s', $reason));
    }

    public static function symlinkRejected(string $path): self
    {
        return new self(sprintf('Source contains a symlink which is prohibited: %s', $path));
    }

    public static function atomicPublishFailed(string $staging, string $destination): self
    {
        return new self(sprintf('Failed to atomically rename %s to %s', $staging, $destination));
    }
}
