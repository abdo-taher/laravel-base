<?php

declare(strict_types=1);

namespace Base\Foundation\AccessControl\Public\Exceptions;

use Base\Foundation\AccessControl\Public\ValueObjects\Permission;
use RuntimeException;

/**
 * Thrown when authorization is denied.
 *
 * Fail-closed: authorization always results in an explicit allow or
 * this exception. Unknown permissions, missing principals, and absent
 * policies all result in denial.
 *
 * The message must not reveal internal policy details, registered
 * permissions, or infrastructure specifics. It may include the
 * permission name since the caller already knows what they requested.
 */
final class AccessDenied extends RuntimeException
{
    public static function forPermission(Permission $permission): self
    {
        return new self(sprintf(
            'Access denied for permission: %s.',
            $permission->value,
        ));
    }

    public static function missingPrincipal(): self
    {
        return new self('Access denied: no authenticated principal provided.');
    }
}
