<?php

declare(strict_types=1);

namespace Base\Foundation\Identity\Public\Exceptions;

use RuntimeException;

/**
 * Thrown when authentication fails due to invalid or unrecognised credentials,
 * or when the supplied Credentials type is not supported by the adapter.
 *
 * Fail-closed: the Authenticator always throws this exception on failure.
 * No null return. No boolean flag. No permissive default.
 *
 * The message must not reveal whether the identifier or the credential
 * was incorrect (prevents user enumeration).
 */
final class AuthenticationFailed extends RuntimeException
{
    public static function invalidCredentials(): self
    {
        return new self('Authentication failed: invalid credentials.');
    }

    /**
     * Thrown when an Infrastructure adapter receives a Credentials
     * implementation it does not support. Fail closed.
     */
    public static function unsupportedCredentials(string $credentialsClass): self
    {
        return new self(sprintf(
            'Authentication failed: credential type %s is not supported by this adapter.',
            $credentialsClass,
        ));
    }
}
