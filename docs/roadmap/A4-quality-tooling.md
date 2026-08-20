# A4 — Enterprise Quality Tooling

## Goal

Establish mandatory quality gates before implementing any Base Platform module.

## Scope

A4 will add:

- formatting enforcement
- static analysis
- dependency architecture validation
- security scanning configuration
- dependency audit commands
- unified Composer quality commands
- CI-ready validation entrypoints

## Planned Steps

### A4.1 Formatting

- Laravel Pint
- repository formatting rules
- check-only command for CI

### A4.2 Static Analysis

- PHPStan / Larastan
- baseline-free configuration
- progressive strictness
- framework-aware analysis

### A4.3 Architecture Validation

- Deptrac
- layer boundaries
- future module boundary rules

### A4.4 Security Gates

- Gitleaks configuration
- Composer audit
- secure dependency policy

### A4.5 Unified Commands

Composer scripts for:

- format
- format:check
- analyse
- architecture
- test
- security
- quality

### A4.6 CI Foundation

Prepare commands that future CI will execute without creating CI workflows yet.

## Rules

- Do not weaken rules simply to make checks pass.
- Do not create a PHPStan baseline unless explicitly justified.
- Do not add architecture exceptions for code that can be fixed.
- Do not implement modules during A4.
- Every sub-step must pass validation before being marked complete.

## Exit Criteria

A4 is complete when:

- formatting check passes
- static analysis passes
- architecture validation passes for the current clean skeleton
- security scan passes
- dependency audit passes
- tests pass
- unified quality command passes

## Current Step

A4.1 — Formatting.

## A4.2 Implementation Plan — Static Analysis

### Goal

Add Laravel-aware static analysis before implementing modules.

### Decisions

- Use Larastan 3.x.
- Use PHPStan 2.x through Larastan.
- Start without a baseline file.
- Start at level 8.
- Increase to the maximum practical level before Module Kernel implementation.
- Analyse application, bootstrap, database, routes, and tests.
- Future Modules/ paths will be added when module structure exists.
- Do not suppress errors globally.
- Fix real errors rather than ignoring them.
- Framework-specific exceptions require documentation.

### Validation

A4.2 is complete when:

- Larastan installs successfully.
- PHPStan configuration validates.
- Static analysis exits successfully.
- No PHPStan baseline exists.
- Formatting still passes.
- Tests still pass.

### Deferred Baseline Observation

Laravel's default Composer `post-create-project-cmd` still creates a local SQLite database.

This is intentionally not changed during static-analysis setup.

It must be reviewed when the Base environment and PostgreSQL infrastructure baseline is established. The final Base project bootstrap must not silently select SQLite when the project blueprint declares PostgreSQL.

## A4.2 Validation Result

Status: COMPLETE

Validated on the clean Laravel baseline with:

- Larastan 3.10.x
- PHPStan 2.2.x
- PHPStan level 8
- no PHPStan baseline
- no globally ignored analysis errors
- application, bootstrap, database, routes, and tests included in analysis

Validation gates:

- `composer analyse` — PASS, no errors
- `composer format:check` — PASS
- `composer test` — PASS
- `composer validate` — PASS
- `composer audit` — PASS, no known vulnerability advisories
- `git diff --check` — PASS

The default meaningless unit assertion was replaced with a deterministic behavioral assertion rather than suppressing the PHPStan finding.

A4.2 is complete.

## A4.3 Implementation Plan — Architecture Dependency Enforcement

### Goal

Introduce machine-enforced architecture dependency rules before the Base module system is implemented.

The architecture validator must evolve with the repository and eventually enforce the module isolation model rather than relying only on documentation.

### Tool

Use Deptrac as the PHP dependency architecture validator.

### Initial Skeleton Scope

The current repository does not yet contain the final module architecture.

Therefore A4.3 begins with rules for the existing Laravel host/composition shell and prepares explicit future module boundaries without inventing fake modules only to satisfy the tool.

### Future Architecture Direction

The target dependency direction is:

Experience
→ Product
→ Platform
→ Foundation

Cross-module dependencies must use declared public contracts.

Implementation internals of one module must never become an allowed dependency of another module.

### Planned Enforcement

The architecture validation will evolve to enforce:

