<?php

declare(strict_types=1);

namespace Base\Platform\Verification\Public\Exceptions;

final class VerificationExpired extends VerificationException
{
    public function __construct()
    {
        parent::__construct('Verification challenge has expired.');
    }
}
