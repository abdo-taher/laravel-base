<?php

declare(strict_types=1);

namespace Base\Platform\Notifications\Public\Contracts;

use Base\Platform\Notifications\Public\Exceptions\UnknownNotificationChannel;
use Base\Platform\Notifications\Public\ValueObjects\ChannelName;
use InvalidArgumentException;

interface ChannelRegistry
{
    /**
     * @throws InvalidArgumentException If a channel is already registered.
     */
    public function register(NotificationChannel $channel): void;

    /**
     * @throws UnknownNotificationChannel
     */
    public function get(ChannelName $name): NotificationChannel;
}
