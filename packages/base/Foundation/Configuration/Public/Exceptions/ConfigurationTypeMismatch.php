<?php

declare(strict_types=1);

namespace Base\Foundation\Configuration\Public\Exceptions;

use Base\Foundation\Configuration\Public\ValueObjects\ConfigurationKey;
use RuntimeException;

/**
 * Thrown when a configuration value is present but does not match the
 * declared PHP type of the ConfigurationKey.
 *
 * Fail-closed: no silent coercion or type juggling is performed.
 */
final class ConfigurationTypeMismatch extends RuntimeException
{
    public function __construct(
        public readonly ConfigurationKey $key,
        public readonly string $actualType,
    ) {
        parent::__construct(sprintf(
            'Configuration key "%s" expects type %s but got %s.',
            $key->path,
            $key->type,
            $actualType,
        ));
    }
}
