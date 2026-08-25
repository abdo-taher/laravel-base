# Configuration

## Ownership

- **Owner:** Base Platform
- **Classification:** base-owned
- **Category:** Foundation

## Purpose

Framework-independent typed configuration Foundation.

This package owns:

- the `ConfigurationRepository` read contract
- the `ConfigurationSource` provider contract
- the `ConfigurationSourceContributor` extension hook
- the `LayeredConfigurationRepository` composition engine
- the `LaravelConfigurationSource` Infrastructure adapter
- typed key/value model (`ConfigurationKey`, `ConfigurationDefinition`)
- fail-closed validation (`ConfigurationKeyMissing`, `ConfigurationTypeMismatch`)

This package does **not** own:

- the meaning of any configuration key (`wallet.*`, `identity.*`, etc.)
- database-backed settings (→ Platform/Settings, deferred)
- feature flags (deferred)
- tenant configuration (deferred)
- secret storage or retrieval (deferred)

**Important distinction:**

> **Configuration** = technical application/package configuration.
> **Settings** = future user/admin/business-adjustable values (Platform).

## Precedence Model

Sources are composed by explicit integer priority. Accepted conventions:

| Priority | Meaning |
|---|---|
| 1 | Package defaults (registered by each package's service provider) |
| 10 | Project configuration (e.g., `LaravelConfigurationSource`) |
| 50 | Extension overrides (via `ConfigurationSourceContributor`) |
| 100 | Environment / runtime overrides (explicit, never implicit `env()`) |

Higher priority always wins for a given key. Same-priority sources: last registered wins. Avoid same-priority collisions.

## Public Contracts

| Contract | Kind | Purpose |
|---|---|---|
| `ConfigurationRepository` | interface | Primary typed read contract |
| `ConfigurationSource` | interface | Key-value provider with priority |
| `ConfigurationSourceContributor` | interface | Extension hook for project overrides |
| `ConfigurationKey` | readonly value object | Typed key: path, type, required flag |
| `ConfigurationDefinition` | readonly value object | Key + concrete value for source registration |
| `ConfigurationKeyMissing` | exception | Required key absent — always thrown, never silenced |
| `ConfigurationTypeMismatch` | exception | Value present but wrong PHP type |

All Public contracts are Laravel-free. No `config()`, `Config::`, `Facades`, or `env()` appear in the Public surface.

## Secret Safety

**Do not store secrets in `ConfigurationSource` implementations.**

Secrets (passwords, API keys, tokens) must not be placed in configuration
source arrays. Use environment variable references only as pointers, not
values, and delegate actual secret resolution to a future dedicated
secret-retrieval capability.

## Usage Example

### Defining a typed key (in the owning package)

```php
// In packages/base/Foundation/SomePackage/Configuration/SomePackageKeys.php
use Base\Foundation\Configuration\Public\ValueObjects\ConfigurationKey;

final class SomePackageKeys
{
    public static function cacheTtl(): ConfigurationKey
    {
        return new ConfigurationKey(
            path: 'some_package.cache_ttl_seconds',
            type: ConfigurationKey::TYPE_INT,
            required: false,
            default: 300,
        );
    }
}
```

### Registering package defaults (in the package service provider)

```php
use Base\Foundation\Configuration\Application\LayeredConfigurationRepository;
use Base\Foundation\Configuration\Public\Contracts\ConfigurationRepository;
use Base\Foundation\Configuration\Public\ValueObjects\ConfigurationDefinition;

$repository = $this->app->make(ConfigurationRepository::class);
assert($repository instanceof LayeredConfigurationRepository);
$repository->addSource(new class implements ConfigurationSource {
    public function priority(): int { return 1; }
    public function definitions(): array {
        return [new ConfigurationDefinition(SomePackageKeys::cacheTtl(), 300)];
    }
});
```

### Reading configuration (in a consumer)

```php
public function __construct(private readonly ConfigurationRepository $config) {}

public function handle(): void
{
    $ttl = $this->config->get(SomePackageKeys::cacheTtl()); // int
}
```

## Extension / Customisation

Implement `ConfigurationSourceContributor` in your project extension and
tag it in the service container:

```php
$this->app->tag(MyConfigOverrideContributor::class, 'configuration.source.contributor');
```

This adds your sources at the configured priority without modifying any
Base package internals.

## Dependencies

None. Configuration Foundation has no dependencies on other Foundation packages.

The Infrastructure `LaravelConfigurationSource` adapter uses
`Illuminate\Contracts\Config\Repository` — this stays inside
`Infrastructure/` and never crosses into `Public/` or `Application/`.

## Data Ownership

No tables. No migrations.

## Service Provider

`ConfigurationServiceProvider` — binds `ConfigurationRepository` and
collects tagged `ConfigurationSourceContributor` services.

## Testing

```bash
composer test -- --filter=Configuration
```

## Status

B3.2 implemented. Typed configuration foundation active. Settings, feature flags, tenant config, and secrets deferred.
