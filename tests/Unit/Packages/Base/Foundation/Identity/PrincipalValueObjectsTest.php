<?php

declare(strict_types=1);

namespace Tests\Unit\Packages\Base\Foundation\Identity;

use Base\Foundation\Identity\Public\Contracts\Credentials;
use Base\Foundation\Identity\Public\ValueObjects\AuthenticationResult;
use Base\Foundation\Identity\Public\ValueObjects\EmailPasswordCredentials;
use Base\Foundation\Identity\Public\ValueObjects\Principal;
use Base\Foundation\Identity\Public\ValueObjects\PrincipalId;
use Base\Foundation\Identity\Public\ValueObjects\PrincipalType;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

final class PrincipalValueObjectsTest extends TestCase
{
    // ── PrincipalId ──────────────────────────────────────────────────────────

    public function test_principal_id_wraps_non_empty_string(): void
    {
        $id = new PrincipalId('42');

        self::assertSame('42', $id->value);
        self::assertSame('42', $id->toString());
    }

    public function test_principal_id_rejects_empty_string(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('non-empty');

        new PrincipalId('');
    }

    public function test_principal_id_rejects_whitespace_only(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new PrincipalId('   ');
    }

    public function test_principal_id_equality_by_value(): void
    {
        $a = new PrincipalId('1');
        $b = new PrincipalId('1');
        $c = new PrincipalId('2');

        self::assertTrue($a->equals($b));
        self::assertFalse($a->equals($c));
    }

    public function test_principal_id_is_readonly(): void
    {
        self::assertTrue((new \ReflectionClass(PrincipalId::class))->isReadOnly());
    }

    // ── PrincipalType ────────────────────────────────────────────────────────

    public function test_principal_type_user_constant(): void
    {
        $t = PrincipalType::user();

        self::assertSame(PrincipalType::USER, $t->value);
        self::assertTrue($t->isUser());
        self::assertFalse($t->isSystem());
    }

    public function test_principal_type_system_constant(): void
    {
        $t = PrincipalType::system();

        self::assertSame(PrincipalType::SYSTEM, $t->value);
        self::assertTrue($t->isSystem());
        self::assertFalse($t->isUser());
    }

    public function test_principal_type_api_key_constant(): void
    {
        $t = PrincipalType::apiKey();

        self::assertSame(PrincipalType::API_KEY, $t->value);
    }

    public function test_principal_type_accepts_custom_value(): void
    {
        $t = new PrincipalType('service-account');

        self::assertSame('service-account', $t->value);
    }

    public function test_principal_type_rejects_empty_string(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new PrincipalType('');
    }

    public function test_principal_type_equality_by_value(): void
    {
        self::assertTrue(PrincipalType::user()->equals(new PrincipalType('user')));
        self::assertFalse(PrincipalType::user()->equals(PrincipalType::system()));
    }

    public function test_principal_type_is_readonly(): void
    {
        self::assertTrue((new \ReflectionClass(PrincipalType::class))->isReadOnly());
    }

    // ── Principal ────────────────────────────────────────────────────────────

    public function test_principal_exposes_id_and_type(): void
    {
        $id = new PrincipalId('99');
        $type = PrincipalType::user();
        $principal = new Principal($id, $type);

        self::assertSame($id, $principal->id);
        self::assertSame($type, $principal->type);
    }

    public function test_principal_equality_by_id_and_type(): void
    {
        $a = new Principal(new PrincipalId('1'), PrincipalType::user());
        $b = new Principal(new PrincipalId('1'), PrincipalType::user());
        $c = new Principal(new PrincipalId('2'), PrincipalType::user());
        $d = new Principal(new PrincipalId('1'), PrincipalType::system());

        self::assertTrue($a->equals($b));
        self::assertFalse($a->equals($c));
        self::assertFalse($a->equals($d));
    }

    public function test_principal_is_readonly(): void
    {
        self::assertTrue((new \ReflectionClass(Principal::class))->isReadOnly());
    }

