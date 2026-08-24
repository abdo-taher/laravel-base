<?php

declare(strict_types=1);

namespace Base\Foundation\DependencyResolver\Public\Contracts;

use Base\Foundation\DependencyResolver\Public\ValueObjects\ResolutionResult;
use Base\Foundation\Manifest\Public\ValueObjects\Manifest;

interface DependencyResolver
{
    /** @param iterable<Manifest> $manifests */
    public function resolve(iterable $manifests): ResolutionResult;
}
