<?php

declare(strict_types=1);

namespace Base\Platform\Notifications\Public\ValueObjects;

use InvalidArgumentException;

final readonly class NotificationMessage
{
    public function __construct(
        public string $body,
        public ?string $subject = null
    ) {
        if (trim($body) === '') {
            throw new InvalidArgumentException('Notification body cannot be empty.');
        }

        if ($subject !== null && trim($subject) === '') {
            throw new InvalidArgumentException('Notification subject cannot be empty whitespace.');
        }
    }
}
