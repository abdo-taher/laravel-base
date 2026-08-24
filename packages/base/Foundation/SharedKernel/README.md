# SharedKernel

## Ownership

- **Owner:** Base Platform
- **Classification:** base-owned
- **Category:** Foundation

## Purpose

SharedKernel provides the minimal set of domain-neutral, stable primitives
that are genuinely shared across multiple Base Foundation packages.

**This package must remain extremely small.**

A primitive belongs here only when:

1. it is domain-neutral;
2. it has stable semantics;
3. multiple Base packages genuinely need the same concept;
4. duplicating it would create incompatible platform semantics.

## Primitives

| Primitive | Kind | Consumers |
|---|---|---|
| `SemanticVersion` | readonly value object | Manifest, CapabilityRegistry |
| `InvalidSemanticVersion` | exception | Manifest, CapabilityRegistry |

## Public Contracts

```
Base\Foundation\SharedKernel\Public\ValueObjects\SemanticVersion
Base\Foundation\SharedKernel\Public\Exceptions\InvalidSemanticVersion
```

No Laravel types. No framework coupling. No service provider required.

## SemanticVersion

Parses and validates a version string conforming to semver.org:
`MAJOR.MINOR.PATCH[-prerelease][+build]`

```php
$v = SemanticVersion::from('1.2.3');
$v->major;        // 1
$v->minor;        // 2
$v->patch;        // 3
$v->preRelease;   // null
$v->value;        // '1.2.3'

SemanticVersion::isValid('1.0.0');   // true
SemanticVersion::isValid('bad');     // false

$v->compareTo(SemanticVersion::from('1.3.0'));  // negative
```

## Rejected Candidates (B3.1)

The following were evaluated and rejected or deferred:

| Candidate | Decision | Reason |
|---|---|---|
| Clock contract | Deferred | No current Foundation consumer pair |
| Identifier abstraction | Rejected | Each package has domain-specific identity needs |
| Result/Error primitives | Rejected | Speculative; all packages use typed exceptions |
| Pagination primitives | Rejected | Not a Foundation concern |

## Data Ownership

No tables. No migrations. No persistence.

## Service Provider

None required. Value objects are instantiated directly.

## Testing

```bash
composer test -- --filter=SharedKernel
```

## Status

B3.1 implemented. One primitive: `SemanticVersion`.
