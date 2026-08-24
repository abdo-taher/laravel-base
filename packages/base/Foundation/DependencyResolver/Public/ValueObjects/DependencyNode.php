<?php

declare(strict_types=1);

namespace Base\Foundation\DependencyResolver\Public\ValueObjects;

use Base\Foundation\Manifest\Public\ValueObjects\Manifest;

final readonly class DependencyNode
{
    public function __construct(public Manifest $manifest) {}

    public function name(): string
    {
        return $this->manifest->name;
    }

    public function category(): string
    {
        return $this->manifest->category;
    }
}
