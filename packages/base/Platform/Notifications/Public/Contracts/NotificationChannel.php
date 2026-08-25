<?php

declare(strict_types=1);

namespace Base\Platform\Notifications\Public\Contracts;

use Base\Platform\Notifications\Public\Exceptions\InvalidChannelAddress;
use Base\Platform\Notifications\Public\Exceptions\NotificationDispatchFailed;
use Base\Platform\Notifications\Public\ValueObjects\ChannelName;
use Base\Platform\Notifications\Public\ValueObjects\NotificationMessage;

interface NotificationChannel
{
    public function name(): ChannelName;

    /**
     * @throws InvalidChannelAddress
     * @throws NotificationDispatchFailed
     */
    public function send(NotificationMessage $message, string $address): void;
}
