<?php

declare(strict_types=1);

namespace Base\Platform\Notifications\Public\Contracts;

use Base\Platform\Notifications\Public\Exceptions\InvalidChannelAddress;
use Base\Platform\Notifications\Public\Exceptions\NotificationDispatchFailed;
use Base\Platform\Notifications\Public\Exceptions\UnknownNotificationChannel;
use Base\Platform\Notifications\Public\ValueObjects\ChannelAddress;
use Base\Platform\Notifications\Public\ValueObjects\NotificationMessage;

interface NotificationDispatcher
{
    /**
     * Dispatch a notification synchronously to a single target.
     * Returns void on successful provider acceptance.
     * Does NOT guarantee physical delivery.
     *
     * @throws UnknownNotificationChannel
     * @throws InvalidChannelAddress
     * @throws NotificationDispatchFailed
     */
    public function dispatch(NotificationMessage $message, ChannelAddress $target): void;
}
