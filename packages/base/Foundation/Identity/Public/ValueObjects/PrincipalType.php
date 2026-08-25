<?php

declare(strict_types=1);

namespace Base\Foundation\Identity\Public\ValueObjects;

use InvalidArgumentException;

/**
 * Named identity type.
 *
 * Describes the kind of actor a principal represents. Base types are
 * defined as constants. Project extensions may introduce additional
 * types by constructing a PrincipalType with a custom string.
 *
 * No framework dependencies.
 */
final readonly class PrincipalType
{
    /** A human user with credentials. */
    public const string USER = 'user';

    /** An internal system actor (e.g., scheduled jobs, background workers). */
    public const string SYSTEM = 'system';

    /** An API key / machine-to-machine principal. */
    public const string API_KEY = 'api-key';

    public function __construct(public string $value)
    {
        if (trim($value) === '') {
            throw new InvalidArgumentException('PrincipalType must be a non-empty string.');
        }
    }

    public static function user(): self
    {
        return new self(self::USER);
    }

    public static function system(): self
    {
        return new self(self::SYSTEM);
    }

    public static function apiKey(): self
    {
        return new self(self::API_KEY);
    }

    public function equals(self $other): bool
    {
        return $this->value === $other->value;
    }

    public function isUser(): bool
    {
        return $this->value === self::USER;
    }

    public function isSystem(): bool
    {
        return $this->value === self::SYSTEM;
    }
}
