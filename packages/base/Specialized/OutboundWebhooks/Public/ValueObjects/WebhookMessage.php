<?php

declare(strict_types=1);

namespace Base\Specialized\OutboundWebhooks\Public\ValueObjects;

final readonly class WebhookMessage
{
    public function __construct(
        public WebhookEndpoint $endpoint,
        public WebhookPayload $payload,
        public ?WebhookHeaders $headers = null,
    ) {}
}
