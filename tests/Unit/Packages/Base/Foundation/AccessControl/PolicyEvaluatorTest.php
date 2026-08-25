<?php

declare(strict_types=1);

namespace Tests\Unit\Packages\Base\Foundation\AccessControl;

use Base\Foundation\AccessControl\Application\PolicyEvaluator;
use Base\Foundation\AccessControl\Public\Contracts\AuthorizationChecker;
use Base\Foundation\AccessControl\Public\Contracts\AuthorizationPolicy;
use Base\Foundation\AccessControl\Public\Exceptions\AccessDenied;
use Base\Foundation\AccessControl\Public\ValueObjects\AuthorizationDecision;
use Base\Foundation\AccessControl\Public\ValueObjects\Permission;
use Base\Foundation\AccessControl\Public\ValueObjects\ResourceType;
use Base\Foundation\Identity\Public\ValueObjects\Principal;
use Base\Foundation\Identity\Public\ValueObjects\PrincipalId;
use Base\Foundation\Identity\Public\ValueObjects\PrincipalType;
use PHPUnit\Framework\TestCase;

/**
 * Tests for the PolicyEvaluator — the internal implementation of
 * AuthorizationChecker.
 *
 * Covers: policy dispatch, first-match-or-deny strategy, fail-closed
 * behavior, Principal integration, and demand/isGranted convenience.
 */
final class PolicyEvaluatorTest extends TestCase
{
    private Principal $principal;

    protected function setUp(): void
    {
        parent::setUp();

        $this->principal = new Principal(
            new PrincipalId('user-42'),
            PrincipalType::user(),
        );
    }

    // ── Implements AuthorizationChecker ──────────────────────────────────────

    public function test_policy_evaluator_implements_authorization_checker(): void
    {
        self::assertInstanceOf(AuthorizationChecker::class, new PolicyEvaluator);
    }

    // ── Positive: explicit allow ────────────────────────────────────────────

    public function test_returns_allow_when_policy_grants(): void
    {
        $policy = $this->createMock(AuthorizationPolicy::class);
        $policy->method('supports')->willReturn(true);
        $policy->method('evaluate')->willReturn(AuthorizationDecision::allow());

        $evaluator = new PolicyEvaluator([$policy]);

        $decision = $evaluator->check(
            $this->principal,
            new Permission('test.action'),
        );

        self::assertTrue($decision->isGranted());
    }

    public function test_is_granted_returns_true_when_allowed(): void
    {
        $policy = $this->createMock(AuthorizationPolicy::class);
        $policy->method('supports')->willReturn(true);
        $policy->method('evaluate')->willReturn(AuthorizationDecision::allow());

        $evaluator = new PolicyEvaluator([$policy]);

        self::assertTrue(
            $evaluator->isGranted($this->principal, new Permission('test.action')),
        );
    }

    public function test_demand_does_not_throw_when_allowed(): void
    {
        $policy = $this->createMock(AuthorizationPolicy::class);
        $policy->method('supports')->willReturn(true);
        $policy->method('evaluate')->willReturn(AuthorizationDecision::allow());

        $evaluator = new PolicyEvaluator([$policy]);

        // Should not throw
        $evaluator->demand($this->principal, new Permission('test.action'));

        $this->addToAssertionCount(1);
    }

    // ── Positive: explicit deny ─────────────────────────────────────────────

    public function test_returns_deny_when_policy_denies(): void
    {
        $policy = $this->createMock(AuthorizationPolicy::class);
        $policy->method('supports')->willReturn(true);
        $policy->method('evaluate')->willReturn(AuthorizationDecision::deny());

        $evaluator = new PolicyEvaluator([$policy]);

        $decision = $evaluator->check(
            $this->principal,
            new Permission('test.action'),
        );

        self::assertTrue($decision->isDenied());
    }

