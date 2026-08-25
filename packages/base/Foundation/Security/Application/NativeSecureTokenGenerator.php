<?php

declare(strict_types=1);

namespace Base\Foundation\Security\Application;

use Base\Foundation\Security\Public\Contracts\SecureTokenGenerator;
use Base\Foundation\Security\Public\ValueObjects\SensitiveValue;
use Exception;
use RuntimeException;

final class NativeSecureTokenGenerator implements SecureTokenGenerator
{
    /** @param int<1, max> $entropyBytes */
    public function generate(int $entropyBytes = 32): SensitiveValue
    {
        try {
            return new SensitiveValue(random_bytes($entropyBytes));
        } catch (Exception $e) {
            throw new RuntimeException('Failed to generate secure random bytes', 0, $e);
        }
    }

    /** @param int<1, max> $entropyBytes */
    public function generateHex(int $entropyBytes = 32): SensitiveValue
    {
        try {
            return new SensitiveValue(bin2hex(random_bytes($entropyBytes)));
        } catch (Exception $e) {
            throw new RuntimeException('Failed to generate secure random hex bytes', 0, $e);
        }
    }
}