    public function test_principal_has_no_product_specific_fields(): void
    {
        $properties = array_map(
            static fn (\ReflectionProperty $p): string => $p->getName(),
            (new \ReflectionClass(Principal::class))->getProperties(),
        );

        $forbidden = ['name', 'email', 'role', 'wallet', 'avatar', 'profile', 'permissions'];

        foreach ($forbidden as $field) {
            self::assertNotContains(
                $field,
                $properties,
                "Principal must not expose product-specific field '{$field}'.",
            );
        }
    }

    // ── AuthenticationResult invariant ───────────────────────────────────────

    public function test_authentication_result_always_contains_principal(): void
    {
        $principal = new Principal(new PrincipalId('1'), PrincipalType::user());
        $result = AuthenticationResult::success($principal);

        self::assertSame($principal, $result->principal);
    }

    public function test_authentication_result_has_no_success_flag(): void
    {
        // The success bool was removed — an AuthenticationResult is always successful.
        // Failure is represented by throwing AuthenticationFailed, not by a flag.
        $reflection = new \ReflectionClass(AuthenticationResult::class);
        $properties = array_map(
            static fn (\ReflectionProperty $p): string => $p->getName(),
            $reflection->getProperties(),
        );

        self::assertNotContains(
            'success',
            $properties,
            'AuthenticationResult must not have a success flag — failure is always an exception.',
        );
    }

    public function test_authentication_result_cannot_be_constructed_without_principal(): void
    {
        // Verify constructor requires a Principal — no optional/nullable principal.
        $constructor = (new \ReflectionClass(AuthenticationResult::class))->getConstructor();

        self::assertNotNull($constructor);
        self::assertSame(1, $constructor->getNumberOfRequiredParameters());

        $param = $constructor->getParameters()[0];
        self::assertSame('principal', $param->getName());
        self::assertFalse($param->isOptional());
    }

    public function test_authentication_result_factory_and_constructor_produce_same_result(): void
    {
        $principal = new Principal(new PrincipalId('5'), PrincipalType::system());

        $viaConstructor = new AuthenticationResult($principal);
        $viaFactory = AuthenticationResult::success($principal);

        self::assertSame($viaConstructor->principal, $viaFactory->principal);
    }

    public function test_authentication_result_is_readonly(): void
    {
        self::assertTrue((new \ReflectionClass(AuthenticationResult::class))->isReadOnly());
    }

    // ── Credentials abstraction ──────────────────────────────────────────────

    public function test_email_password_credentials_implements_credentials_interface(): void
    {
        $creds = new EmailPasswordCredentials('user@example.com', 'secret');

        self::assertInstanceOf(Credentials::class, $creds);
    }

    public function test_credentials_is_an_interface(): void
    {
        self::assertTrue((new \ReflectionClass(Credentials::class))->isInterface());
    }

    public function test_credentials_interface_has_no_required_methods(): void
    {
        // It is a marker interface — no methods required of implementors.
        $methods = (new \ReflectionClass(Credentials::class))->getMethods();

        self::assertCount(0, $methods, 'Credentials must be a marker interface with no methods.');
    }

    // ── EmailPasswordCredentials ─────────────────────────────────────────────

    public function test_email_password_credentials_expose_email_and_password(): void
    {
        $creds = new EmailPasswordCredentials('user@example.com', 'secret');

        self::assertSame('user@example.com', $creds->email);
        self::assertSame('secret', $creds->password);
    }

    public function test_credentials_reject_empty_email(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Email');

        new EmailPasswordCredentials('', 'secret');
    }

    public function test_credentials_reject_empty_password(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Password');

        new EmailPasswordCredentials('user@example.com', '');
    }

    public function test_credentials_is_readonly(): void
    {
        self::assertTrue((new \ReflectionClass(EmailPasswordCredentials::class))->isReadOnly());
    }

    // ── Framework independence ────────────────────────────────────────────────

    public function test_principal_instantiates_without_container(): void
    {
        $principal = new Principal(new PrincipalId('123'), PrincipalType::user());

        self::assertSame('123', $principal->id->value);
    }
}
