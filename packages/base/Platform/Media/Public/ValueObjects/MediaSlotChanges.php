<?php

declare(strict_types=1);

namespace Base\Platform\Media\Public\ValueObjects;

final class MediaSlotChanges
{
    /** @param array<string, MediaSlotChange> $changes */
    public function __construct(public readonly array $changes) {}

    public function getChangeForSlot(MediaSlotName $slot): MediaSlotChange
    {
        return $this->changes[$slot->value] ?? MediaSlotChange::untouched();
    }
}
