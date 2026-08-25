<?php

declare(strict_types=1);

namespace Base\Foundation\Identity\Infrastructure;

use Base\Foundation\Identity\Public\Contracts\CurrentPrincipal;
use Base\Foundation\Identity\Public\Exceptions\AuthenticationRequired;
use Base\Foundation\Identity\Public\ValueObjects\Principal;
use Base\Foundation\Identity\Public\ValueObjects\PrincipalId;
use Base\Foundation\Identity\Public\ValueObjects\PrincipalType;
use Illuminate\Contracts\Auth\Factory as AuthFactory;

/**
 * Laravel-backed CurrentPrincipal implementation.
 *
 * Reads the authenticated user from Laravel's Auth guard and maps it
 * to a framework-free Principal value object.
 *
 * Stays in Infrastructure intentionally. The Public CurrentPrincipal
 * contract remains Laravel-free.
 */
final readonly class LaravelCurrentPrincipal implements CurrentPrincipal
{
    public function __construct(private AuthFactory $auth) {}

    public function get(): Principal
    {
        $principal = $this->find();

        if ($principal === null) {
            throw AuthenticationRequired::noAuthenticatedPrincipal();
        }

        return $principal;
    }

    public function find(): ?Principal
    {
        $user = $this->auth->guard()->user();

        if ($user === null) {
            return null;
        }

        return new Principal(
            id: new PrincipalId((string) $user->getAuthIdentifier()),
            type: PrincipalType::user(),
        );
    }

    public function isAuthenticated(): bool
    {
        return $this->auth->guard()->check();
    }
}
