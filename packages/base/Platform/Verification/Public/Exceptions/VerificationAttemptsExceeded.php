<?php

declare(strict_types=1);

namespace Base\Platform\Verification\Public\Exceptions;

final class VerificationAttemptsExceeded extends VerificationException
{
    public function __construct()
    {
        parent::__construct('Maximum verification attempts exceeded.');
    }
}
