# AccessControl

## Ownership

- **Owner:** Base Platform
- **Classification:** base-owned
- **Category:** Foundation
- **Security classification:** critical

## Purpose

Authorization evaluation infrastructure.

AccessControl owns the authorization boundary: it answers **"Can this
Principal perform this Action on this Resource?"** using explicit contracts
and value objects.

AccessControl does **not** own:

| Concern | Belongs to |
|---|---|
| Permission names (e.g. `wallet.view`) | Business modules |
| Roles (admin, vendor, customer) | Business/Platform modules |
| Authentication | Identity |
| User profile fields | Project extensions |
| Database-backed permission assignments | Future task (if needed) |
| Laravel Gate bridging | Future Infrastructure adapter |

## Authorization Model

```
Principal + Permission + ResourceType → PolicyEvaluator → AuthorizationDecision
```

Policies are stateless evaluators contributed by business modules.
AccessControl aggregates and dispatches them. The default when no policy
grants access is **deny** (fail-closed).

## Public Contracts

| Contract | Kind | Purpose |
|---|---|---|
| `AuthorizationChecker` | interface | Primary authorization evaluation entry point |
| `AuthorizationPolicy` | interface | Extension hook for policy contribution |
| `PermissionContributor` | interface | Extension hook for permission declaration |
| `Permission` | readonly value object | Named permission identifier |
| `ResourceType` | readonly value object | Resource category identifier |
| `AuthorizationDecision` | readonly value object | Explicit grant/deny outcome |
| `AccessDenied` | exception | Authorization failure — fail-closed |

All Public contracts are Laravel-free. No `Illuminate` types cross the Public boundary.

## Usage

### Checking Authorization

```php
public function __construct(private readonly AuthorizationChecker $auth) {}

// Explicit decision
$decision = $this->auth->check($principal, new Permission('wallet.view'));

// Boolean convenience
if ($this->auth->isGranted($principal, new Permission('wallet.transfer'))) {
    // ...
}

// Fail-closed assertion
$this->auth->demand($principal, new Permission('wallet.transfer'));
// throws AccessDenied if not granted
```

### Contributing Permissions

Business modules declare their permissions:

```php
final class WalletPermissionContributor implements PermissionContributor
{
    public function permissions(): array
    {
        return [
            new Permission('wallet.view'),
            new Permission('wallet.transfer'),
            new Permission('wallet.deposit'),
        ];
    }
}
```

### Contributing Policies

Business modules provide authorization logic:

```php
final class WalletAuthorizationPolicy implements AuthorizationPolicy
{
    public function supports(Permission $permission, ?ResourceType $resource = null): bool
    {
        return str_starts_with($permission->value, 'wallet.');
    }

    public function evaluate(
        Principal $principal,
        Permission $permission,
        ?ResourceType $resource = null,
    ): ?AuthorizationDecision {
        // Business authorization logic here
        // Return AuthorizationDecision::allow(), deny(), or null to abstain
    }
}
```

## Extension Model

Two contributor contracts:

1. **`PermissionContributor`** — declares permission names
2. **`AuthorizationPolicy`** — contributes evaluation logic

These follow the existing contributor pattern established by
`ConfigurationSourceContributor` in Configuration. Wiring to the full
ExtensionRegistry runtime is deferred to post-B3.

## Security Rules

- Authorization always fails closed. No permissive default.
- Unknown permissions are denied.
- Missing policies result in deny.
- `AccessDenied` messages include the permission name but not internal
  policy details.
- No product-specific permissions or roles are hard-coded.

## Persistence

No database persistence in B3.4. Policies and permissions are contributed
at runtime. If future persistence is needed, tables will be prefixed with
`access_control_` and owned exclusively by this package.

## Dependencies

- `Base\Foundation\Identity\Public\ValueObjects\Principal` (parameter type)

No other Foundation package dependencies.

## Service Provider

`AccessControlServiceProvider` — binds `AuthorizationChecker` to
`PolicyEvaluator`. Policy contributor wiring is deferred to the full
ExtensionRegistry runtime.

## Testing

```bash
composer test -- --filter=AccessControl
```

## Status

B3.4 implemented. Core authorization evaluation infrastructure delivered.
Roles, database persistence, Laravel Gate adapter, and ExtensionRegistry
wiring are deferred.
