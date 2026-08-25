# Settings (Platform)

Provides a strongly-typed, runtime-adjustable global configuration store for project settings.

## Responsibility
The Settings package owns runtime-adjustable global/project settings. Examples include application title, maintenance mode configuration, and runtime limits. 

It explicitly **does not** own:
- Deployment configuration (e.g., `config/`, `.env`). These remain the domain of `Foundation.Configuration`.
- Secrets (e.g., passwords, API keys).
- Tenant, user, or organization-specific settings.

## Configuration vs Settings
- **Configuration:** Static, deploy-time, immutable at runtime.
- **Settings:** Dynamic, runtime-mutable, persisted in the database.

## Scope Model
The package currently implements a **Global / Project-wide** scope. There is no polymorphism, and no `tenant_id` or `user_id` context.

## Public API
The primary interfaces reside in `Public\Contracts\`:
- `SettingsReader::get(SettingKey|string $key): mixed`
- `SettingsWriter::set(SettingKey|string $key, mixed $value): void`
- `SettingsWriter::reset(SettingKey|string $key): void`
- `SettingsRegistry::register(SettingDefinition $definition): void`

## Type and Definition Model
Settings are strongly typed to primitive values defined by `SettingType`:
- `STRING`, `INTEGER`, `FLOAT`, `BOOLEAN`

Each setting requires a `SettingDefinition` to be registered. The definition controls the type, requirements, and default fallback.

## Persistence Model
Settings are stored in the package-owned `settings` table. 
- Writing to a setting utilizes a deterministic last-write-wins model.
- Deleting or resetting a setting removes the row from the database, meaning the next read will gracefully fall back to the definition's default value.

## Extension Status
The package provides a passive `SettingContributor` interface for projects and downstream modules to register their setting definitions. Automatic runtime discovery via the ExtensionRegistry is structurally planned but currently deferred.

## Optional Integrations and Security
- **Audit:** Integration is strictly optional and currently deferred. The package operates without `Foundation.Audit`.
- **Security:** Settings must not be used to store secrets, private tokens, or credentials. It operates as a clear-text persistence model.
- **Cache / Concurrency:** The initial B4.1 MVP avoids caching to ensure strong read-after-write consistency, relying on the underlying DB queries.

## Example
```php
use Base\Platform\Settings\Public\ValueObjects\SettingDefinition;
use Base\Platform\Settings\Public\ValueObjects\SettingKey;
use Base\Platform\Settings\Public\ValueObjects\SettingType;

// 1. Register a definition
$registry->register(new SettingDefinition(
    key: new SettingKey('maintenance.enabled'),
    type: SettingType::BOOLEAN,
    default: false
));

// 2. Write a value
$writer->set('maintenance.enabled', true);

// 3. Read a value
$isEnabled = $reader->get('maintenance.enabled'); // true
```
