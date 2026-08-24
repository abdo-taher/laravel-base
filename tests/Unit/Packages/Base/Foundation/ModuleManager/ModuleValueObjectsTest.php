<?php

declare(strict_types=1);

namespace Tests\Unit\Packages\Base\Foundation\ModuleManager;

use Base\Foundation\ModuleManager\Public\ValueObjects\ModuleIdentifier;
use Base\Foundation\ModuleManager\Public\ValueObjects\ModuleState;
use PHPUnit\Framework\TestCase;

final class ModuleValueObjectsTest extends TestCase
{
    // ── ModuleIdentifier ─────────────────────────────────────────────────────

    public function test_module_identifier_exposes_name_and_category(): void
    {
        $id = new ModuleIdentifier('Manifest', 'Foundation');

        self::assertSame('Manifest', $id->name);
        self::assertSame('Foundation', $id->category);
    }

    public function test_module_identifier_equality_is_by_name(): void
    {
        $a = new ModuleIdentifier('Manifest', 'Foundation');
        $b = new ModuleIdentifier('Manifest', 'Foundation');
        $c = new ModuleIdentifier('Other', 'Foundation');

        self::assertTrue($a->equals($b));
        self::assertFalse($a->equals($c));
    }

    public function test_module_identifier_to_string_combines_category_and_name(): void
    {
        $id = new ModuleIdentifier('Manifest', 'Foundation');

        self::assertSame('Foundation/Manifest', $id->toString());
    }

    public function test_module_identifier_is_readonly(): void
    {
        $id = new ModuleIdentifier('Manifest', 'Foundation');
        $reflection = new \ReflectionClass($id);

        self::assertTrue($reflection->isReadOnly());
    }

    // ── ModuleState ──────────────────────────────────────────────────────────

    public function test_module_state_exposes_identifier_and_state(): void
    {
        $id = new ModuleIdentifier('Manifest', 'Foundation');
        $state = new ModuleState($id, ModuleState::READY);

        self::assertSame($id, $state->identifier);
        self::assertSame(ModuleState::READY, $state->state);
    }

    public function test_module_state_is_ready_returns_true_for_ready_state(): void
    {
        $id = new ModuleIdentifier('Manifest', 'Foundation');
        $ready = new ModuleState($id, ModuleState::READY);
        $discovered = new ModuleState($id, ModuleState::DISCOVERED);

        self::assertTrue($ready->isReady());
        self::assertFalse($discovered->isReady());
    }

    public function test_module_state_constants_have_expected_values(): void
    {
        self::assertSame('discovered', ModuleState::DISCOVERED);
        self::assertSame('ready', ModuleState::READY);
    }

    public function test_module_state_is_readonly(): void
    {
        $id = new ModuleIdentifier('Manifest', 'Foundation');
        $state = new ModuleState($id, ModuleState::READY);
        $reflection = new \ReflectionClass($state);

        self::assertTrue($reflection->isReadOnly());
    }
}
