<?php

declare(strict_types=1);

namespace Tests\Feature\Packages\Base\Platform\Settings;

use Base\Platform\Settings\Public\Contracts\SettingsRegistry;
use Base\Platform\Settings\Public\Contracts\SettingsRepository;
use Base\Platform\Settings\Public\ValueObjects\SettingDefinition;
use Base\Platform\Settings\Public\ValueObjects\SettingKey;
use Base\Platform\Settings\Public\ValueObjects\SettingType;
use Base\Platform\Settings\SettingsServiceProvider;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class DatabaseIntegrationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->app->register(SettingsServiceProvider::class);
        $this->artisan('migrate');
    }

    public function test_primitive_round_tripping(): void
    {
        /** @var SettingsRegistry $registry */
        $registry = $this->app->make(SettingsRegistry::class);

        /** @var SettingsRepository $repository */
        $repository = $this->app->make(SettingsRepository::class);

        // String
        $registry->register(new SettingDefinition(new SettingKey('test.string'), SettingType::STRING));
        $repository->set('test.string', '42');
        $stringVal = $repository->get('test.string');
        $this->assertSame('42', $stringVal, 'String primitive round trip failed');

        // Integer
        $registry->register(new SettingDefinition(new SettingKey('test.int'), SettingType::INTEGER));
        $repository->set('test.int', 42);
        $intVal = $repository->get('test.int');
        $this->assertSame(42, $intVal, 'Integer primitive round trip failed');

        // Float
        $registry->register(new SettingDefinition(new SettingKey('test.float'), SettingType::FLOAT));
        $repository->set('test.float', 1.5);
        $floatVal = $repository->get('test.float');
        $this->assertSame(1.5, $floatVal, 'Float primitive round trip failed');

        // Boolean
        $registry->register(new SettingDefinition(new SettingKey('test.bool'), SettingType::BOOLEAN));
        $repository->set('test.bool', false);
        $boolVal = $repository->get('test.bool');
        $this->assertSame(false, $boolVal, 'Boolean primitive round trip failed');
    }

    public function test_write_overwrite_reset_flow(): void
    {
        /** @var SettingsRegistry $registry */
        $registry = $this->app->make(SettingsRegistry::class);

        /** @var SettingsRepository $repository */
        $repository = $this->app->make(SettingsRepository::class);

        $registry->register(new SettingDefinition(
            new SettingKey('flow.setting'),
            SettingType::STRING,
            default: 'default-value'
        ));

        // Read default
        $this->assertSame('default-value', $repository->get('flow.setting'));

        // Write
        $repository->set('flow.setting', 'first-write');
        $this->assertSame('first-write', $repository->get('flow.setting'));

        // Overwrite
        $repository->set('flow.setting', 'second-write');
        $this->assertSame('second-write', $repository->get('flow.setting'));

        // Reset
        $repository->reset('flow.setting');
        $this->assertSame('default-value', $repository->get('flow.setting'));
    }
}
