<?php

declare(strict_types=1);

namespace Tests\Unit\Packages\Base\Foundation\Identity;

use Base\Foundation\Identity\Public\Contracts\Authenticator;
use Base\Foundation\Identity\Public\Contracts\Credentials;
use Base\Foundation\Identity\Public\Contracts\CurrentPrincipal;
use Base\Foundation\Identity\Public\Contracts\PrincipalEnricher;
use Base\Foundation\Identity\Public\Exceptions\AuthenticationFailed;
use Base\Foundation\Identity\Public\Exceptions\AuthenticationRequired;
use Base\Foundation\Identity\Public\ValueObjects\AuthenticationResult;
use Base\Foundation\Identity\Public\ValueObjects\EmailPasswordCredentials;
use Base\Foundation\Identity\Public\ValueObjects\Principal;
use Base\Foundation\Identity\Public\ValueObjects\PrincipalId;
use Base\Foundation\Identity\Public\ValueObjects\PrincipalType;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/**
 * Architecture tests for the Identity Foundation package.
 *
 * Proves:
 * - Public contracts contain no Laravel/Illuminate dependency.
 * - Public contracts expose no Eloquent model types.
 * - Public contracts expose no product-specific vocabulary.
 * - Value objects are readonly.
 * - Exception fail-closed semantics.
 */
final class IdentityArchitectureTest extends TestCase
{
    /** @return array<string, array{class-string}> */
    public static function publicContractClassProvider(): array
    {
        return [
            'Authenticator' => [Authenticator::class],
            'Credentials' => [Credentials::class],
            'CurrentPrincipal' => [CurrentPrincipal::class],
            'PrincipalEnricher' => [PrincipalEnricher::class],
            'AuthenticationFailed' => [AuthenticationFailed::class],
            'AuthenticationRequired' => [AuthenticationRequired::class],
            'Principal' => [Principal::class],
            'PrincipalId' => [PrincipalId::class],
            'PrincipalType' => [PrincipalType::class],
            'AuthenticationResult' => [AuthenticationResult::class],
            'EmailPasswordCredentials' => [EmailPasswordCredentials::class],
        ];
    }

    #[DataProvider('publicContractClassProvider')]
    public function test_public_contract_has_no_illuminate_import(string $class): void
    {
        /** @var class-string $class */
        $file = (new \ReflectionClass($class))->getFileName();

        self::assertNotFalse($file);

        $source = file_get_contents($file);

        self::assertIsString($source);
        self::assertStringNotContainsString(
            'use Illuminate',
            $source,
            "Public contract {$class} must not import any Illuminate/Laravel class.",
        );
    }

    #[DataProvider('publicContractClassProvider')]
    public function test_public_contract_has_no_eloquent_reference(string $class): void
    {
        /** @var class-string $class */
        $file = (new \ReflectionClass($class))->getFileName();

        self::assertNotFalse($file);

        $source = file_get_contents($file);

        self::assertIsString($source);

        foreach (['Eloquent', 'Builder', 'HasFactory', 'Authenticatable'] as $term) {
            self::assertStringNotContainsString(
                $term,
                $source,
                "Public contract {$class} must not reference Eloquent type '{$term}'.",
            );
        }
    }

    #[DataProvider('publicContractClassProvider')]
    public function test_public_contract_has_no_product_vocabulary(string $class): void
    {
        /** @var class-string $class */
        $file = (new \ReflectionClass($class))->getFileName();

        self::assertNotFalse($file);

        $source = file_get_contents($file);

        self::assertIsString($source);

        // Dotted namespace prefixes ensure we match actual product references,
        // not legitimate English words in comments.
        foreach (['wallet.', 'order.', 'cart.', 'payment.', 'vendor.', 'customer.'] as $term) {
            self::assertStringNotContainsString(
                $term,
                strtolower($source),
                "Public contract {$class} must not contain product vocabulary '{$term}'.",
            );
        }
    }

    public function test_contracts_are_interfaces(): void
    {
        foreach ([Authenticator::class, CurrentPrincipal::class, PrincipalEnricher::class] as $class) {
            self::assertTrue(
                (new \ReflectionClass($class))->isInterface(),
                "{$class} must be an interface.",
            );
        }
    }

    public function test_value_objects_are_readonly(): void
    {
        foreach ([
            Principal::class,
            PrincipalId::class,
            PrincipalType::class,
            AuthenticationResult::class,
            EmailPasswordCredentials::class,
        ] as $class) {
            self::assertTrue(
                (new \ReflectionClass($class))->isReadOnly(),
                "{$class} must be a readonly class.",
            );
        }
    }

    // ── Exception fail-closed semantics ──────────────────────────────────────

    public function test_authentication_failed_message_does_not_disclose_which_field_failed(): void
    {
        $e = AuthenticationFailed::invalidCredentials();

        // Must not say "email not found" or "wrong password" — prevents user enumeration
        self::assertStringNotContainsString('email', strtolower($e->getMessage()));
        self::assertStringNotContainsString('password', strtolower($e->getMessage()));
        self::assertStringNotContainsString('user', strtolower($e->getMessage()));
    }

    public function test_authentication_required_has_descriptive_message(): void
    {
        $e = AuthenticationRequired::noAuthenticatedPrincipal();

        self::assertStringContainsString('authenticated', strtolower($e->getMessage()));
    }
}
