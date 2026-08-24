<?php

declare(strict_types=1);

namespace Base\Foundation\Manifest\Public\Contracts;

use Base\Foundation\Manifest\Public\ValueObjects\Manifest;

interface ManifestReader
{
    public function read(string $path): Manifest;
}
