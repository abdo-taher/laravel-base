<?php

declare(strict_types=1);

namespace Base\Platform\Notifications\Public\ValueObjects;

use Base\Platform\Notifications\Public\Exceptions\InvalidChannelAddress;

final readonly class ChannelAddress
{
    public string $address;

    public function __construct(
        public ChannelName $channel,
        string $address
    ) {
        $trimmed = trim($address);

        if ($trimmed === '') {
            throw InvalidChannelAddress::genericValidationFailed($channel, 'Address cannot be empty or whitespace.');
        }

        if (preg_match('/[\x00-\x1F\x7F]/', $address)) {
            throw InvalidChannelAddress::genericValidationFailed($channel, 'Address cannot contain control characters.');
        }

        $this->address = $trimmed;
    }
}
