<?php

declare(strict_types=1);

namespace Base\Platform\Media\Public\ValueObjects;

final class MediaSlotDefinition
{
    /** @param list<string> $allowedMimeTypes */
    private function __construct(
        public readonly MediaSlotName $name,
        public readonly bool $isMultiple,
        public readonly array $allowedMimeTypes,
        public readonly ?int $maxSizeBytes,
        public readonly ?int $maxItems
    ) {}

    /** @param list<string> $allowedMimeTypes */
    public static function single(
        string $name,
        array $allowedMimeTypes = [],
        ?int $maxSizeBytes = null
    ): self {
        return new self(
            MediaSlotName::fromString($name),
            false,
            $allowedMimeTypes,
            $maxSizeBytes,
            null
        );
    }

    /** @param list<string> $allowedMimeTypes */
    public static function multiple(
        string $name,
        array $allowedMimeTypes = [],
        ?int $maxSizeBytes = null,
        ?int $maxItems = null
    ): self {
        return new self(
            MediaSlotName::fromString($name),
            true,
            $allowedMimeTypes,
            $maxSizeBytes,
            $maxItems
        );
    }
}
