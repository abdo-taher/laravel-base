<?php

declare(strict_types=1);

namespace Base\Platform\Notifications\Public\Exceptions;

use Base\Platform\Notifications\Public\ValueObjects\ChannelName;

final class InvalidChannelAddress extends NotificationException
{
    public static function genericValidationFailed(ChannelName $channel, string $reason): self
    {
        // Address is omitted for PII security.
        return new self("Invalid target address for channel '{$channel->value}': {$reason}");
    }

    public static function channelSpecificValidationFailed(ChannelName $channel, string $reason): self
    {
        // Address is omitted for PII security.
        return new self("Channel '{$channel->value}' rejected the target address: {$reason}");
    }
}
