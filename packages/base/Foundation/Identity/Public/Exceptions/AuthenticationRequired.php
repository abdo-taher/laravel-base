<?php

declare(strict_types=1);

namespace Base\Foundation\Identity\Public\Exceptions;

use RuntimeException;

/**
 * Thrown when CurrentPrincipal::get() is called in an unauthenticated context.
 *
 * Fail-closed: accessing the current principal in a context where no
 * authenticated principal exists is always an explicit error.
 * Use CurrentPrincipal::find() or CurrentPrincipal::isAuthenticated()
 * when unauthenticated access is an expected code path.
 */
final class AuthenticationRequired extends RuntimeException
{
    public static function noAuthenticatedPrincipal(): self
    {
        return new self('No authenticated principal is available in the current context.');
    }
}
