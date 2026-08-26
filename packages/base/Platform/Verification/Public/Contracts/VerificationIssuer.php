<?php

declare(strict_types=1);

namespace Base\Platform\Verification\Public\Contracts;

use Base\Platform\Verification\Public\ValueObjects\IssuedChallenge;
use Base\Platform\Verification\Public\ValueObjects\VerificationPurpose;
use Base\Platform\Verification\Public\ValueObjects\VerificationTarget;

interface VerificationIssuer
{
    public function issue(
        VerificationTarget $target,
        VerificationPurpose $purpose,
        int $length = 6,
        int $ttlSeconds = 900,
        int $maxAttempts = 3
    ): IssuedChallenge;
}
