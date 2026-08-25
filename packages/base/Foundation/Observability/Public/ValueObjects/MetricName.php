<?php

declare(strict_types=1);

namespace Base\Foundation\Observability\Public\ValueObjects;

use InvalidArgumentException;

/**
 * Ensures metric names are well-formed strings.
 * Business modules own their metric nomenclature.
 */
final readonly class MetricName
{
    public function __construct(public string $value)
    {
        if (trim($value) === '') {
            throw new InvalidArgumentException('MetricName cannot be empty.');
        }
    }

    public function toString(): string
    {
        return $this->value;
    }
}
