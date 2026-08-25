<?php

declare(strict_types=1);

namespace Base\Platform\Media\Public\ValueObjects;

final class MediaOwnerReference
{
    public function __construct(
        public readonly string $type,
        public readonly string $id
    ) {
        if (trim($type) === '' || trim($id) === '') {
            throw new \InvalidArgumentException('Owner type and ID must not be empty.');
        }
    }

    public function equals(self $other): bool
    {
        return $this->type === $other->type && $this->id === $other->id;
    }
}
