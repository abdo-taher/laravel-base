<?php

declare(strict_types=1);

namespace Base\Foundation\Identity\Infrastructure;

use Base\Foundation\Identity\Public\Contracts\Authenticator;
use Base\Foundation\Identity\Public\Contracts\Credentials;
use Base\Foundation\Identity\Public\Exceptions\AuthenticationFailed;
use Base\Foundation\Identity\Public\ValueObjects\AuthenticationResult;
use Base\Foundation\Identity\Public\ValueObjects\EmailPasswordCredentials;
use Base\Foundation\Identity\Public\ValueObjects\Principal;
use Base\Foundation\Identity\Public\ValueObjects\PrincipalId;
use Base\Foundation\Identity\Public\ValueObjects\PrincipalType;
use Illuminate\Contracts\Auth\Factory as AuthFactory;
use Illuminate\Contracts\Auth\StatefulGuard;

/**
 * Laravel-backed Authenticator implementation.
 *
 * Accepts only EmailPasswordCredentials. Any other Credentials
 * implementation is explicitly rejected — fail closed.
 *
 * Uses Laravel's StatefulGuard to attempt credential verification and
 * resolves the result into a framework-free Principal.
 *
 * Stays in Infrastructure intentionally. The Public Authenticator
 * contract remains framework-free.
 */
final readonly class LaravelAuthenticator implements Authenticator
{
    public function __construct(private AuthFactory $auth) {}

    public function authenticate(Credentials $credentials): AuthenticationResult
    {
        if (! ($credentials instanceof EmailPasswordCredentials)) {
            throw AuthenticationFailed::unsupportedCredentials($credentials::class);
        }

        $guard = $this->auth->guard();

        if (! ($guard instanceof StatefulGuard)) {
            throw AuthenticationFailed::invalidCredentials();
        }

        $attempted = $guard->attempt([
            'email' => $credentials->email,
            'password' => $credentials->password,
        ]);

        if (! $attempted) {
            throw AuthenticationFailed::invalidCredentials();
        }

        $user = $guard->user();

        if ($user === null) {
            throw AuthenticationFailed::invalidCredentials();
        }

        return AuthenticationResult::success(new Principal(
            id: new PrincipalId((string) $user->getAuthIdentifier()),
            type: PrincipalType::user(),
        ));
    }
}
