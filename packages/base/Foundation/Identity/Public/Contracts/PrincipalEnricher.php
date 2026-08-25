<?php

declare(strict_types=1);

namespace Base\Foundation\Identity\Public\Contracts;

use Base\Foundation\Identity\Public\ValueObjects\Principal;

/**
 * Extension hook for project-owned principal context enrichment.
 *
 * Project extensions (e.g. extensions/Base/Identity/) implement this
 * contract to attach additional context to a Principal without
 * modifying Identity internals or adding business fields to the
 * Principal value object.
 *
 * Enrichers are passive in B3.3 — the contract is exposed but no
 * runtime enrichment pipeline is wired yet. Wiring is deferred until
 * the full ExtensionRegistry discovery is available.
 *
 * Enrichers must return a valid Principal. They must not alter the
 * PrincipalId or PrincipalType. They must not perform authorization
 * checks. They must not throw for missing optional profile data.
 *
 * No framework dependencies.
 */
interface PrincipalEnricher
{
    /**
     * Enrich the given principal with additional context.
     *
     * Implementations must return a Principal instance. In B3.3, since
     * Principal carries only id and type, enrichment is a no-op unless
     * a subtype or decorator pattern is introduced later.
     */
    public function enrich(Principal $principal): Principal;
}
