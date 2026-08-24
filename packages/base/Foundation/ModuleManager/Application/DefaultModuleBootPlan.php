<?php

declare(strict_types=1);

namespace Base\Foundation\ModuleManager\Application;

use Base\Foundation\ModuleManager\Public\Contracts\ModuleBootPlan;
use Base\Foundation\ModuleManager\Public\ValueObjects\ModuleIdentifier;
use Base\Foundation\ModuleManager\Public\ValueObjects\ModuleState;

/**
 * Immutable boot plan produced by the orchestration pass.
 *
 * Holds the deterministic ordered list of module states.
 */
final readonly class DefaultModuleBootPlan implements ModuleBootPlan
{
    /** @var list<ModuleState> */
    private array $states;

    /** @var array<string, ModuleState> */
    private array $statesByName;

    /** @param list<ModuleState> $orderedStates */
    public function __construct(array $orderedStates)
    {
        $this->states = $orderedStates;

        $byName = [];

        foreach ($orderedStates as $state) {
            $byName[$state->identifier->name] = $state;
        }

        $this->statesByName = $byName;
    }

    /** @return list<ModuleIdentifier> */
    public function orderedIdentifiers(): array
    {
        return array_map(
            static fn (ModuleState $state): ModuleIdentifier => $state->identifier,
            $this->states,
        );
    }

    public function stateFor(ModuleIdentifier $identifier): ?ModuleState
    {
        return $this->statesByName[$identifier->name] ?? null;
    }

    /** @return list<ModuleState> */
    public function allStates(): array
    {
        return $this->states;
    }
}
