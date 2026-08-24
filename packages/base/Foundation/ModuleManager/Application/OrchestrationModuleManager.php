<?php

declare(strict_types=1);

namespace Base\Foundation\ModuleManager\Application;

use Base\Foundation\CapabilityRegistry\Public\Contracts\CapabilityResolver;
use Base\Foundation\CapabilityRegistry\Public\ValueObjects\CapabilityName;
use Base\Foundation\CapabilityRegistry\Public\ValueObjects\CapabilityProviderDefinition;
use Base\Foundation\CapabilityRegistry\Public\ValueObjects\CapabilityVersion;
use Base\Foundation\DependencyResolver\Public\Contracts\DependencyResolver;
use Base\Foundation\DependencyResolver\Public\Exceptions\DependencyResolutionFailed;
use Base\Foundation\DependencyResolver\Public\ValueObjects\DependencyNode;
use Base\Foundation\Manifest\Public\ValueObjects\Manifest;
use Base\Foundation\ModuleManager\Public\Contracts\ModuleBootPlan;
use Base\Foundation\ModuleManager\Public\Contracts\ModuleDiscovery;
use Base\Foundation\ModuleManager\Public\Contracts\ModuleManager;
use Base\Foundation\ModuleManager\Public\Exceptions\ModuleBootPlanFailed;
use Base\Foundation\ModuleManager\Public\ValueObjects\ModuleIdentifier;
use Base\Foundation\ModuleManager\Public\ValueObjects\ModuleState;

/**
 * Orchestrates the B2.5 ModuleManager foundation pass:
 *
 *   1. Discover manifests via ModuleDiscovery.
 *   2. Reject duplicate module identities (fail closed).
 *   3. Resolve dependency order via DependencyResolver.
 *   4. Register declared capabilities into CapabilityResolver.
 *   5. Return a DefaultModuleBootPlan in topological order.
 *
 * Extension point registration from manifests is deferred until the Manifest
 * value object carries extension_points metadata (post B2.5).
 *
 * No full lifecycle management. No database. No business modules.
 */
final class OrchestrationModuleManager implements ModuleManager
{
    public function __construct(
        private readonly ModuleDiscovery $discovery,
        private readonly DependencyResolver $dependencyResolver,
        private readonly CapabilityResolver $capabilityResolver,
    ) {}

    public function boot(iterable $searchPaths): ModuleBootPlan
    {
        $manifests = $this->discovery->discover($searchPaths);

        $this->assertNoDuplicateIdentities($manifests);

        $orderedNodes = $this->resolveOrder($manifests);

        $this->registerCapabilities($orderedNodes);

        return $this->buildPlan($orderedNodes);
    }

    /**
     * @param  list<Manifest>  $manifests
     *
     * @throws ModuleBootPlanFailed
     */
    private function assertNoDuplicateIdentities(array $manifests): void
    {
        $seen = [];

        foreach ($manifests as $manifest) {
            if (isset($seen[$manifest->name])) {
                throw ModuleBootPlanFailed::duplicateIdentity($manifest->name);
            }

            $seen[$manifest->name] = true;
        }
    }

    /**
     * @param  list<Manifest>  $manifests
     * @return list<DependencyNode>
     *
     * @throws ModuleBootPlanFailed
     */
    private function resolveOrder(array $manifests): array
    {
        try {
            $result = $this->dependencyResolver->resolve($manifests);
        } catch (DependencyResolutionFailed $e) {
            throw ModuleBootPlanFailed::fromResolutionErrors($e->getMessage());
        }

        return $result->orderedNodes;
    }

    /**
     * Registers each manifest's declared capabilities into the capability resolver.
     *
     * @param  list<DependencyNode>  $orderedNodes
     */
    private function registerCapabilities(array $orderedNodes): void
    {
        foreach ($orderedNodes as $node) {
            foreach ($node->manifest->capabilities as $capability) {
                $this->capabilityResolver->register(new CapabilityProviderDefinition(
                    name: new CapabilityName($capability->name),
                    version: new CapabilityVersion($capability->version),
                    provider: new ManifestCapabilityProvider($node->manifest),
                ));
            }
        }
    }

    /**
     * @param  list<DependencyNode>  $orderedNodes
     */
    private function buildPlan(array $orderedNodes): DefaultModuleBootPlan
    {
        $states = array_map(
            static fn (DependencyNode $node): ModuleState => new ModuleState(
                identifier: new ModuleIdentifier($node->name(), $node->category()),
                state: ModuleState::READY,
            ),
            $orderedNodes,
        );

        return new DefaultModuleBootPlan($states);
    }
}
