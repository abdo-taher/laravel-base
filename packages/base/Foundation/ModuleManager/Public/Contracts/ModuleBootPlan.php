<?php

declare(strict_types=1);

namespace Base\Foundation\ModuleManager\Public\Contracts;

use Base\Foundation\ModuleManager\Public\ValueObjects\ModuleIdentifier;
use Base\Foundation\ModuleManager\Public\ValueObjects\ModuleState;

/**
 * Represents the deterministic initialization order produced by the
 * ModuleManager orchestration pass.
 *
 * The ordered identifiers reflect topological dependency resolution:
 * a module appears after all modules it depends on.
 *
 * No Laravel types.
 */
interface ModuleBootPlan
{
    /**
     * Returns the deterministic ordered list of module identifiers.
     * Order is dependency-topological, then lexicographic for ties.
     *
     * @return list<ModuleIdentifier>
     */
    public function orderedIdentifiers(): array;

    /**
     * Returns the per-module state descriptor for the given identifier.
     * Returns null when the identifier is not part of this plan.
     */
    public function stateFor(ModuleIdentifier $identifier): ?ModuleState;

    /**
     * Returns all module states in boot order.
     *
     * @return list<ModuleState>
     */
    public function allStates(): array;
}
