<?php

declare(strict_types=1);

namespace Base\Foundation\Identity\Public\ValueObjects;

/**
 * The outcome of a successful authentication attempt.
 *
 * Invariant: an AuthenticationResult always and only represents
 * successful authentication containing a verified Principal.
 *
 * Failed authentication never produces this value object —
 * AuthenticationFailed is thrown instead. No partial or failure
 * state is representable here.
 *
 * This design eliminates the invalid state where success=false
 * could coexist with a principal (or no principal could coexist
 * with success=true).
 *
 * No framework dependencies.
 */
final readonly class AuthenticationResult
{
    public function __construct(public Principal $principal) {}

    /**
     * Named constructor — preferred factory method.
     * Identical to new AuthenticationResult($principal).
     */
    public static function success(Principal $principal): self
    {
        return new self($principal);
    }
}
