# DependencyResolver

## Ownership

- **Owner:** Base Platform
- **Classification:** base-owned
- **Category:** Foundation

## Purpose

Builds an immutable dependency graph from validated Manifest values, rejects invalid compositions, and returns a deterministic provider-first load order.

## Public Contracts

- `Public\\Contracts\\DependencyResolver` resolves an iterable of validated manifests.
- `Public\\Contracts\\DependencyGraph` exposes immutable node and edge lists.
- `Public\\ValueObjects\\DependencyNode` represents a manifest in the graph.
- `Public\\ValueObjects\\DependencyEdge` represents a consumer-to-provider declaration.
- `Public\\ValueObjects\\ResolutionResult` exposes the graph and ordered nodes.
- `Public\\Exceptions\\DependencyResolutionFailed` reports accumulated validation failures.

Public contracts contain no Laravel dependencies.

## Validation

The resolver rejects:

- missing required package or capability providers;
- forbidden category dependency directions;
- duplicate dependency declarations;
- empty version constraints;
- ambiguous capability providers without a selection strategy;
- circular dependencies.

Missing optional dependencies do not block resolution.

## Ordering

Topological order is provider-first. When multiple nodes are ready, manifest names are sorted lexicographically so input iteration order cannot change the result.

## Capabilities Provided

- `dependency.resolve` version `1.0.0`.

## Dependencies

- Manifest Public value objects, declared as package `Manifest` version `^0.1`.

## Version Foundation

Every dependency declaration must contain a non-empty version constraint. Full semantic version compatibility solving is deferred.

## Data Ownership

No tables or persistent state.

## Testing

```bash
composer test -- --filter=DependencyResolver
```

## Status

B2.2 graph construction, validation, cycle detection, and deterministic ordering implemented.
