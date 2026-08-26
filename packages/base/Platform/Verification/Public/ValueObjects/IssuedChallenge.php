<?php

declare(strict_types=1);

namespace Base\Platform\Verification\Public\ValueObjects;

final readonly class IssuedChallenge
{
    public function __construct(
        public VerificationReference $reference,
        public string $plaintextCode,
    ) {}
}
