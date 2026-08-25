<?php

declare(strict_types=1);

namespace Base\Foundation\Observability\Public\ValueObjects;

use InvalidArgumentException;

/**
 * Ensures structured log contexts do not accidentally capture or leak
 * domain models, resources, or closures.
 *
 * IMPORTANT: This rejects objects to prevent accidental serialization
 * (e.g. Eloquent models), but it DOES NOT automatically redact scalar secrets.
 * Callers MUST ensure that the following are NOT included in this context:
 * - password
 * - token
 * - api_key
 * - authorization header
 *
 * No framework dependencies.
 */
final readonly class LogContext
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
            if (! is_string($key)) {
                throw new InvalidArgumentException(
                    sprintf(
                        'LogContext keys must be strings. Invalid key at path "%s".',
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
                'Invalid LogContext value at path "%s". Only scalars, nulls, and nested arrays are permitted. Got: %s',
                $path,
                get_debug_type($value)
            )
        );
    }
}
