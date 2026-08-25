<?php

declare(strict_types=1);

namespace Base\Foundation\Identity\Public\Contracts;

/**
 * Marker contract for authentication credentials.
 *
 * Implementations carry the data required by a specific authentication
 * mechanism. The Authenticator contract accepts Credentials so that
 * future adapters (API key, token, OAuth code, etc.) can be introduced
 * without changing the Public Authenticator signature.
 *
 * Infrastructure adapters must explicitly type-check the supplied
 * Credentials implementation and throw AuthenticationFailed for any
 * unsupported type — fail closed.
 *
 * No framework dependencies. No methods required.
 */
interface Credentials {}
