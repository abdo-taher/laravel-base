<?php

declare(strict_types=1);

namespace Base\Foundation\Health\Public\ValueObjects;

use InvalidArgumentException;

/**
 * Immutable metadata for health check results.
 *
 * Enforces strict structured validation to prevent domain model leakage.
 * Rejects objects, closures, resources, and non-string keys.
 *
 * IMPORTANT: This DOES NOT automatically redact scalar secrets.
 * Callers MUST ensure that the following are NOT included:
 * - password
 * - token
 * - api_key
 * - connection string
 * - authorization header
 */
final readonly class HealthMetadata
{
    /**
     * @param  array<string, mixed>  $values
     */
    public function __construct(public array $values = [])
    {
        $this->validateValues($values, '');
    }

    /**
     * @param  array<array-key, mixed>  $data
     */
    private function validateValues(array $data, string $currentPath): void
    {
        foreach ($data as $key => $value) {
            if (! is_string($key) || trim($key) === '') {
                throw new InvalidArgumentException(
                    sprintf(
                        'HealthMetadata keys must be non-empty strings. Invalid key at path "%s".',
                        $currentPath === '' ? 'root' : $currentPath
                    )
                );
            }

            $path = $currentPath === '' ? $key : $currentPath.'.'.$key;
            $this->validateValue($value, $path);
        }
    }

    private function validateValue(mixed $value, string $path): void
    {
        if ($value === null || is_scalar($value)) {
            return;
        }

        if (is_array($value)) {
            $this->validateValues($value, $path);

            return;
        }

        throw new InvalidArgumentException(
            sprintf(
                'Invalid HealthMetadata value at path "%s". Only scalars, nulls, and nested arrays are permitted. Got: %s',
                $path,
                get_debug_type($value)
            )
        );
    }
}
