# B1 Governance Validation Layer

## Status

IMPLEMENTATION PLANNED

## Goal

Add a repository-level documentation governance check without changing runtime code or architecture contracts.

## Implementation Plan

### Affected Files

- `scripts/validate-architecture-docs.sh` — add the architecture documentation validator.
- `docs/roadmap/B1-governance-validation.md` — record this plan and the validation outcome.

### Affected Packages

None. The change is limited to a repository script and this B1 roadmap record.

### Dependency Impact

None. The validator uses Bash and standard command-line tools already used by repository scripts. Composer dependencies and runtime dependency direction are unchanged.

### Public Contracts Introduced

None. No runtime API, module contract, capability, event, contributor, or strategy is introduced.

### Tests Required

- Run `./scripts/validate-architecture-docs.sh` against the repository.
- Confirm it validates required architecture documents, corruption markers, the canonical source of truth, roadmap document references, and the accepted ownership model.

### Rollback Considerations

Rollback consists of removing the validator and this roadmap record. No application state, package state, persistence, generated artifact, or dependency lockfile is affected.

## Scope Constraints

- Do not modify runtime or application code.
- Do not modify architecture documents.
- Do not modify Composer, Deptrac, or PHPStan configuration.
- Do not reinterpret or redesign accepted architecture decisions.
