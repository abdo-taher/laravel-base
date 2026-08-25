<?php

declare(strict_types=1);

namespace Base\Foundation\Configuration\Application;

use Base\Foundation\Configuration\Public\Contracts\ConfigurationRepository;
use Base\Foundation\Configuration\Public\Contracts\ConfigurationSource;
use Base\Foundation\Configuration\Public\Exceptions\ConfigurationKeyMissing;
use Base\Foundation\Configuration\Public\Exceptions\ConfigurationTypeMismatch;
use Base\Foundation\Configuration\Public\ValueObjects\ConfigurationKey;

/**
 * Composes multiple ConfigurationSource instances into a single
 * ConfigurationRepository using explicit priority ordering.
 *
 * Resolution algorithm:
 *   1. Sort all sources by priority descending (highest wins).
 *   2. For each source, index its definitions by key path.
 *   3. On get(): walk sources highest-first; return the first match
 *      that provides the requested key path.
 *   4. If no source provides the key and the key has a definition
 *      default, return the default.
 *   5. If the key is required and has no value, throw
 *      ConfigurationKeyMissing.
 *
 * Two sources at the same priority: the one registered later wins for
 * that key (stable sort is not guaranteed across all PHP versions, so
 * same-priority behaviour is documented but should be avoided by callers
 * using distinct priority values).
 *
 * No framework dependencies.
 */
final class LayeredConfigurationRepository implements ConfigurationRepository
{
    /**
     * Flat map: key path -> value, built once from sorted sources.
     *
     * @var array<string, mixed>
     */
    private array $resolved = [];

    private bool $built = false;

    /** @var list<ConfigurationSource> */
    private array $sources = [];

    public function addSource(ConfigurationSource $source): void
    {
        $this->sources[] = $source;
        $this->built = false; // invalidate cache on mutation
    }

    public function get(ConfigurationKey $key): mixed
    {
        $this->ensureBuilt();

        if (! array_key_exists($key->path, $this->resolved)) {
            if ($key->isOptional() && $key->hasDefault()) {
                return $this->assertType($key, $key->default);
            }

            if ($key->isOptional()) {
                return null;
            }

            throw new ConfigurationKeyMissing($key);
        }

        return $this->assertType($key, $this->resolved[$key->path]);
    }

    public function has(ConfigurationKey $key): bool
    {
        $this->ensureBuilt();

        return array_key_exists($key->path, $this->resolved);
    }

    public function getOrDefault(ConfigurationKey $key, mixed $fallback): mixed
    {
        $this->ensureBuilt();

        if (! array_key_exists($key->path, $this->resolved)) {
            return $fallback;
        }

        return $this->assertType($key, $this->resolved[$key->path]);
    }

    private function ensureBuilt(): void
    {
        if ($this->built) {
            return;
        }

        $sorted = $this->sources;

        // Sort ascending by priority so that higher-priority sources
        // overwrite lower-priority ones in the flat map.
        usort($sorted, static fn (
            ConfigurationSource $a,
            ConfigurationSource $b,
        ): int => $a->priority() <=> $b->priority());

        $resolved = [];

        foreach ($sorted as $source) {
            foreach ($source->definitions() as $definition) {
                $resolved[$definition->key->path] = $definition->value;
            }
        }

        $this->resolved = $resolved;
        $this->built = true;
    }

    private function assertType(ConfigurationKey $key, mixed $value): mixed
    {
        $actual = get_debug_type($value);

        $matches = match ($key->type) {
            ConfigurationKey::TYPE_STRING => is_string($value),
            ConfigurationKey::TYPE_INT => is_int($value),
            ConfigurationKey::TYPE_FLOAT => is_float($value) || is_int($value),
            ConfigurationKey::TYPE_BOOL => is_bool($value),
            ConfigurationKey::TYPE_ARRAY => is_array($value),
            default => false,
        };

        if (! $matches) {
            throw new ConfigurationTypeMismatch($key, $actual);
        }

        return $value;
    }
}
