<?php

declare(strict_types=1);

namespace Base\Foundation\AccessControl\Public\Contracts;

use Base\Foundation\AccessControl\Public\Exceptions\AccessDenied;
use Base\Foundation\AccessControl\Public\ValueObjects\AuthorizationDecision;
use Base\Foundation\AccessControl\Public\ValueObjects\Permission;
use Base\Foundation\AccessControl\Public\ValueObjects\ResourceType;
use Base\Foundation\Identity\Public\ValueObjects\Principal;

/**
 * Primary authorization contract.
 *
 * Consumers inject this interface to evaluate whether a given
 * Principal is authorized to perform an action (Permission) on
 * an optional resource type.
 *
 * Fail-closed: unknown permissions, missing policies, and absent
 * principals always result in denial. There is no permissive default.
 *
 * No framework dependencies. No Laravel Gate, Auth, or Request types.
 */
interface AuthorizationChecker
{
    /**
     * Evaluate authorization and return an explicit decision.
     *
     * Always returns a decision — never null, never throws for
     * a legitimate deny. The decision object contains a reason.
     */
    public function check(
        Principal $principal,
        Permission $permission,
        ?ResourceType $resource = null,
    ): AuthorizationDecision;

    /**
     * Convenience boolean: returns true only when access is granted.
     *
     * Defaults to false (deny) in all ambiguous or error cases.
     */
    public function isGranted(
        Principal $principal,
        Permission $permission,
        ?ResourceType $resource = null,
    ): bool;

    /**
     * Assert that access is granted; throw AccessDenied otherwise.
     *
     * Use in code paths that must fail loudly on unauthorized access.
     *
     * @throws AccessDenied When the decision is deny.
     */
    public function demand(
        Principal $principal,
        Permission $permission,
        ?ResourceType $resource = null,
    ): void;
}
