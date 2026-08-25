<?php

declare(strict_types=1);

namespace Base\Platform\Notifications\Public\Exceptions;

use Base\Platform\Notifications\Public\ValueObjects\ChannelName;

final class UnknownNotificationChannel extends NotificationException
{
    public static function forName(ChannelName $name): self
    {
        return new self("Unknown notification channel: {$name->value}");
    }
}
