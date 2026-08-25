<?php

declare(strict_types=1);

namespace Base\Foundation\AccessControl\Public\ValueObjects;

/**
 * The explicit outcome of an authorization evaluation.
 *
 * Every authorization check produces a deterministic decision: either
 * granted or denied, with a human-readable reason.
 *
 * There is no tri-state. There is no null. There is no "maybe".
 * Named constructors enforce semantics and make call sites readable.
 *
 * No framework dependencies. Instantiable without a container.
 */
final readonly class AuthorizationDecision
{
    private function __construct(
        public bool $granted,
        public string $reason,
    ) {}

    public static function allow(string $reason = 'Allowed by policy.'): self
    {
        return new self(granted: true, reason: $reason);
    }

    public static function deny(string $reason = 'Denied by policy.'): self
    {
        return new self(granted: false, reason: $reason);
    }

    public function isGranted(): bool
    {
        return $this->granted;
    }

    public function isDenied(): bool
    {
        return ! $this->granted;
    }
}
