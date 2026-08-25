<?php

declare(strict_types=1);

namespace Base\Specialized\OutboundWebhooks\Application\Contracts;

use Base\Specialized\OutboundWebhooks\Public\ValueObjects\WebhookMessage;

interface WebhookTransport
{
    /**
     * Sends the webhook request via underlying infrastructure.
     *
     * @throws \Throwable
     */
    public function send(WebhookMessage $message): void;
}
