<?php

declare(strict_types=1);

namespace Base\Platform\Settings\Infrastructure\Database;

use Base\Platform\Settings\Public\Contracts\SettingsRegistry;
use Base\Platform\Settings\Public\Contracts\SettingsRepository;
use Base\Platform\Settings\Public\Exceptions\SettingNotDefined;
use Base\Platform\Settings\Public\Exceptions\SettingPersistenceFailed;
use Base\Platform\Settings\Public\Exceptions\SettingTypeMismatch;
use Base\Platform\Settings\Public\Exceptions\SettingValueMissing;
use Base\Platform\Settings\Public\ValueObjects\SettingKey;
use Base\Platform\Settings\Public\ValueObjects\SettingType;
use Carbon\Carbon;
use Illuminate\Database\ConnectionInterface;
use Throwable;

final readonly class DatabaseSettingsRepository implements SettingsRepository
{
    public function __construct(
        private SettingsRegistry $registry,
        private ConnectionInterface $connection,
        private string $table = 'settings'
    ) {}

    public function get(SettingKey|string $key): mixed
    {
        $keyString = $key instanceof SettingKey ? $key->value : $key;
        $definition = $this->registry->getDefinition($keyString);

        if ($definition === null) {
            throw SettingNotDefined::forKey($keyString);
        }

        try {
            $record = $this->connection->table($this->table)->where('key', $keyString)->first();
        } catch (Throwable $e) {
            throw SettingPersistenceFailed::readFailed($keyString, $e);
        }

        if ($record !== null) {
            return $this->decodeValue($record->payload, $definition->type);
        }

        if ($definition->required && $definition->default === null) {
            throw SettingValueMissing::forRequired($keyString);
        }

        return $definition->default;
    }

    public function set(SettingKey|string $key, mixed $value): void
    {
        $keyString = $key instanceof SettingKey ? $key->value : $key;
        $definition = $this->registry->getDefinition($keyString);

        if ($definition === null) {
            throw SettingNotDefined::forKey($keyString);
        }

        try {
            $definition->validateType($value);
        } catch (\InvalidArgumentException $e) {
            throw SettingTypeMismatch::forKey($keyString, $definition->type->value, gettype($value));
        }

        try {
            $this->connection->table($this->table)->updateOrInsert(
                ['key' => $keyString],
                [
                    'type' => $definition->type->value,
                    'payload' => json_encode($value, JSON_THROW_ON_ERROR),
                    'updated_at' => Carbon::now(),
                ]
            );
        } catch (Throwable $e) {
            throw SettingPersistenceFailed::writeFailed($keyString, $e);
        }
    }

    public function reset(SettingKey|string $key): void
    {
        $keyString = $key instanceof SettingKey ? $key->value : $key;

        try {
            $this->connection->table($this->table)->where('key', $keyString)->delete();
        } catch (Throwable $e) {
            throw SettingPersistenceFailed::writeFailed($keyString, $e);
        }
    }

    private function decodeValue(string $payload, SettingType $type): mixed
    {
        $value = json_decode($payload, true, 512, JSON_THROW_ON_ERROR);

        // Sanity check, though technically schema enforcement would prevent dirty types.
        return match ($type) {
            SettingType::STRING => (string) $value,
            SettingType::INTEGER => (int) $value,
            SettingType::FLOAT => (float) $value,
            SettingType::BOOLEAN => (bool) $value,
        };
    }
}
