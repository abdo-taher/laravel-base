<?php

declare(strict_types=1);

namespace Tests\Unit\Packages\Base\Foundation\AccessControl;

use Base\Foundation\AccessControl\Public\Contracts\AuthorizationChecker;
use Base\Foundation\AccessControl\Public\Contracts\AuthorizationPolicy;
use Base\Foundation\AccessControl\Public\Contracts\PermissionContributor;
use Base\Foundation\AccessControl\Public\Exceptions\AccessDenied;
use Base\Foundation\AccessControl\Public\ValueObjects\AuthorizationDecision;
use Base\Foundation\AccessControl\Public\ValueObjects\Permission;
use Base\Foundation\AccessControl\Public\ValueObjects\ResourceType;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/**
 * Architecture tests for the AccessControl Foundation package.
 *
 * Proves:
 * - Public contracts contain no Laravel/Illuminate dependency.
 * - Public contracts expose no Eloquent model types.
 * - Public contracts expose no product-specific vocabulary.
 * - Value objects are readonly.
 * - Exception fail-closed semantics.
 * - Contracts are interfaces.
 */
final class AccessControlArchitectureTest extends TestCase
{
    /** @return array<string, array{class-string}> */
    public static function publicContractClassProvider(): array
    {
        return [
            'AuthorizationChecker' => [AuthorizationChecker::class],
            'AuthorizationPolicy' => [AuthorizationPolicy::class],
            'PermissionContributor' => [PermissionContributor::class],
            'AccessDenied' => [AccessDenied::class],
            'AuthorizationDecision' => [AuthorizationDecision::class],
            'Permission' => [Permission::class],
            'ResourceType' => [ResourceType::class],
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
    public function test_public_contract_has_no_laravel_gate_reference(string $class): void
    {
        /** @var class-string $class */
        $file = (new \ReflectionClass($class))->getFileName();

        self::assertNotFalse($file);

        $source = file_get_contents($file);

        self::assertIsString($source);

        foreach (['use Illuminate\\Auth', 'use Illuminate\\Contracts\\Auth\\Access\\Gate', 'Request $request'] as $term) {
            self::assertStringNotContainsString(
                $term,
                $source,
                "Public contract {$class} must not reference Laravel Gate/Auth/Request type '{$term}'.",
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
        foreach ([
            AuthorizationChecker::class,
            AuthorizationPolicy::class,
            PermissionContributor::class,
        ] as $class) {
            self::assertTrue(
                (new \ReflectionClass($class))->isInterface(),
                "{$class} must be an interface.",
            );
        }
    }

    public function test_value_objects_are_readonly(): void
    {
        foreach ([
            Permission::class,
            ResourceType::class,
            AuthorizationDecision::class,
        ] as $class) {
            self::assertTrue(
                (new \ReflectionClass($class))->isReadOnly(),
                "{$class} must be a readonly class.",
            );
        }
    }

    // ── No product-specific roles or permissions hard-coded ─────────────────

    public function test_no_product_role_constants(): void
    {
        $forbiddenRoles = ['admin', 'provider', 'vendor', 'customer', 'inspector'];

        $classes = [
            AuthorizationChecker::class,
            AuthorizationPolicy::class,
            PermissionContributor::class,
            AccessDenied::class,
            AuthorizationDecision::class,
            Permission::class,
            ResourceType::class,
        ];

        foreach ($classes as $class) {
            $reflection = new \ReflectionClass($class);
            $constants = $reflection->getConstants();

            foreach ($forbiddenRoles as $role) {
                foreach ($constants as $name => $value) {
                    if (is_string($value)) {
                        self::assertNotSame(
                            $role,
                            strtolower($value),
                            "{$class}::{$name} must not define product role '{$role}'.",
                        );
                    }
                }
            }
        }
        self::assertNotEmpty($classes);
    }

    // ── Exception fail-closed semantics ──────────────────────────────────────

    public function test_access_denied_for_permission_includes_permission_name(): void
    {
        $e = AccessDenied::forPermission(new Permission('test.action'));

        self::assertStringContainsString('test.action', $e->getMessage());
    }

    public function test_access_denied_missing_principal_has_descriptive_message(): void
    {
        $e = AccessDenied::missingPrincipal();

        self::assertStringContainsString('principal', strtolower($e->getMessage()));
    }

    public function test_access_denied_does_not_disclose_policy_internals(): void
    {
        $e = AccessDenied::forPermission(new Permission('sensitive.action'));

        // Must not leak policy class names, stack traces, or internal details
        self::assertStringNotContainsString('PolicyEvaluator', $e->getMessage());
        self::assertStringNotContainsString('class', strtolower($e->getMessage()));
    }
}
