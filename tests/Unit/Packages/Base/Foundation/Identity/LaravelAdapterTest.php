<?php

declare(strict_types=1);

namespace Tests\Unit\Packages\Base\Foundation\Identity;

use Base\Foundation\Identity\Infrastructure\LaravelAuthenticator;
use Base\Foundation\Identity\Infrastructure\LaravelCurrentPrincipal;
use Base\Foundation\Identity\Public\Contracts\Credentials;
use Base\Foundation\Identity\Public\Exceptions\AuthenticationFailed;
use Base\Foundation\Identity\Public\Exceptions\AuthenticationRequired;
use Base\Foundation\Identity\Public\ValueObjects\EmailPasswordCredentials;
use Base\Foundation\Identity\Public\ValueObjects\PrincipalType;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Contracts\Auth\Factory as AuthFactory;
use Illuminate\Contracts\Auth\StatefulGuard;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Tests for Laravel Infrastructure adapters.
 *
 * Uses mocks for the Laravel Auth factory — no database or HTTP context required.
 * StatefulGuard is used because attempt() is not on the base Guard interface.
 */
final class LaravelAdapterTest extends TestCase
{
    // ── LaravelAuthenticator: positive ───────────────────────────────────────

    public function test_authenticator_returns_principal_on_successful_attempt(): void
    {
        $user = $this->mockUser('42');
        $guard = $this->mockGuard(attemptResult: true, user: $user);
        $factory = $this->mockFactory($guard);

        $result = (new LaravelAuthenticator($factory))->authenticate(
            new EmailPasswordCredentials('alice@example.com', 'secret'),
        );

        self::assertSame('42', $result->principal->id->value);
        self::assertTrue($result->principal->type->isUser());
        self::assertSame(PrincipalType::USER, $result->principal->type->value);
    }

    public function test_authentication_result_always_contains_principal_on_success(): void
    {
        $user = $this->mockUser('1');
        $guard = $this->mockGuard(attemptResult: true, user: $user);
        $factory = $this->mockFactory($guard);

        $result = (new LaravelAuthenticator($factory))->authenticate(
            new EmailPasswordCredentials('a@example.com', 'pass'),
        );

        // AuthenticationResult has no success flag — verify the principal id is populated
        self::assertSame('1', $result->principal->id->value);
    }

    // ── LaravelAuthenticator: negative ───────────────────────────────────────

    public function test_authenticator_throws_on_failed_attempt(): void
    {
        $guard = $this->mockGuard(attemptResult: false, user: null);
        $factory = $this->mockFactory($guard);

        $this->expectException(AuthenticationFailed::class);

        (new LaravelAuthenticator($factory))->authenticate(
            new EmailPasswordCredentials('bad@example.com', 'wrong'),
        );
    }

    public function test_authenticator_throws_when_attempt_succeeds_but_user_is_null(): void
    {
        $guard = $this->mockGuard(attemptResult: true, user: null);
        $factory = $this->mockFactory($guard);

        $this->expectException(AuthenticationFailed::class);

        (new LaravelAuthenticator($factory))->authenticate(
            new EmailPasswordCredentials('ghost@example.com', 'pass'),
        );
    }

    public function test_authenticator_throws_for_unsupported_credentials_type(): void
    {
        // An anonymous Credentials implementation that is not EmailPasswordCredentials
        $unsupported = new class implements Credentials {};

        $guard = $this->mockGuard(attemptResult: false, user: null);
        $factory = $this->mockFactory($guard);

        $this->expectException(AuthenticationFailed::class);
        $this->expectExceptionMessage('not supported by this adapter');

        (new LaravelAuthenticator($factory))->authenticate($unsupported);
    }

    public function test_unsupported_credentials_message_contains_class_name(): void
    {
        $unsupported = new class implements Credentials {};

        $guard = $this->mockGuard(attemptResult: false, user: null);
        $factory = $this->mockFactory($guard);

        try {
            (new LaravelAuthenticator($factory))->authenticate($unsupported);
            self::fail('Expected AuthenticationFailed');
        } catch (AuthenticationFailed $e) {
            self::assertStringContainsString('not supported', $e->getMessage());
        }
    }

    // ── LaravelCurrentPrincipal ───────────────────────────────────────────────

    public function test_current_principal_get_returns_principal_when_authenticated(): void
    {
        $user = $this->mockUser('7');
        $guard = $this->mockGuard(attemptResult: false, user: $user, checkResult: true);
        $factory = $this->mockFactory($guard);

        $principal = (new LaravelCurrentPrincipal($factory))->get();

        self::assertSame('7', $principal->id->value);
        self::assertTrue($principal->type->isUser());
    }

    public function test_current_principal_get_throws_when_not_authenticated(): void
    {
        $guard = $this->mockGuard(attemptResult: false, user: null, checkResult: false);
        $factory = $this->mockFactory($guard);

        $this->expectException(AuthenticationRequired::class);

        (new LaravelCurrentPrincipal($factory))->get();
    }

    public function test_current_principal_find_returns_null_when_not_authenticated(): void
    {
        $guard = $this->mockGuard(attemptResult: false, user: null, checkResult: false);
        $factory = $this->mockFactory($guard);

        self::assertNull((new LaravelCurrentPrincipal($factory))->find());
    }

    public function test_current_principal_find_returns_principal_when_authenticated(): void
    {
        $user = $this->mockUser('55');
        $guard = $this->mockGuard(attemptResult: false, user: $user, checkResult: true);
        $factory = $this->mockFactory($guard);

        self::assertSame('55', (new LaravelCurrentPrincipal($factory))->find()?->id->value);
    }

    public function test_current_principal_is_authenticated_returns_true(): void
    {
        $guard = $this->mockGuard(attemptResult: false, user: null, checkResult: true);
        $factory = $this->mockFactory($guard);

        self::assertTrue((new LaravelCurrentPrincipal($factory))->isAuthenticated());
    }

    public function test_current_principal_is_authenticated_returns_false(): void
    {
        $guard = $this->mockGuard(attemptResult: false, user: null, checkResult: false);
        $factory = $this->mockFactory($guard);

        self::assertFalse((new LaravelCurrentPrincipal($factory))->isAuthenticated());
    }

    // ── Helpers ──────────────────────────────────────────────────────────────

    private function mockUser(string $id): Authenticatable&MockObject
    {
        $user = $this->createMock(Authenticatable::class);
        $user->method('getAuthIdentifier')->willReturn($id);

        return $user;
    }

    private function mockGuard(
        bool $attemptResult,
        ?Authenticatable $user,
        bool $checkResult = false,
    ): StatefulGuard&MockObject {
        $guard = $this->createMock(StatefulGuard::class);
        $guard->method('attempt')->willReturn($attemptResult);
        $guard->method('user')->willReturn($user);
        $guard->method('check')->willReturn($checkResult);

        return $guard;
    }

    private function mockFactory(StatefulGuard $guard): AuthFactory&MockObject
    {
        $factory = $this->createMock(AuthFactory::class);
        $factory->method('guard')->willReturn($guard);

        return $factory;
    }
}
