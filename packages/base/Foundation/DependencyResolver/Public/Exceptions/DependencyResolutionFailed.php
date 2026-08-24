<?php

declare(strict_types=1);

namespace Base\Foundation\DependencyResolver\Public\Exceptions;

use RuntimeException;

final class DependencyResolutionFailed extends RuntimeException
{
    /** @param non-empty-list<string> $errors */
    public function __construct(private readonly array $errors)
    {
        parent::__construct("Dependency resolution failed:\n- ".implode("\n- ", $errors));
    }

    /** @return non-empty-list<string> */
    public function errors(): array
    {
        return $this->errors;
    }
}
