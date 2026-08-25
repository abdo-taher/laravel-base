<?php

declare(strict_types=1);

namespace Tests\Unit\Packages\Base\Platform\Settings;

use Base\Platform\Settings\Application\InMemorySettingsRegistry;
use Base\Platform\Settings\Infrastructure\Database\DatabaseSettingsRepository;
use Base\Platform\Settings\Public\Exceptions\SettingNotDefined;
use Base\Platform\Settings\Public\Exceptions\SettingTypeMismatch;
use Base\Platform\Settings\Public\Exceptions\SettingValueMissing;
use Base\Platform\Settings\Public\ValueObjects\SettingDefinition;
use Base\Platform\Settings\Public\ValueObjects\SettingKey;
use Base\Platform\Settings\Public\ValueObjects\SettingType;
use Illuminate\Database\ConnectionInterface;
use Illuminate\Database\Query\Builder;
use Mockery;
use Mockery\MockInterface;
use PHPUnit\Framework\TestCase;

final class DatabaseSettingsRepositoryTest extends TestCase
{
    private InMemorySettingsRegistry $registry;

    /** @var ConnectionInterface&MockInterface */
    private $connection;

    /** @var Builder&MockInterface */
    private $builder;

    private DatabaseSettingsRepository $repository;

    protected function setUp(): void
    {
        parent::setUp();

        $this->registry = new InMemorySettingsRegistry;

        /** @var ConnectionInterface&MockInterface $connection */
        $connection = Mockery::mock(ConnectionInterface::class);
        $this->connection = $connection;

        /** @var Builder&MockInterface $builder */
        $builder = Mockery::mock(Builder::class);
        $this->builder = $builder;

        $this->connection->shouldReceive('table')->with('settings')->andReturn($this->builder);

        $this->repository = new DatabaseSettingsRepository(
            $this->registry,
            $this->connection
        );
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function test_get_throws_if_not_defined(): void
    {
        $this->expectException(SettingNotDefined::class);
        $this->repository->get('unknown.key');
    }

    public function test_get_returns_persisted_value(): void
    {
        $this->registry->register(new SettingDefinition(
            key: new SettingKey('site.name'),
            type: SettingType::STRING
        ));

        $this->builder->shouldReceive('where')->with('key', 'site.name')->andReturnSelf();
        $this->builder->shouldReceive('first')->andReturn((object) [
            'payload' => json_encode('My Site', JSON_THROW_ON_ERROR),
        ]);

        $value = $this->repository->get('site.name');
        $this->assertSame('My Site', $value);
    }

    public function test_get_returns_default_if_not_persisted(): void
    {
        $this->registry->register(new SettingDefinition(
            key: new SettingKey('site.name'),
            type: SettingType::STRING,
            default: 'Default Site'
        ));

        $this->builder->shouldReceive('where')->with('key', 'site.name')->andReturnSelf();
        $this->builder->shouldReceive('first')->andReturnNull();

        $value = $this->repository->get('site.name');
        $this->assertSame('Default Site', $value);
    }

    public function test_get_throws_if_missing_required_with_no_default(): void
    {
        $this->registry->register(new SettingDefinition(
            key: new SettingKey('site.name'),
            type: SettingType::STRING,
            required: true
        ));

        $this->builder->shouldReceive('where')->with('key', 'site.name')->andReturnSelf();
        $this->builder->shouldReceive('first')->andReturnNull();

        $this->expectException(SettingValueMissing::class);
        $this->repository->get('site.name');
    }

    public function test_set_throws_if_not_defined(): void
    {
        $this->expectException(SettingNotDefined::class);
        $this->repository->set('unknown.key', 'value');
    }

    public function test_set_throws_on_type_mismatch(): void
    {
        $this->registry->register(new SettingDefinition(
            key: new SettingKey('site.port'),
            type: SettingType::INTEGER
        ));

        $this->expectException(SettingTypeMismatch::class);
        $this->repository->set('site.port', '8080'); // String instead of int
    }

    public function test_set_persists_valid_value(): void
    {
        $this->registry->register(new SettingDefinition(
            key: new SettingKey('site.port'),
            type: SettingType::INTEGER
        ));

        $this->builder->shouldReceive('updateOrInsert')
            ->once()
            ->with(
                ['key' => 'site.port'],
                Mockery::on(function (array $values) {
                    return $values['type'] === 'integer' &&
                           $values['payload'] === json_encode(8080, JSON_THROW_ON_ERROR) &&
                           isset($values['updated_at']);
                })
            );

        $this->repository->set('site.port', 8080);
        $this->expectNotToPerformAssertions();
    }

    public function test_reset_deletes_persisted_value(): void
    {
        $this->registry->register(new SettingDefinition(
            key: new SettingKey('site.name'),
            type: SettingType::STRING
        ));

        $this->builder->shouldReceive('where')->with('key', 'site.name')->andReturnSelf();
        $this->builder->shouldReceive('delete')->once();

        $this->repository->reset('site.name');
        $this->expectNotToPerformAssertions();
    }
}
