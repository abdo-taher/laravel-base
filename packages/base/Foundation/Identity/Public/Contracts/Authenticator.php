<?php

declare(strict_types=1);

namespace Base\Foundation\Identity\Public\Contracts;

use Base\Foundation\Identity\Public\Exceptions\AuthenticationFailed;
use Base\Foundation\Identity\Public\ValueObjects\AuthenticationResult;

/**
 * Credential verification boundary.
 *
 * Verifies the supplied credentials and returns a successful
 * AuthenticationResult, or throws AuthenticationFailed.
 *
 * Fail-closed invariants:
 *   - An AuthenticationResult always contains an authenticated Principal.
 *   - Failed or unsupported credentials always throw AuthenticationFailed.
 *   - No null return. No boolean flag. No silent permissive fallback.
 *
 * Infrastructure adapters must explicitly reject unsupported Credentials
 * implementations and throw AuthenticationFailed — never silently pass.
 *
 * The implementation may use any backend (database, external IdP,
 * LDAP directory, etc.). The Public contract remains mechanism-agnostic
 * and framework-free.
 *
 * No framework dependencies.
 */
interface Authenticator
{
    /**
     * @throws AuthenticationFailed When credentials are invalid, the
     *                              identity cannot be found, or the
     *                              Credentials type is not supported
     *                              by this adapter.
     */
    public function authenticate(Credentials $credentials): AuthenticationResult;
}
