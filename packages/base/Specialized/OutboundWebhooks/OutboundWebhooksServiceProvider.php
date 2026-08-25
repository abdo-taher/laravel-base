<?php

declare(strict_types=1);

namespace Base\Specialized\OutboundWebhooks;

use Base\Specialized\OutboundWebhooks\Application\Contracts\WebhookTransport;
use Base\Specialized\OutboundWebhooks\Application\DefaultWebhookDispatcher;
use Base\Specialized\OutboundWebhooks\Infrastructure\Adapters\LaravelHttpWebhookTransport;
use Base\Specialized\OutboundWebhooks\Public\Contracts\WebhookDispatcher;
use Illuminate\Support\ServiceProvider;

final class OutboundWebhooksServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(WebhookTransport::class, function () {
            return new LaravelHttpWebhookTransport(10, 5); // MVP defaults, can be overridden by configuration
        });

        $this->app->bind(WebhookDispatcher::class, DefaultWebhookDispatcher::class);
    }
}
