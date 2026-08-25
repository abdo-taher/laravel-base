<?php

declare(strict_types=1);

namespace Base\Platform\Notifications;

use Base\Platform\Notifications\Application\DefaultNotificationDispatcher;
use Base\Platform\Notifications\Application\InMemoryChannelRegistry;
use Base\Platform\Notifications\Public\Contracts\ChannelRegistry;
use Base\Platform\Notifications\Public\Contracts\NotificationDispatcher;
use Illuminate\Support\ServiceProvider;

final class NotificationsServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(ChannelRegistry::class, InMemoryChannelRegistry::class);
        $this->app->bind(NotificationDispatcher::class, DefaultNotificationDispatcher::class);
    }
}
