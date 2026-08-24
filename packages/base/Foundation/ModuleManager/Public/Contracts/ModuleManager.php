<?php

declare(strict_types=1);

namespace Base\Foundation\ModuleManager\Public\Contracts;

use Base\Foundation\ModuleManager\Public\Exceptions\ModuleBootPlanFailed;
use Base\Foundation\ModuleManager\Public\Exceptions\ModuleDiscoveryFailed;

/**
 * Orchestration entry point for the Base runtime foundation.
 *
 * Responsibilities delegated internally:
 *   - Discovery:              ModuleDiscovery
 *   - Manifest validation:    ManifestReader (via discovery)
 *   - Dependency resolution:  DependencyResolver
 *   - Capability registration: CapabilityResolver
 *   - Extension registration: ExtensionRegistry
 *   - Boot order:             ModuleBootPlan
 *
 * No full lifecycle management. No business modules. No database.
 * No hard-coded module list. No Laravel types in this contract.
 */
interface ModuleManager
{
    /**
     * Discover manifests under the given paths, validate them, resolve
     * dependencies, register capabilities and extension points, and
     * return the deterministic boot plan.
     *
     * @param  iterable<string>  $searchPaths
     *
     * @throws ModuleDiscoveryFailed When a manifest cannot be read or is invalid.
     * @throws ModuleBootPlanFailed When dependencies cannot be resolved (missing,
     *                              cycle, duplicate identity, or ambiguous).
     */
    public function boot(iterable $searchPaths): ModuleBootPlan;
}
