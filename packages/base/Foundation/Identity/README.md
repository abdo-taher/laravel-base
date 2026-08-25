# Identity

## Ownership

- **Owner:** Base Platform
- **Classification:** base-owned
- **Category:** Foundation
- **Security classification:** critical

## Purpose

Authentication identity and principal primitives.

Identity owns the authenticated actor boundary. It defines *who* is making a
request, expressed as a typed, framework-free `Principal` value object.

Identity does **not** own:

| Concern | Belongs to |
|---|---|
| Roles, permissions, authorization policies | AccessControl (future) |
| Customer/vendor/provider profile fields | Project extensions |
| Wallet, product, order concepts | Product modules |
| Tenant business rules | Tenancy (future) |
| JWT / OAuth / Passport / Sanctum mechanics | Infrastructure adapters |
| Secret storage | Secrets capability (future) |

## Principal Model

```
Principal
  id:   PrincipalId    — typed non-empty string identifier
  type: PrincipalType  — 'user' | 'system' | 'api-key' | custom
```

No profile fields. No roles. No permissions.

## Public Contracts

| Contract | Kind | Purpose |
|---|---|---|
| `Authenticator` | interface | Accepts `Credentials`, returns `AuthenticationResult` or throws |
| `Credentials` | marker interface | Mechanism-agnostic credential boundary |
| `CurrentPrincipal` | interface | Active principal for the current request context |
| `PrincipalEnricher` | interface | Extension hook for profile contribution |
| `Principal` | readonly value object | Authenticated identity: id + type |
| `PrincipalId` | readonly value object | Typed non-empty string identifier |
| `PrincipalType` | readonly value object | Named type with USER, SYSTEM, API_KEY constants |
| `AuthenticationResult` | readonly value object | Always-successful outcome — contains Principal, no success flag |
| `EmailPasswordCredentials` | readonly value object | Implements `Credentials`; email + password |
| `AuthenticationFailed` | exception | Invalid or unsupported credentials — always thrown, enumeration-safe |
| `AuthenticationRequired` | exception | No authenticated principal in current context |

All Public contracts are Laravel-free. No `Illuminate` types cross the Public boundary.

### AuthenticationResult invariant (B3.3.1)

`AuthenticationResult` always and only represents successful authentication.
It contains a non-nullable `Principal` and no `success` flag.
Failure is always signalled by throwing `AuthenticationFailed` — never by a flag or null.

## Authentication

```php
// Inject the contract, not the implementation
public function __construct(private readonly Authenticator $auth) {}

// Authenticator::authenticate() accepts any Credentials implementation
$result = $this->auth->authenticate(new EmailPasswordCredentials($email, $password));
$principal = $result->principal; // Principal — always present; no success flag
```

`AuthenticationFailed` is always thrown on invalid or unsupported credentials.
`AuthenticationResult` never carries a failure state — it always contains a `Principal`.

## Current Principal

```php
// For code paths that require authentication
public function __construct(private readonly CurrentPrincipal $currentPrincipal) {}

$principal = $this->currentPrincipal->get();   // throws AuthenticationRequired if not authenticated
$principal = $this->currentPrincipal->find();  // returns null if not authenticated
$bool      = $this->currentPrincipal->isAuthenticated();
```

## Extension / Profile Enrichment

Implement `PrincipalEnricher` in your project extension:

```php
// extensions/Base/Identity/CustomerProfileEnricher.php
final class CustomerProfileEnricher implements PrincipalEnricher
{
    public function enrich(Principal $principal): Principal
    {
        // attach project-specific context — must return a Principal
        return $principal;
    }
}
```

Tag it in the service container as `'identity.principal-enricher'`.
Enrichment pipeline wiring is deferred to post-B3.

## Security Rules

- Authentication always fails closed. No permissive default.
- `AuthenticationFailed` messages do not reveal whether the identifier
  or credential was incorrect (prevents user enumeration).
- No plaintext password storage. The `Authenticator` implementation
  performs secure credential comparison internally.
- Infrastructure adapters are not part of the Public API.

## Persistence Ownership

Identity declares ownership of `users`, `password_reset_tokens`, and `sessions` in `module.json`.

**Transitional state — B3.3.1 clarification:**

| Aspect | Target architecture | Current transitional state |
|---|---|---|
| Table ownership | `Base\Foundation\Identity` | Declared in `module.json`; not yet enforced |
| Physical migration | `Identity/Database/Migrations/` | Still at `database/migrations/` (Laravel host level) |
| Eloquent model | `Identity\Infrastructure\IdentityUser` | Still `App\Models\User` (host scope) |

The current `App\Models\User` and the root Laravel migration are **not** the final architecture.
They are transitional placeholders. The following is a **mandatory future task** before
Identity persistence is considered architecturally complete:

> **TODO (mandatory):** Transfer physical migration ownership and Eloquent model to
> `packages/base/Foundation/Identity/Database/Migrations/` and
> `Identity\Infrastructure\IdentityUser`. Remove `App\Models\User` from the host
> and update `config/auth.php` to reference the Identity-owned model.

Do not treat the current state as approved architecture. The boundary is documented
here to prevent it from being forgotten.

## Dependencies

None. Identity Foundation has no dependencies on other Foundation packages.

## Service Provider

`IdentityServiceProvider` — binds `Authenticator` and `CurrentPrincipal`
to their Laravel-backed Infrastructure implementations.

## Testing

```bash
composer test -- --filter=Identity
```

## Status

B3.3 + B3.3.1 implemented. Core authentication and principal identity contracts hardened.
`AuthenticationResult` invariant enforced (no success flag). `Credentials` abstraction
introduced. Authorization (AccessControl), profile enrichment pipeline, and migration
ownership transfer are deferred.
