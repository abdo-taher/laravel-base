<?php

declare(strict_types=1);

namespace Base\Foundation\DependencyResolver\Application;

use Base\Foundation\DependencyResolver\Public\Contracts\DependencyResolver;
use Base\Foundation\DependencyResolver\Public\Exceptions\DependencyResolutionFailed;
use Base\Foundation\DependencyResolver\Public\ValueObjects\DependencyEdge;
use Base\Foundation\DependencyResolver\Public\ValueObjects\DependencyNode;
use Base\Foundation\DependencyResolver\Public\ValueObjects\ResolutionResult;
use Base\Foundation\Manifest\Public\ValueObjects\Manifest;
use Base\Foundation\Manifest\Public\ValueObjects\ManifestDependency;

final class ManifestDependencyResolver implements DependencyResolver
{
    private const ALLOWED_TARGET_CATEGORIES = [
        'Foundation' => ['Foundation'],
        'Platform' => ['Foundation', 'Platform'],
        'Specialized' => ['Foundation', 'Platform'],
        'Extension' => ['Foundation', 'Platform', 'Specialized'],
        'Product' => ['Foundation', 'Platform', 'Specialized', 'Extension'],
    ];

    public function resolve(iterable $manifests): ResolutionResult
    {
        $errors = [];
        $nodes = $this->indexNodes($manifests, $errors);
        $capabilityProviders = $this->indexCapabilityProviders($nodes);
        $edges = [];

        foreach ($nodes as $consumer) {
            $declaredDependencies = [];

            foreach ($consumer->manifest->dependencies as $dependency) {
                $declarationKey = $dependency->targetType.':'.$dependency->target;

                if (isset($declaredDependencies[$declarationKey])) {
                    $errors[] = sprintf(
                        '%s declares dependency %s more than once',
                        $consumer->name(),
                        $declarationKey,
                    );

                    continue;
                }

                $declaredDependencies[$declarationKey] = true;

                if (trim($dependency->version) === '') {
                    $errors[] = sprintf(
                        '%s dependency %s must declare a version constraint',
                        $consumer->name(),
                        $declarationKey,
                    );

                    continue;
                }

                $provider = $this->providerFor(
                    $dependency,
                    $nodes,
                    $capabilityProviders,
                    $consumer,
                    $errors,
                );

                if ($provider === null) {
                    continue;
                }

                if (! $this->directionIsAllowed($consumer, $provider)) {
                    $errors[] = sprintf(
                        'forbidden dependency direction: %s (%s) -> %s (%s)',
                        $consumer->name(),
                        $consumer->category(),
                        $provider->name(),
                        $provider->category(),
                    );

                    continue;
                }

                $edges[] = new DependencyEdge(
                    consumer: $consumer,
                    provider: $provider,
                    targetType: $dependency->targetType,
                    versionConstraint: $dependency->version,
                    required: $dependency->required,
                );
            }
        }

        if ($errors !== []) {
            throw new DependencyResolutionFailed($errors);
        }

        $this->sortEdges($edges);
        $orderedNodes = $this->topologicalOrder($nodes, $edges);

        return new ResolutionResult(
            new ImmutableDependencyGraph(array_values($nodes), $edges),
            $orderedNodes,
        );
    }

    /**
     * @param  iterable<Manifest>  $manifests
     * @param  list<string>  $errors
     * @return array<string, DependencyNode>
     */
    private function indexNodes(iterable $manifests, array &$errors): array
    {
        $nodes = [];

        foreach ($manifests as $manifest) {
            if (isset($nodes[$manifest->name])) {
                $errors[] = sprintf('manifest name %s is declared more than once', $manifest->name);

                continue;
            }

            $nodes[$manifest->name] = new DependencyNode($manifest);
        }

        ksort($nodes, SORT_STRING);

        return $nodes;
    }

