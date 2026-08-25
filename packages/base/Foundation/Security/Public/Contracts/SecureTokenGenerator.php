<?php

declare(strict_types=1);

namespace Base\Foundation\Security\Public\Contracts;

use Base\Foundation\Security\Public\ValueObjects\SensitiveValue;

/**
 * Generates cryptographically secure random tokens.
 */
interface SecureTokenGenerator
{
    /**
     * Generate a cryptographically secure token.
     *
     * @param int<1, max> $entropyBytes The amount of entropy (e.g., 32 for 256-bit).
     * @return SensitiveValue The generated secure token.
     */
    public function generate(int $entropyBytes = 32): SensitiveValue;

    /**
     * Generate a cryptographically secure token formatted as a hex string.
     *
     * @param int<1, max> $entropyBytes The amount of entropy (e.g., 32 for 256-bit).
     * @return SensitiveValue The generated secure token (length will be 2x entropy bytes).
     */
    public function generateHex(int $entropyBytes = 32): SensitiveValue;
}
