<?php

declare(strict_types=1);

namespace Tests\Unit\Packages\Base\Foundation\Configuration;

use Base\Foundation\Configuration\Application\LayeredConfigurationRepository;
use Base\Foundation\Configuration\Public\Contracts\ConfigurationSource;
use Base\Foundation\Configuration\Public\Exceptions\ConfigurationKeyMissing;
use Base\Foundation\Configuration\Public\Exceptions\ConfigurationTypeMismatch;
use Base\Foundation\Configuration\Public\ValueObjects\ConfigurationDefinition;
use Base\Foundation\Configuration\Public\ValueObjects\ConfigurationKey;
use PHPUnit\Framework\TestCase;

final class LayeredConfigurationRepositoryTest extends TestCase
{
    private LayeredConfigurationRepository $repository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = new LayeredConfigurationRepository;
    }

    // ── Positive: typed retrieval ────────────────────────────────────────────

    public function test_get_returns_string_value(): void
    {
        $key = new ConfigurationKey('app.name', ConfigurationKey::TYPE_STRING);
        $this->repository->addSource($this->source(1, [new ConfigurationDefinition($key, 'BaseApp')]));

        self::assertSame('BaseApp', $this->repository->get($key));
    }

    public function test_get_returns_int_value(): void
    {
        $key = new ConfigurationKey('app.timeout', ConfigurationKey::TYPE_INT);
        $this->repository->addSource($this->source(1, [new ConfigurationDefinition($key, 30)]));

        self::assertSame(30, $this->repository->get($key));
    }

    public function test_get_returns_bool_value(): void
    {
        $key = new ConfigurationKey('app.debug', ConfigurationKey::TYPE_BOOL);
        $this->repository->addSource($this->source(1, [new ConfigurationDefinition($key, false)]));

        self::assertFalse($this->repository->get($key));
    }

    public function test_get_returns_float_value(): void
    {
        $key = new ConfigurationKey('app.ratio', ConfigurationKey::TYPE_FLOAT);
        $this->repository->addSource($this->source(1, [new ConfigurationDefinition($key, 1.5)]));

        self::assertSame(1.5, $this->repository->get($key));
    }

    public function test_get_returns_array_value(): void
    {
        $key = new ConfigurationKey('app.drivers', ConfigurationKey::TYPE_ARRAY);
        $this->repository->addSource($this->source(1, [new ConfigurationDefinition($key, ['a', 'b'])]));

        self::assertSame(['a', 'b'], $this->repository->get($key));
    }

    public function test_float_key_accepts_int_value(): void
    {
        // int is a valid float per the type contract
        $key = new ConfigurationKey('app.ratio', ConfigurationKey::TYPE_FLOAT);
        $this->repository->addSource($this->source(1, [new ConfigurationDefinition($key, 2)]));

        self::assertSame(2, $this->repository->get($key));
    }

    // ── Positive: default handling ───────────────────────────────────────────

    public function test_optional_key_with_definition_default_returns_default_when_absent(): void
    {
        $key = new ConfigurationKey('pkg.ttl', ConfigurationKey::TYPE_INT, required: false, default: 300);
        // no source registered for this key

        self::assertSame(300, $this->repository->get($key));
    }

    public function test_optional_key_without_default_returns_null_when_absent(): void
    {
        $key = new ConfigurationKey('pkg.label', ConfigurationKey::TYPE_STRING, required: false);

        self::assertNull($this->repository->get($key));
    }

    public function test_get_or_default_returns_fallback_when_key_absent(): void
    {
        $key = new ConfigurationKey('pkg.missing', ConfigurationKey::TYPE_STRING);

        self::assertSame('fallback', $this->repository->getOrDefault($key, 'fallback'));
    }

    public function test_get_or_default_returns_value_when_key_present(): void
    {
        $key = new ConfigurationKey('pkg.name', ConfigurationKey::TYPE_STRING);
        $this->repository->addSource($this->source(1, [new ConfigurationDefinition($key, 'real')]));

        self::assertSame('real', $this->repository->getOrDefault($key, 'fallback'));
    }

    // ── Positive: has() ──────────────────────────────────────────────────────

    public function test_has_returns_true_for_present_key(): void
    {
        $key = new ConfigurationKey('app.name', ConfigurationKey::TYPE_STRING);
        $this->repository->addSource($this->source(1, [new ConfigurationDefinition($key, 'test')]));

        self::assertTrue($this->repository->has($key));
    }

    public function test_has_returns_false_for_absent_key(): void
    {
        $key = new ConfigurationKey('app.missing', ConfigurationKey::TYPE_STRING);

        self::assertFalse($this->repository->has($key));
    }

    // ── Positive: deterministic precedence ───────────────────────────────────

    public function test_higher_priority_source_overrides_lower(): void
    {
        $key = new ConfigurationKey('app.name', ConfigurationKey::TYPE_STRING);

        $this->repository->addSource($this->source(1, [new ConfigurationDefinition($key, 'default')]));
        $this->repository->addSource($this->source(10, [new ConfigurationDefinition($key, 'project')]));

        self::assertSame('project', $this->repository->get($key));
    }

    public function test_multiple_sources_at_different_priorities_compose_correctly(): void
    {
        $keyA = new ConfigurationKey('app.name', ConfigurationKey::TYPE_STRING);
        $keyB = new ConfigurationKey('app.timeout', ConfigurationKey::TYPE_INT);
        $keyC = new ConfigurationKey('app.debug', ConfigurationKey::TYPE_BOOL);

        // package defaults (priority 1)
        $this->repository->addSource($this->source(1, [
            new ConfigurationDefinition($keyA, 'DefaultApp'),
            new ConfigurationDefinition($keyB, 10),
            new ConfigurationDefinition($keyC, false),
        ]));

        // project overrides priority 10 — only overrides name and timeout
        $this->repository->addSource($this->source(10, [
            new ConfigurationDefinition($keyA, 'ProjectApp'),
            new ConfigurationDefinition($keyB, 60),
        ]));

        // extension override priority 50 — only overrides debug
        $this->repository->addSource($this->source(50, [
            new ConfigurationDefinition($keyC, true),
        ]));

        self::assertSame('ProjectApp', $this->repository->get($keyA));
        self::assertSame(60, $this->repository->get($keyB));
        self::assertTrue($this->repository->get($keyC));
    }

    public function test_lower_priority_source_does_not_override_higher(): void
    {
        $key = new ConfigurationKey('app.name', ConfigurationKey::TYPE_STRING);

        // Register high priority first, then low — order of addSource must not matter
        $this->repository->addSource($this->source(50, [new ConfigurationDefinition($key, 'extension')]));
        $this->repository->addSource($this->source(1, [new ConfigurationDefinition($key, 'default')]));

        self::assertSame('extension', $this->repository->get($key));
    }

    // ── Positive: valid override ─────────────────────────────────────────────

    public function test_explicit_extension_override_at_priority_50_wins_over_project(): void
    {
        $key = new ConfigurationKey('pkg.feature', ConfigurationKey::TYPE_STRING);

        $this->repository->addSource($this->source(1, [new ConfigurationDefinition($key, 'pkg-default')]));
        $this->repository->addSource($this->source(10, [new ConfigurationDefinition($key, 'project-value')]));
        $this->repository->addSource($this->source(50, [new ConfigurationDefinition($key, 'ext-override')]));

        self::assertSame('ext-override', $this->repository->get($key));
    }

    // ── Negative: missing required ───────────────────────────────────────────

    public function test_get_throws_for_missing_required_key(): void
    {
        $key = new ConfigurationKey('pkg.required', ConfigurationKey::TYPE_STRING);

        $this->expectException(ConfigurationKeyMissing::class);
        $this->expectExceptionMessage('pkg.required');

        $this->repository->get($key);
    }

    public function test_exception_exposes_the_missing_key(): void
    {
        $key = new ConfigurationKey('pkg.required', ConfigurationKey::TYPE_INT);

        try {
            $this->repository->get($key);
            self::fail('Expected ConfigurationKeyMissing');
        } catch (ConfigurationKeyMissing $e) {
            self::assertSame($key, $e->key);
            self::assertStringContainsString('pkg.required', $e->getMessage());
        }
    }

    // ── Negative: type mismatch ───────────────────────────────────────────────

    public function test_get_throws_type_mismatch_when_value_is_wrong_type(): void
    {
        $key = new ConfigurationKey('pkg.count', ConfigurationKey::TYPE_INT);
        $this->repository->addSource($this->source(1, [new ConfigurationDefinition($key, 'not-an-int')]));

        $this->expectException(ConfigurationTypeMismatch::class);
        $this->expectExceptionMessage('pkg.count');

        $this->repository->get($key);
    }

    public function test_type_mismatch_exception_exposes_key_and_actual_type(): void
    {
        $key = new ConfigurationKey('pkg.flag', ConfigurationKey::TYPE_BOOL);
        $this->repository->addSource($this->source(1, [new ConfigurationDefinition($key, 'yes')]));

        try {
            $this->repository->get($key);
            self::fail('Expected ConfigurationTypeMismatch');
        } catch (ConfigurationTypeMismatch $e) {
            self::assertSame($key, $e->key);
            self::assertSame('string', $e->actualType);
        }
    }

    public function test_get_or_default_throws_type_mismatch_when_value_present_but_wrong_type(): void
    {
        $key = new ConfigurationKey('pkg.count', ConfigurationKey::TYPE_INT);
        $this->repository->addSource($this->source(1, [new ConfigurationDefinition($key, 'bad')]));

        $this->expectException(ConfigurationTypeMismatch::class);

        $this->repository->getOrDefault($key, 0);
    }

    // ── Negative: duplicate priority ─────────────────────────────────────────

    public function test_same_priority_last_registered_wins(): void
    {
        $key = new ConfigurationKey('app.name', ConfigurationKey::TYPE_STRING);

        $this->repository->addSource($this->source(10, [new ConfigurationDefinition($key, 'first')]));
        $this->repository->addSource($this->source(10, [new ConfigurationDefinition($key, 'second')]));

        // Higher-priority sort is stable: both priority-10 sources are equal,
        // ascending sort means the second (later) overwrite wins in the flat map.
        self::assertSame('second', $this->repository->get($key));
    }

    // ── Helpers ──────────────────────────────────────────────────────────────

    /** @param list<ConfigurationDefinition> $definitions */
    private function source(int $priority, array $definitions): ConfigurationSource
    {
        return new class($priority, $definitions) implements ConfigurationSource
        {
            /** @param list<ConfigurationDefinition> $defs */
            public function __construct(
                private readonly int $p,
                private readonly array $defs,
            ) {}

            public function priority(): int
            {
                return $this->p;
            }

            public function definitions(): array
            {
                return $this->defs;
            }
        };
    }
}
