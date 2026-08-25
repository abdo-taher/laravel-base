<?php

declare(strict_types=1);

namespace Base\Foundation\Observability\Public\ValueObjects;

use InvalidArgumentException;

/**
 * Ensures metric tags are strictly key/value pairs of scalars.
 *
 * Prevents arbitrary object serialization or nested structures
 * inside telemetry metadata.
 */
final readonly class MetricTags
{
    /**
     * @param  array<string, string|int|float|bool>  $values
     */
    public function __construct(public array $values = [])
    {
        $this->validateValues($values);
    }

    /**
     * @param  array<array-key, mixed>  $data
     */
    private function validateValues(array $data): void
    {
        foreach ($data as $key => $value) {
            if (! is_string($key) || trim($key) === '') {
                throw new InvalidArgumentException('Metric tag keys must be non-empty strings.');
            }

            if (! is_scalar($value)) {
                throw new InvalidArgumentException(
                    sprintf(
                        'Metric tag values must be scalar (string, int, float, bool). Got: %s for key "%s"',
                        get_debug_type($value),
                        $key
                    )
                );
            }
        }
    }
}
