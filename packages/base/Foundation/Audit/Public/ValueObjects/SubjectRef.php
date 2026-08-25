<?php

declare(strict_types=1);

namespace Base\Foundation\Audit\Public\ValueObjects;

use InvalidArgumentException;

/**
 * A generic reference to the subject or resource of an audit event.
 *
 * Avoids recording full domain models which might mutate or leak secrets.
 * Instead, only the type and ID are recorded.
 *
 * No framework dependencies.
 */
final readonly class SubjectRef
{
    public function __construct(
        public string $type,
        public string $id,
    ) {
        if (trim($type) === '') {
            throw new InvalidArgumentException('Subject type must be non-empty.');
        }

        if (trim($id) === '') {
            throw new InvalidArgumentException('Subject ID must be non-empty.');
        }
    }

    public function equals(self $other): bool
    {
        return $this->type === $other->type && $this->id === $other->id;
    }
}