    public function test_is_granted_returns_false_when_denied(): void
    {
        $policy = $this->createMock(AuthorizationPolicy::class);
        $policy->method('supports')->willReturn(true);
        $policy->method('evaluate')->willReturn(AuthorizationDecision::deny());

        $evaluator = new PolicyEvaluator([$policy]);

        self::assertFalse(
            $evaluator->isGranted($this->principal, new Permission('test.action')),
        );
    }

    // ── Positive: policy receives correct arguments ─────────────────────────

    public function test_policy_receives_principal_permission_and_resource(): void
    {
        $evaluator = new PolicyEvaluator;
        $permission = new Permission('test.action');
        $resource = new ResourceType('document');

        $policy = $this->createMock(AuthorizationPolicy::class);
        $policy->expects(self::once())
            ->method('supports')
            ->with($permission, $resource)
            ->willReturn(true);

        $policy->expects(self::once())
            ->method('evaluate')
            ->with($this->principal, $permission, $resource)
            ->willReturn(AuthorizationDecision::allow());

        $evaluator->addPolicy($policy);
        $evaluator->check($this->principal, $permission, $resource);
    }

    // ── Positive: Identity Principal evaluation ─────────────────────────────

    public function test_identity_principal_can_be_evaluated(): void
    {
        $systemPrincipal = new Principal(
            new PrincipalId('system-1'),
            PrincipalType::system(),
        );

        $policy = $this->createMock(AuthorizationPolicy::class);
        $policy->method('supports')->willReturn(true);
        $policy->method('evaluate')->willReturnCallback(
            function (Principal $principal) {
                if ($principal->type->isSystem()) {
                    return AuthorizationDecision::allow('System principals are allowed.');
                }

                return AuthorizationDecision::deny('Only system principals allowed.');
            }
        );

        $evaluator = new PolicyEvaluator([$policy]);

        self::assertTrue(
            $evaluator->isGranted($systemPrincipal, new Permission('admin.action')),
        );
        self::assertFalse(
            $evaluator->isGranted($this->principal, new Permission('admin.action')),
        );
    }

    // ── Positive: deny-overrides ────────────────────────────────────────────

    public function test_deny_overrides_allow(): void
    {
        $allowPolicy = $this->createMock(AuthorizationPolicy::class);
        $allowPolicy->method('supports')->willReturn(true);
        $allowPolicy->method('evaluate')->willReturn(AuthorizationDecision::allow('Policy allowed.'));

        $denyPolicy = $this->createMock(AuthorizationPolicy::class);
        $denyPolicy->method('supports')->willReturn(true);
        $denyPolicy->method('evaluate')->willReturn(AuthorizationDecision::deny('Policy denied.'));

        // Allow registered first, but deny should still override
        $evaluator = new PolicyEvaluator([$allowPolicy, $denyPolicy]);

        $decision = $evaluator->check($this->principal, new Permission('test.action'));

        self::assertTrue($decision->isDenied());
        self::assertSame('Policy denied.', $decision->reason);
    }

    public function test_multiple_allows_result_in_allow(): void
    {
        $allowPolicy1 = $this->createMock(AuthorizationPolicy::class);
        $allowPolicy1->method('supports')->willReturn(true);
        $allowPolicy1->method('evaluate')->willReturn(AuthorizationDecision::allow('First allow.'));

        $allowPolicy2 = $this->createMock(AuthorizationPolicy::class);
        $allowPolicy2->method('supports')->willReturn(true);
        $allowPolicy2->method('evaluate')->willReturn(AuthorizationDecision::allow('Second allow.'));

        $evaluator = new PolicyEvaluator([$allowPolicy1, $allowPolicy2]);

        $decision = $evaluator->check($this->principal, new Permission('test.action'));

        self::assertTrue($decision->isGranted());
        // Reason could be either, currently we take the last one evaluated (or first, depending on implementation).
        // Let's just assert it is granted.
    }

    // ── Positive: resource type support ─────────────────────────────────────

