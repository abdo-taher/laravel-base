<?php

declare(strict_types=1);

namespace Base\Platform\Verification\Public\Contracts;

use Base\Platform\Verification\Public\ValueObjects\VerificationReference;

interface VerificationVerifier
{
    public function verify(
        VerificationReference $reference,
        string $code
    ): void;
}
