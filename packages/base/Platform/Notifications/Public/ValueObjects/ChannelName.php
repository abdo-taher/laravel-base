<?php

declare(strict_types=1);

namespace Base\Platform\Notifications\Public\ValueObjects;

use InvalidArgumentException;

final readonly class ChannelName
{
    public string $value;

    public function __construct(string $value)
    {
        $trimmed = trim($value);

        if ($trimmed === '') {
            throw new InvalidArgumentException('Channel name cannot be empty or whitespace.');
        }

        $this->value = $trimmed;
    }
}
