<?php

declare(strict_types=1);

namespace Tests\Unit\Packages\Base\Foundation\AccessControl;

use Base\Foundation\AccessControl\Public\ValueObjects\AuthorizationDecision;
use Base\Foundation\AccessControl\Public\ValueObjects\Permission;
use Base\Foundation\AccessControl\Public\ValueObjects\ResourceType;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

/**
 * Tests for AccessControl value objects: Permission, ResourceType,
 * and AuthorizationDecision.
 *
 * Covers construction, validation, equality, and immutability.
 */
final class ValueObjectsTest extends TestCase
{
    // ── Permission ──────────────────────────────────────────────────────────

    public function test_permission_wraps_non_empty_string(): void
    {
        $p = new Permission('wallet.view');

        self::assertSame('wallet.view', $p->value);
        self::assertSame('wallet.view', $p->toString());
    }

    public function test_permission_rejects_empty_string(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('non-empty');

        new Permission('');
    }

    public function test_permission_rejects_whitespace_only(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new Permission('   ');
    }

    public function test_permission_equality_by_value(): void
    {
        $a = new Permission('wallet.view');
        $b = new Permission('wallet.view');
        $c = new Permission('wallet.transfer');

        self::assertTrue($a->equals($b));
        self::assertFalse($a->equals($c));
    }

    public function test_permission_is_readonly(): void
    {
        self::assertTrue((new \ReflectionClass(Permission::class))->isReadOnly());
    }

    // ── ResourceType ────────────────────────────────────────────────────────

    public function test_resource_type_wraps_non_empty_string(): void
    {
        $r = new ResourceType('wallet');

        self::assertSame('wallet', $r->value);
        self::assertSame('wallet', $r->toString());
    }

    public function test_resource_type_rejects_empty_string(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('non-empty');

        new ResourceType('');
    }

    public function test_resource_type_rejects_whitespace_only(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new ResourceType('   ');
    }

    public function test_resource_type_equality_by_value(): void
    {
        $a = new ResourceType('wallet');
        $b = new ResourceType('wallet');
        $c = new ResourceType('order');

        self::assertTrue($a->equals($b));
        self::assertFalse($a->equals($c));
    }

    public function test_resource_type_is_readonly(): void
    {
        self::assertTrue((new \ReflectionClass(ResourceType::class))->isReadOnly());
    }

    // ── AuthorizationDecision ───────────────────────────────────────────────

    public function test_allow_decision_is_granted(): void
    {
        $decision = AuthorizationDecision::allow('Test allow reason.');

        self::assertTrue($decision->isGranted());
        self::assertFalse($decision->isDenied());
        self::assertTrue($decision->granted);
        self::assertSame('Test allow reason.', $decision->reason);
    }

    public function test_deny_decision_is_denied(): void
    {
        $decision = AuthorizationDecision::deny('Test deny reason.');

        self::assertFalse($decision->isGranted());
        self::assertTrue($decision->isDenied());
        self::assertFalse($decision->granted);
        self::assertSame('Test deny reason.', $decision->reason);
    }

    public function test_allow_decision_default_reason(): void
    {
        $decision = AuthorizationDecision::allow();

        self::assertSame('Allowed by policy.', $decision->reason);
    }

    public function test_deny_decision_default_reason(): void
    {
        $decision = AuthorizationDecision::deny();

        self::assertSame('Denied by policy.', $decision->reason);
    }

    public function test_authorization_decision_is_readonly(): void
    {
        self::assertTrue((new \ReflectionClass(AuthorizationDecision::class))->isReadOnly());
    }

    // ── Framework independence ────────────────────────────────────────────────

    public function test_value_objects_instantiate_without_container(): void
    {
        $permission = new Permission('test.action');
        $resource = new ResourceType('test');
        $decision = AuthorizationDecision::allow();

        self::assertSame('test.action', $permission->value);
        self::assertSame('test', $resource->value);
        self::assertTrue($decision->isGranted());
    }
}