1. Laravel host/composition code does not become a business domain.
2. Domain modules do not depend directly on another module's internals.
3. Product modules may depend on Platform and Foundation public contracts.
4. Platform modules may depend on Foundation public contracts.
5. Foundation modules must not depend on Product, Experience, or optional business modules.
6. Experience modules may compose capabilities but must not become owners of domain behavior.
7. Circular module dependencies are forbidden.
8. Cross-module persistence access is forbidden by architecture and additional validation.
9. Public contracts and internal implementations will become separately enforceable layers once Modules/ exists.
10. Architecture exceptions must be explicit and justified; no blanket skip rules.

### A4.3 Execution Steps

1. Install a compatible Deptrac release.
2. Record the exact installed version.
3. Create the initial Deptrac configuration for the clean Laravel skeleton.
4. Add a Composer architecture-check command.
5. Run Deptrac against the current repository.
6. Fix genuine violations rather than suppressing them.
7. Document any limitations that cannot be enforced until Modules/ exists.
8. Run all existing A4 quality gates again.
9. Mark A4.3 complete only after all gates pass.

### Non-Goals

Do not implement during A4.3:

- Module Kernel
- Module Manager
- SharedKernel
- Identity
- AccessControl
- Settings
- Product
- Cart
- Wallet
- module manifests
- module runtime lifecycle
- PostgreSQL infrastructure
- Redis
- MinIO
- Docker

A4.3 establishes enforcement infrastructure only.

## Current Step

A4.3 — Architecture dependency enforcement planning complete; implementation not started.

## A4.3.2 Implementation — Initial Host Architecture Gate

### Goal

Replace the generic Deptrac template with a Laravel-host-specific architecture configuration.

### Current Scope

Before Modules/ exists, enforce only host-layer boundaries:

- Host HTTP
- Host Models
- Host Providers
- Bootstrap
- Database
- Routes
- Tests

This is intentionally temporary and will be replaced/extended when B1 introduces the real module tree.

### Rules

- Host HTTP may depend on Host Models and framework code.
- Host Providers may compose framework services but must not become business modules.
- Routes may depend on host HTTP/framework entrypoints.
- Database code must not become a shared business layer.
- Tests may depend on application code.
- No fake module layers will be introduced merely to satisfy Deptrac.

### Future Upgrade

When Modules/ exists, Deptrac must enforce:

Experience
→ Product
→ Platform
→ Foundation

and Public-vs-Internal module boundaries.

### Validation

A4.3.2 is complete when:

- Deptrac configuration loads successfully.
- Deptrac analysis passes on the current skeleton.
- `.deptrac.cache` is ignored.
- No blanket architecture skips are introduced.

## A4.3.3 Plan — Internal Architecture Coverage

### Observation

The initial Deptrac host gate reports 13 uncovered dependencies.

Manual inspection confirms that all 13 are dependencies from Base host code to external framework/testing types:

- Laravel Eloquent and authentication base classes
- Laravel traits and service provider base classes
- Laravel database factory/seeder infrastructure
- Laravel facades/utilities
- Laravel testing infrastructure
- PHPUnit testing infrastructure

No uncovered dependency currently represents a Base-to-Base architectural relationship.

### Decision

Do not create fake Laravel or PHPUnit architecture layers merely to force the uncovered count to zero.

Deptrac architecture layers represent Base-owned architectural boundaries, not third-party package ownership.

The architecture gate must distinguish:

1. forbidden Base-to-Base dependencies;
2. allowed Base-to-Base dependencies;
3. external framework/library dependencies.

External dependencies are governed separately through Composer constraints, Composer audit, security scanning, and future dependency policy.

### Current Host Gate

The current host layers remain:

- HostHttp
- HostModels
- HostProviders
- Bootstrap
- Database
- Routes
- Tests

The current gate must fail on architecture violations.

### Future Module Gate

When Modules/ is introduced, Deptrac must evolve to enforce:

Experience
→ Product
→ Platform
→ Foundation

and additionally:

- module internals are private;
- cross-module access uses Public contracts;
- undeclared module dependencies are forbidden;
- circular dependencies are forbidden;
- Foundation cannot depend on higher layers;
- Platform cannot depend on Product or Experience;
- Product cannot depend on Experience.

### Additional Enforcement

Deptrac alone will not be considered sufficient for full module isolation.

Future architecture validation will also cover:

- manifest dependency declarations;
- public/internal namespace boundaries;
- table ownership;
- migration ownership;
- cross-module persistence access;
- optional-module disable tests;
- capability contracts;
- circular module dependency detection.

