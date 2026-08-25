<?php

declare(strict_types=1);

namespace Base\Foundation\AccessControl\Application;

use Base\Foundation\AccessControl\Public\Contracts\AuthorizationChecker;
use Base\Foundation\AccessControl\Public\Contracts\AuthorizationPolicy;
use Base\Foundation\AccessControl\Public\Exceptions\AccessDenied;
use Base\Foundation\AccessControl\Public\ValueObjects\AuthorizationDecision;
use Base\Foundation\AccessControl\Public\ValueObjects\Permission;
use Base\Foundation\AccessControl\Public\ValueObjects\ResourceType;
use Base\Foundation\Identity\Public\ValueObjects\Principal;

/**
 * Aggregates registered AuthorizationPolicy instances and evaluates
 * authorization requests against them.
 * Evaluation strategy: deny-overrides (order-independent).
 *
 *   1. Iterate all registered policies.
 *   2. Skip policies that do not support the permission/resource.
 *   3. Call evaluate() on supporting policies.
 *   4. If ANY policy returns a DENY decision, access is denied immediately.
 *   5. If AT LEAST ONE policy returns ALLOW (and none deny), access is allowed.
 *   6. If all policies abstain (return null), deny by default.
 *
 * Fail-closed: no policies registered → deny. All policies abstain → deny.
 * Unknown permission → deny. Any explicit deny overrides all allows.
 *
 * This class is internal to AccessControl. External consumers use
 * the AuthorizationChecker public contract.
 */
final class PolicyEvaluator implements AuthorizationChecker
{
    /** @var list<AuthorizationPolicy> */
    private array $policies = [];

    /**
     * @param  iterable<AuthorizationPolicy>  $policies
     */
    public function __construct(iterable $policies = [])
    {
        foreach ($policies as $policy) {
            $this->policies[] = $policy;
        }
    }

    public function addPolicy(AuthorizationPolicy $policy): void
    {
        $this->policies[] = $policy;
    }

    public function check(
        Principal $principal,
        Permission $permission,
        ?ResourceType $resource = null,
    ): AuthorizationDecision {
        $allowed = false;
        $allowReason = 'Allowed by policy.';

        foreach ($this->policies as $policy) {
            if (! $policy->supports($permission, $resource)) {
                continue;
            }

            $decision = $policy->evaluate($principal, $permission, $resource);

            if ($decision === null) {
                continue;
            }

            if ($decision->isDenied()) {
                // Deny immediately overrides any allows (fail-fast)
                return $decision;
            }

            // We have at least one allow
            $allowed = true;
            $allowReason = $decision->reason;
        }

        if ($allowed) {
            return AuthorizationDecision::allow($allowReason);
        }

        return AuthorizationDecision::deny('No policy granted access.');
    }

    public function isGranted(
        Principal $principal,
        Permission $permission,
        ?ResourceType $resource = null,
    ): bool {
        return $this->check($principal, $permission, $resource)->isGranted();
    }

    public function demand(
        Principal $principal,
        Permission $permission,
        ?ResourceType $resource = null,
    ): void {
        $decision = $this->check($principal, $permission, $resource);

        if ($decision->isDenied()) {
            throw AccessDenied::forPermission($permission);
        }
    }
}