    /**
     * @param  array<string, DependencyNode>  $nodes
     * @return array<string, list<DependencyNode>>
     */
    private function indexCapabilityProviders(array $nodes): array
    {
        $providers = [];

        foreach ($nodes as $node) {
            foreach ($node->manifest->capabilities as $capability) {
                $providers[$capability->name][$node->name()] = $node;
            }
        }

        $indexedProviders = [];

        foreach ($providers as $capability => $capabilityProviders) {
            ksort($capabilityProviders, SORT_STRING);
            $indexedProviders[$capability] = array_values($capabilityProviders);
        }

        return $indexedProviders;
    }

    /**
     * @param  array<string, DependencyNode>  $nodes
     * @param  array<string, list<DependencyNode>>  $capabilityProviders
     * @param  list<string>  $errors
     */
    private function providerFor(
        ManifestDependency $dependency,
        array $nodes,
        array $capabilityProviders,
        DependencyNode $consumer,
        array &$errors,
    ): ?DependencyNode {
        if ($dependency->targetType === 'package') {
            $provider = $nodes[$dependency->target] ?? null;
        } else {
            $providers = $capabilityProviders[$dependency->target] ?? [];

            if (count($providers) > 1) {
                $errors[] = sprintf(
                    '%s capability %s has multiple providers and requires a selection strategy',
                    $consumer->name(),
                    $dependency->target,
                );

                return null;
            }

            $provider = $providers[0] ?? null;
        }

        if ($provider === null && $dependency->required) {
            $errors[] = sprintf(
                '%s requires missing %s dependency %s',
                $consumer->name(),
                $dependency->targetType,
                $dependency->target,
            );
        }

        return $provider;
    }

    private function directionIsAllowed(DependencyNode $consumer, DependencyNode $provider): bool
    {
        return in_array(
            $provider->category(),
            self::ALLOWED_TARGET_CATEGORIES[$consumer->category()] ?? [],
            true,
        );
    }

    /** @param list<DependencyEdge> $edges */
    private function sortEdges(array &$edges): void
    {
        usort(
            $edges,
            static fn (DependencyEdge $left, DependencyEdge $right): int => [
                $left->consumer->name(),
                $left->provider->name(),
                $left->targetType,
            ] <=> [
                $right->consumer->name(),
                $right->provider->name(),
                $right->targetType,
            ],
        );
    }

    /**
     * @param  array<string, DependencyNode>  $nodes
     * @param  list<DependencyEdge>  $edges
     * @return list<DependencyNode>
     */
    private function topologicalOrder(array $nodes, array $edges): array
    {
        $dependencyCounts = array_fill_keys(array_keys($nodes), 0);
        $dependents = [];

        foreach ($edges as $edge) {
            $dependencyCounts[$edge->consumer->name()]++;
            $dependents[$edge->provider->name()][] = $edge->consumer->name();
        }

        foreach ($dependents as &$dependentNames) {
            sort($dependentNames, SORT_STRING);
        }

        $ready = [];

        foreach ($dependencyCounts as $name => $count) {
            if ($count === 0) {
                $ready[] = $name;
            }
        }

        sort($ready, SORT_STRING);
        $ordered = [];

        while ($ready !== []) {
            $name = array_shift($ready);
            $ordered[] = $nodes[$name];

            foreach ($dependents[$name] ?? [] as $dependent) {
                $dependencyCounts[$dependent]--;

                if ($dependencyCounts[$dependent] === 0) {
                    $ready[] = $dependent;
                    sort($ready, SORT_STRING);
                }
            }
        }

        if (count($ordered) !== count($nodes)) {
            $cyclicNodes = array_keys(array_filter(
                $dependencyCounts,
                static fn (int $count): bool => $count > 0,
            ));
            sort($cyclicNodes, SORT_STRING);

            throw new DependencyResolutionFailed([
                'circular dependency detected involving: '.implode(', ', $cyclicNodes),
            ]);
        }

        return $ordered;
    }
}
