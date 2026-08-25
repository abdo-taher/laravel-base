<?php

declare(strict_types=1);

namespace Base\Platform\Settings\Public\ValueObjects;

final readonly class SettingDefinition
{
    public function __construct(
        public SettingKey $key,
        public SettingType $type,
        public bool $required = false,
        public mixed $default = null,
        public ?string $description = null
    ) {
        if ($this->default !== null) {
            $this->validateType($this->default);
        }
    }

    public function validateType(mixed $value): void
    {
        $valid = match ($this->type) {
            SettingType::STRING => is_string($value),
            SettingType::INTEGER => is_int($value),
            SettingType::FLOAT => is_float($value) || is_int($value),
            SettingType::BOOLEAN => is_bool($value),
        };

        if (! $valid) {
            $type = gettype($value);
            throw new \InvalidArgumentException(
                "Value for setting '{$this->key}' must be of type {$this->type->value}, got {$type}."
            );
        }
    }
}
