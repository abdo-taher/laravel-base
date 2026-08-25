<?php

declare(strict_types=1);

namespace Base\Platform\Media\Public\ValueObjects;

final class MediaSlotChange
{
    private const TYPE_UNTOUCHED = 'untouched';

    private const TYPE_CLEAR = 'clear';

    private const TYPE_SET = 'set';

    /** @param list<MediaReference>|null $references */
    private function __construct(
        public readonly string $type,
        public readonly ?array $references
    ) {}

    public static function untouched(): self
    {
        return new self(self::TYPE_UNTOUCHED, null);
    }

    public static function clear(): self
    {
        return new self(self::TYPE_CLEAR, null);
    }

    /** @param list<MediaReference>|MediaReference $references */
    public static function set(array|MediaReference $references): self
    {
        return new self(self::TYPE_SET, is_array($references) ? $references : [$references]);
    }

    public function isUntouched(): bool
    {
        return $this->type === self::TYPE_UNTOUCHED;
    }

    public function isClear(): bool
    {
        return $this->type === self::TYPE_CLEAR;
    }

    public function isSet(): bool
    {
        return $this->type === self::TYPE_SET;
    }
}
