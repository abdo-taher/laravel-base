<?php

declare(strict_types=1);

namespace Base\Foundation\Security\Public\ValueObjects;

use JsonSerializable;
use LogicException;

/**
 * Wraps a sensitive string (e.g., password, API key) to prevent accidental
 * exposure in logs, exception traces, or JSON payloads.
 *
 * It deliberately breaks __toString() and serialization.
 * The underlying value must be explicitly requested via reveal().
 */
final readonly class SensitiveValue implements JsonSerializable
{
    public function __construct(private string $value) {}

    /**
     * Explicitly retrieve the unmasked secret.
     * Call this ONLY when the secret is about to be consumed securely.
     */
    public function reveal(): string
    {
        return $this->value;
    }

    /**
     * Prevents accidental string casting (e.g., in string interpolation).
     */
    public function __toString(): string
    {
        throw new LogicException('Cannot cast SensitiveValue to string. Use reveal() explicitly.');
    }

    /**
     * Masks the value during var_dump() or print_r().
     *
     * @return array<string, string>
     */
    public function __debugInfo(): array
    {
        return ['value' => '[HIDDEN]'];
    }

    /**
     * Masks the value during json_encode().
     */
    public function jsonSerialize(): string
    {
        return '[HIDDEN]';
    }

    /**
     * Masks the value during serialize().
     *
     * @return array<string, string>
     */
    public function __serialize(): array
    {
        return ['value' => '[HIDDEN]'];
    }

    /**
     * Prevent unserialization of actual secrets from potentially untrusted payloads.
     *
     * @param  array<string, mixed>  $data
     */
    public function __unserialize(array $data): void
    {
        throw new LogicException('SensitiveValue cannot be unserialized.');
    }
}
