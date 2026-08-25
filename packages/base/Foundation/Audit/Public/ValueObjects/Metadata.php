<?php

declare(strict_types=1);

namespace Base\Foundation\Audit\Public\ValueObjects;

use InvalidArgumentException;

/**
 * Immutable metadata associated with an audit event.
 *
 * Rejects arbitrary PHP objects, resources, and closures to prevent
 * accidental domain model leakage or secret capture (e.g. avoiding
 * serialization of framework models with hidden attributes).
 *
 * IMPORTANT: This mechanism prevents unsafe object/model serialization
 * but does NOT guarantee secret safety for scalar values. Callers MUST
 * explicitly avoid including:
 * - passwords
 * - tokens
 * - API keys
 * - authorization headers
 * - any other secrets
 *
 * Only null, bool, int, float, string, and nested arrays with string keys
 * are permitted.
 *
 * No framework dependencies.
 */
final readonly class Metadata
{
    /**
     * @param array<string, mixed> $values
     */
    public function __construct(public array $values = [])
    {
        $this->validateValues($values, '');
    }

    /**
     * @param array<array-key, mixed> $data
     */
    private function validateValues(array $data, string $currentPath): void
    {
        foreach ($data as $key => $value) {
            if (! is_string($key)) {
                throw new InvalidArgumentException(
                    sprintf(
                        'Invalid metadata key type at path "%s". Only string keys are allowed in metadata arrays, got %s.',
                        $currentPath === '' ? 'root' : $currentPath,
                        get_debug_type($key)
                    )
                );
            }

            $path = $currentPath === '' ? $key : $currentPath . '.' . $key;
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
                'Invalid metadata value at path "%s". Only scalars, nulls, and nested arrays are permitted. Got: %s',
                $path,
                get_debug_type($value)
            )
        );
    }
}
