<?php

declare(strict_types=1);

namespace Base\Foundation\Identity\Public\Contracts;

use Base\Foundation\Identity\Public\Exceptions\AuthenticationRequired;
use Base\Foundation\Identity\Public\ValueObjects\Principal;

/**
 * Retrieves the authenticated principal for the current request context.
 *
 * This contract replaces direct coupling to Laravel's Auth::user() across
 * Base Foundation and Platform packages. Consumers (Audit, Observability,
 * AccessControl) inject this interface rather than any Laravel type.
 *
 * No framework dependencies.
 */
interface CurrentPrincipal
{
    /**
     * Returns the authenticated principal.
     *
     * @throws AuthenticationRequired When no principal is authenticated
     *                                in the current context.
     */
    public function get(): Principal;

    /**
     * Returns the authenticated principal, or null if not authenticated.
     * Never throws.
     */
    public function find(): ?Principal;

    /**
     * Returns true when a principal is authenticated in the current context.
     * Never throws.
     */
    public function isAuthenticated(): bool;
}