### Exit Rule

A4.3 may complete with external framework dependencies reported as uncovered only when:

- every uncovered dependency has been reviewed;
- no uncovered Base-owned dependency exists;
- violations are zero;
- skipped violations are zero;
- warnings are zero;
- architecture command exits successfully;
- the limitation is explicitly documented.

No blanket skip-violation configuration or architecture baseline is permitted.

## A4.3 Validation Result

Status: COMPLETE

Architecture enforcement was validated with:

- Deptrac 4.7.1
- zero architecture violations
- zero skipped violations
- zero warnings
- external Laravel/PHPUnit dependencies reviewed separately from Base-owned architecture
- custom uncovered-dependency validation for Base-owned namespaces
- no architecture baseline
- no blanket skip rules

Negative enforcement test:

A temporary `App\Models\ArchitectureProbe` was created with a forbidden dependency on `App\Providers\AppServiceProvider`.

Expected result:

- Deptrac rejected `HostModels -> HostProviders`
- architecture command exited with failure

After removing the probe:

- `composer architecture` — PASS
- `composer architecture:coverage` — PASS
- `composer analyse` — PASS
- `composer format:check` — PASS
- `composer test` — PASS
- `composer validate` — PASS
- `composer audit` — PASS
- `git diff --check` — PASS

A4.3 is complete.

## Current Step

A4.4 — Security gates and unified quality command.

## A4.4 Implementation Plan — Security Gates and Unified Quality Command

### Goal

Create one repeatable quality entrypoint for developers, AI agents, local validation, and future CI.

### Required Gates

- Composer validation
- formatting validation
- static analysis
- architecture validation
- architecture coverage validation
- automated tests
- dependency security audit
- secret scanning

### Unified Quality Contract

The repository must expose:

`composer quality`

The command must fail if any mandatory gate fails.

### Rules

- Security failures must not be downgraded to warnings.
- Secret scanning must redact secret values.
- No blanket ignores or baselines are introduced just to make validation pass.
- Quality commands must work from a clean checkout after dependencies are installed.
- No Base module implementation begins before the quality contract passes.

### Planned Composer Commands

- `composer format:check`
- `composer analyse`
- `composer architecture`
- `composer architecture:coverage`
- `composer test`
- `composer security:dependencies`
- `composer security:secrets`
- `composer quality`

### Exit Criteria

A4.4 is complete when every individual gate passes and `composer quality` passes.

## A4.4 Validation Result

Status: COMPLETE

The security and unified quality contract was validated successfully.

Secret scanning covers both:

- Git history through `gitleaks detect`
- the current working tree through `gitleaks detect --no-git`

Negative secret detection test:

A temporary synthetic GitHub token was placed in an untracked working-tree file.

Expected result:

- Git-history scan passed
- working-tree scan detected one leak
- `composer security:secrets` exited with failure

The probe was removed immediately.

After removal:

- `composer security:secrets` — PASS
- `composer quality` — PASS

Validated quality gates:

- Composer validation — PASS
- Pint formatting validation — PASS
- Larastan/PHPStan static analysis — PASS
- Deptrac architecture validation — PASS
- Base-owned architecture coverage validation — PASS
- PHPUnit tests — PASS
- Composer dependency audit — PASS
- Git-history secret scan — PASS
- working-tree secret scan — PASS

No secret baseline or blanket security exclusion was introduced.

A4.4 is complete.

## A4.5 Implementation Plan — Final Quality Tooling Review

### Goal

Review, validate, and commit the complete A4 quality-tooling milestone before any Base module implementation begins.

### Review Scope

Verify:

- Pint configuration
- PHPStan/Larastan configuration
- Deptrac configuration
- architecture coverage scripts
- Gitleaks configuration
- secret scan scripts
- Composer quality scripts
- dependency locks
- roadmap accuracy
- ignore rules
- absence of temporary probe files

### Required Evidence

Before the milestone is committed:

- `composer quality` passes
- `git diff --check` passes
- no temporary architecture probe exists
- no temporary secret probe exists
- no runtime cache is staged
- no secrets are staged
- no local database is staged
- no vendor or node_modules files are staged

### Milestone Rule

Do not begin B1 — Module Structure Contract until this milestone is committed and the working tree is clean.

## Current Step

A4.5 — Final quality-tooling review.

