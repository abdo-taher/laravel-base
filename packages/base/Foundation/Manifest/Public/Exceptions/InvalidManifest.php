<?php

declare(strict_types=1);

namespace Base\Foundation\Manifest\Public\Exceptions;

use InvalidArgumentException;

final class InvalidManifest extends InvalidArgumentException
{
    /** @param non-empty-list<string> $errors */
    public function __construct(private readonly array $errors)
    {
        parent::__construct("Manifest validation failed:\n- ".implode("\n- ", $errors));
    }

    /** @return non-empty-list<string> */
    public function errors(): array
    {
        return $this->errors;
    }
}
