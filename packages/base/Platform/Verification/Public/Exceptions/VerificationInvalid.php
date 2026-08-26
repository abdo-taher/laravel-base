<?php

declare(strict_types=1);

namespace Base\Platform\Verification\Public\Exceptions;

final class VerificationInvalid extends VerificationException
{
    public function __construct()
    {
        parent::__construct('Invalid verification code.');
    }
}
