<?php

declare(strict_types=1);

namespace Base\Platform\Notifications\Application;

use Base\Platform\Notifications\Public\Contracts\ChannelRegistry;
use Base\Platform\Notifications\Public\Contracts\NotificationChannel;
use Base\Platform\Notifications\Public\Exceptions\UnknownNotificationChannel;
use Base\Platform\Notifications\Public\ValueObjects\ChannelName;
use InvalidArgumentException;

final class InMemoryChannelRegistry implements ChannelRegistry
{
    /** @var array<string, NotificationChannel> */
    private array $channels = [];

    public function register(NotificationChannel $channel): void
    {
        $name = $channel->name()->value;

        if (array_key_exists($name, $this->channels)) {
            throw new InvalidArgumentException("Channel '{$name}' is already registered.");
        }

        $this->channels[$name] = $channel;
    }

    public function get(ChannelName $name): NotificationChannel
    {
        if (! array_key_exists($name->value, $this->channels)) {
            throw UnknownNotificationChannel::forName($name);
        }

        return $this->channels[$name->value];
    }
}
