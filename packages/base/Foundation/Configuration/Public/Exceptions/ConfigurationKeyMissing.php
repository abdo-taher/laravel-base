<?php

declare(strict_types=1);

namespace Base\Foundation\Configuration\Public\Exceptions;

use Base\Foundation\Configuration\Public\ValueObjects\ConfigurationKey;
use RuntimeException;

/**
 * Thrown when a required ConfigurationKey has no value in any
 * registered source.
 *
 * Fail-closed: required configuration absence is always an error.
 * Never substitute a silent default for a required key.
 */
final class ConfigurationKeyMissing extends RuntimeException
{
    public function __construct(public readonly ConfigurationKey $key)
    {
        parent::__construct(sprintf(
            'Required configuration key "%s" (type: %s) is not provided by any registered source.',
            $key->path,
            $key->type,
        ));
    }
}
