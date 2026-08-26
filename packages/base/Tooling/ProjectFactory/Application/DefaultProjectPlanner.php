<?php

declare(strict_types=1);

namespace Base\Tooling\ProjectFactory\Application;

use Base\Foundation\DependencyResolver\Public\Contracts\DependencyResolver;
use Base\Foundation\DependencyResolver\Public\Exceptions\DependencyResolutionFailed;
use Base\Foundation\Manifest\Public\ValueObjects\Manifest;
use Base\Tooling\ProjectFactory\Public\Contracts\ProjectPlanner;
use Base\Tooling\ProjectFactory\Public\Exceptions\ProjectPlannerException;
use Base\Tooling\ProjectFactory\Public\ValueObjects\GenerationNode;
use Base\Tooling\ProjectFactory\Public\ValueObjects\GenerationPlan;
use Base\Tooling\ProjectFactory\Public\ValueObjects\Operations\CopyTreeOperation;
use Base\Tooling\ProjectFactory\Public\ValueObjects\Operations\GenerateProvidersBootstrapOperation;
use Base\Tooling\ProjectFactory\Public\ValueObjects\ProjectDefinition;
use Base\Tooling\ProjectFactory\Public\ValueObjects\SafePath;
use Base\Tooling\ProjectFactory\Public\ValueObjects\SelectionReason;

final readonly class DefaultProjectPlanner implements ProjectPlanner
{
    /**
     * @param  iterable<Manifest>  $catalog
     */
    public function __construct(
        private DependencyResolver $resolver,
        private iterable $catalog,
    ) {}

    public function plan(ProjectDefinition $definition): GenerationPlan
    {
        $manifests = [];
        foreach ($this->catalog as $manifest) {
            $manifests[] = $manifest;
        }

        try {
            $result = $this->resolver->resolve($manifests);
        } catch (DependencyResolutionFailed $e) {
            throw new ProjectPlannerException('Dependency resolution failed: '.$e->getMessage(), 0, $e);
        }

        // Map graph
        $providers = [];
        $adjacency = [];
        $nodeMap = [];

        foreach ($result->orderedNodes as $node) {
            $nodeMap[$node->name()] = $node;
            foreach ($node->manifest->capabilities as $cap) {
                $providers[$cap->name] = $node->name();
            }
        }

        foreach ($result->graph->edges() as $edge) {
            $adjacency[$edge->consumer->name()][] = $edge->provider->name();
        }

        $seeds = [];
        $explicitPackages = [];
        $explicitCapabilities = [];

        foreach ($definition->explicitModules as $dep) {
            if ($dep->targetType === 'package') {
                if (! isset($nodeMap[$dep->target])) {
                    throw ProjectPlannerException::unknownModule($dep->target);
                }
                $seeds[] = $dep->target;
                $explicitPackages[] = $dep->target;
            }
        }

        foreach ($definition->explicitCapabilities as $dep) {
            if ($dep->targetType === 'capability') {
                if (! isset($providers[$dep->target])) {
                    throw new ProjectPlannerException('Unresolvable explicit capability: '.$dep->target);
                }
                $seeds[] = $providers[$dep->target];
                $explicitCapabilities[] = $dep->target;
            }
        }

        // BFS to find all reachable nodes
        $reachable = [];
        $queue = $seeds;

        while (! empty($queue)) {
            $current = array_shift($queue);
            if (! isset($reachable[$current])) {
                $reachable[$current] = true;
                if (isset($adjacency[$current])) {
                    foreach ($adjacency[$current] as $provider) {
                        $queue[] = $provider;
                    }
                }
            }
        }

        $nodes = [];
        $operations = [];

        $providerClasses = ["App\Providers\AppServiceProvider::class"];

        foreach ($result->orderedNodes as $node) {
            if (! isset($reachable[$node->name()])) {
                continue;
            }

            $segments = explode('.', $node->name());
            $shortName = end($segments);
            $providerClasses[] = $node->manifest->namespace.'\\'.$shortName.'ServiceProvider::class';

            $reason = SelectionReason::AUTO_RESOLVED;
            if (in_array($node->name(), $explicitPackages, true)) {
                $reason = SelectionReason::DIRECT_MODULE;
            } else {
                foreach ($node->manifest->capabilities as $cap) {
                    if (in_array($cap->name, $explicitCapabilities, true)) {
                        $reason = SelectionReason::DIRECT_CAPABILITY;
                        break;
                    }
                }
            }

            $nodes[] = new GenerationNode($node->manifest, $reason);
            $segments = explode('.', $node->name());
            if ($segments[0] === 'Base') {
                $target = 'packages/base/'.$segments[1].'/'.$segments[2];
            } elseif ($segments[0] === 'Modules') {
                $target = 'modules/'.$segments[1];
            } else {
                $target = 'src/'.str_replace('.', '/', $node->name());
            }
            $operations[] = new CopyTreeOperation($node->name(), new SafePath($target));
        }

        $operations[] = new GenerateProvidersBootstrapOperation(
            new SafePath('bootstrap/providers.php'),
            $providerClasses
        );

        return new GenerationPlan(
            identity: $definition->identity,
            resolvedGraph: $nodes,
            filesystemOperations: $operations,
        );
    }
}
