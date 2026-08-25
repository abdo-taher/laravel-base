<?php

declare(strict_types=1);

namespace Base\Platform\Notifications\Public\Exceptions;

use Base\Platform\Notifications\Public\ValueObjects\ChannelName;
use Throwable;

final class NotificationDispatchFailed extends NotificationException
{
    public static function backendRejected(ChannelName $channel, Throwable $previous): self
    {
        // Provider secrets or raw addresses must not leak here.
        return new self(
            "Notification dispatch failed via channel '{$channel->value}'.",
            0,
            $previous
        );
    }
}
