<?php

declare(strict_types=1);

namespace Base\Foundation\Security\Public\Utilities;

/**
 * Technical utility for constant-time comparison to prevent timing attacks.
 */
final class SecureCompare
{
    /**
     * Compare two strings in constant time.
     *
     * @param  string  $knownString  The expected string (e.g., hash or token)
     * @param  string  $userString  The user-provided string to test
     */
    public static function equals(string $knownString, string $userString): bool
    {
        return hash_equals($knownString, $userString);
    }
}
