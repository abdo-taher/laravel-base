<?php

declare(strict_types=1);

namespace Base\Platform\Verification\Public\Exceptions;

final class VerificationNotFound extends VerificationException
{
    public function __construct()
    {
        parent::__construct('Verification challenge not found or invalid.');
    }
}
