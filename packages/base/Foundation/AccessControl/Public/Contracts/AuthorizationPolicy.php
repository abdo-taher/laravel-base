<?php

declare(strict_types=1);

namespace Base\Foundation\AccessControl\Public\Contracts;

use Base\Foundation\AccessControl\Public\ValueObjects\AuthorizationDecision;
use Base\Foundation\AccessControl\Public\ValueObjects\Permission;
use Base\Foundation\AccessControl\Public\ValueObjects\ResourceType;
use Base\Foundation\Identity\Public\ValueObjects\Principal;

/**
 * Extension hook: contributes authorization evaluation logic.
 *
 * Business modules implement this contract to provide authorization
 * rules for permissions they own. AccessControl aggregates policies
 * and evaluates them at runtime.
 *
 * Policies may:
 *   - Return AuthorizationDecision::allow() to grant access.
 *   - Return AuthorizationDecision::deny() to deny access.
 *   - Return null to abstain (the policy does not apply).
 *
 * If all policies abstain, AccessControl defaults to deny (fail-closed).
 *
 * Compatible with the existing extension contributor model
 * (ConfigurationSourceContributor, PrincipalEnricher). Wiring to the
 * full ExtensionRegistry runtime is deferred to post-B3.
 *
 * No framework dependencies.
 */
interface AuthorizationPolicy
{
    /**
     * Whether this policy can evaluate the given permission/resource.
     *
     * Used for efficient dispatch — policies that return false here
     * will not have evaluate() called.
     */
    public function supports(
        Permission $permission,
        ?ResourceType $resource = null,
    ): bool;

    /**
     * Evaluate authorization for the given principal and permission.
     *
     * Returns an explicit decision, or null to abstain.
     * Abstaining means the policy has no opinion — the evaluator
     * will consult the next policy.
     */
    public function evaluate(
        Principal $principal,
        Permission $permission,
        ?ResourceType $resource = null,
    ): ?AuthorizationDecision;
}