    public function test_check_with_resource_type(): void
    {
        $policy = $this->createMock(AuthorizationPolicy::class);
        $policy->method('supports')->willReturnCallback(
            function (Permission $permission, ?ResourceType $resource) {
                return $resource !== null && $resource->value === 'document';
            }
        );
        $policy->method('evaluate')->willReturn(AuthorizationDecision::allow('Document access granted.'));

        $evaluator = new PolicyEvaluator([$policy]);

        self::assertTrue(
            $evaluator->isGranted(
                $this->principal,
                new Permission('doc.view'),
                new ResourceType('document'),
            ),
        );

        self::assertFalse(
            $evaluator->isGranted(
                $this->principal,
                new Permission('doc.view'),
                new ResourceType('image'),
            ),
        );
    }

    // ── Negative: fail-closed when no policies registered ───────────────────

    public function test_denies_when_no_policies_registered(): void
    {
        $evaluator = new PolicyEvaluator;

        $decision = $evaluator->check(
            $this->principal,
            new Permission('any.action'),
        );

        self::assertTrue($decision->isDenied());
        self::assertSame('No policy granted access.', $decision->reason);
    }

    // ── Negative: fail-closed when all policies abstain ─────────────────────

    public function test_denies_when_all_policies_abstain(): void
    {
        $abstainPolicy = $this->createMock(AuthorizationPolicy::class);
        $abstainPolicy->method('supports')->willReturn(true);
        $abstainPolicy->method('evaluate')->willReturn(null);

        $evaluator = new PolicyEvaluator([$abstainPolicy]);

        $decision = $evaluator->check(
            $this->principal,
            new Permission('any.action'),
        );

        self::assertTrue($decision->isDenied());
    }

    // ── Negative: fail-closed when no policy supports the permission ────────

    public function test_denies_when_no_policy_supports_permission(): void
    {
        $policy = $this->createMock(AuthorizationPolicy::class);
        $policy->method('supports')->willReturnCallback(
            function (Permission $permission) {
                return $permission->value === 'other.action';
            }
        );
        $policy->method('evaluate')->willReturn(AuthorizationDecision::allow());

        $evaluator = new PolicyEvaluator([$policy]);

        $decision = $evaluator->check(
            $this->principal,
            new Permission('unregistered.action'),
        );

        self::assertTrue($decision->isDenied());
    }

    // ── Negative: demand throws AccessDenied on deny ────────────────────────

    public function test_demand_throws_access_denied_on_deny(): void
    {
        $evaluator = new PolicyEvaluator;
        $permission = new Permission('forbidden.action');

        $this->expectException(AccessDenied::class);
        $this->expectExceptionMessage('forbidden.action');

        $evaluator->demand($this->principal, $permission);
    }

    // ── addPolicy: dynamic policy registration ──────────────────────────────

    public function test_add_policy_registers_policy_at_runtime(): void
    {
        $evaluator = new PolicyEvaluator;
        $permission = new Permission('dynamic.action');

        // Initially denied (no policies)
        self::assertFalse($evaluator->isGranted($this->principal, $permission));

        $policy = $this->createMock(AuthorizationPolicy::class);
        $policy->method('supports')->willReturn(true);
        $policy->method('evaluate')->willReturn(AuthorizationDecision::allow());

        // Add a policy
        $evaluator->addPolicy($policy);

        // Now granted
        self::assertTrue($evaluator->isGranted($this->principal, $permission));
    }

    // ── Skips unsupporting policies ─────────────────────────────────────────

    public function test_skips_policies_that_do_not_support_permission(): void
    {
        $unsupporting = $this->createMock(AuthorizationPolicy::class);
        $unsupporting->method('supports')->willReturn(false);
        $unsupporting->expects(self::never())->method('evaluate');

        $supporting = $this->createMock(AuthorizationPolicy::class);
        $supporting->method('supports')->willReturn(true);
        $supporting->method('evaluate')->willReturn(AuthorizationDecision::allow());

        $evaluator = new PolicyEvaluator([$unsupporting, $supporting]);

        self::assertTrue(
            $evaluator->isGranted($this->principal, new Permission('test.action')),
        );
    }
}
