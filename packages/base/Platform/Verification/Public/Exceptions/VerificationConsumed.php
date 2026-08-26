<?php

declare(strict_types=1);

namespace Base\Platform\Verification\Public\Exceptions;

final class VerificationConsumed extends VerificationException
{
    public function __construct()
    {
        parent::__construct('Verification challenge has already been consumed.');
    }
}
