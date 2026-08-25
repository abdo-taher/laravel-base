<?php

declare(strict_types=1);

namespace Base\Foundation\Identity\Public\ValueObjects;

use Base\Foundation\Identity\Public\Contracts\Credentials;
use InvalidArgumentException;

/**
 * Email and password credential pair.
 *
 * Implements the Credentials marker contract for email-based
 * authentication. The password is held as a plain string — the
 * Authenticator implementation is responsible for secure verification
 * (e.g., bcrypt comparison). This package does not hash or store
 * passwords.
 *
 * No framework dependencies.
 */
final readonly class EmailPasswordCredentials implements Credentials
{
    public function __construct(
        public string $email,
        public string $password,
    ) {
        if (trim($email) === '') {
            throw new InvalidArgumentException('Email must be a non-empty string.');
        }

        if ($password === '') {
            throw new InvalidArgumentException('Password must be a non-empty string.');
        }
    }
}
