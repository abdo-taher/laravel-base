<?php

declare(strict_types=1);

namespace Base\Platform\Notifications\Application;

use Base\Platform\Notifications\Public\Contracts\ChannelRegistry;
use Base\Platform\Notifications\Public\Contracts\NotificationDispatcher;
use Base\Platform\Notifications\Public\Exceptions\NotificationDispatchFailed;
use Base\Platform\Notifications\Public\Exceptions\NotificationException;
use Base\Platform\Notifications\Public\ValueObjects\ChannelAddress;
use Base\Platform\Notifications\Public\ValueObjects\NotificationMessage;

final readonly class DefaultNotificationDispatcher implements NotificationDispatcher
{
    public function __construct(
        private ChannelRegistry $registry
    ) {}

    public function dispatch(NotificationMessage $message, ChannelAddress $target): void
    {
        $channel = $this->registry->get($target->channel);

        try {
            $channel->send($message, $target->address);
        } catch (NotificationException $e) {
            throw $e;
        } catch (\Throwable $e) {
            throw NotificationDispatchFailed::backendRejected($target->channel, $e);
        }
    }
}
