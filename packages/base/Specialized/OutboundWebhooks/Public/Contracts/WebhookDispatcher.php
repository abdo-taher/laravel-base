<?php

declare(strict_types=1);

namespace Base\Specialized\OutboundWebhooks\Public\Contracts;

use Base\Specialized\OutboundWebhooks\Public\Exceptions\WebhookDispatchFailed;
use Base\Specialized\OutboundWebhooks\Public\ValueObjects\WebhookMessage;

interface WebhookDispatcher
{
    /**
     * Attempts synchronous delivery of the webhook message.
     *
     * @throws WebhookDispatchFailed
     */
    public function dispatch(WebhookMessage $message): void;
}
