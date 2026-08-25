<?php

declare(strict_types=1);

namespace Base\Specialized\OutboundWebhooks\Infrastructure\Adapters;

use Base\Specialized\OutboundWebhooks\Application\Contracts\WebhookTransport;
use Base\Specialized\OutboundWebhooks\Public\Exceptions\WebhookDispatchFailed;
use Base\Specialized\OutboundWebhooks\Public\ValueObjects\WebhookMessage;
use Illuminate\Support\Facades\Http;

final class LaravelHttpWebhookTransport implements WebhookTransport
{
    public function __construct(
        private readonly int $timeoutSeconds = 10,
        private readonly int $connectTimeoutSeconds = 5
    ) {}

    public function send(WebhookMessage $message): void
    {
        $headers = $message->headers ? $message->headers->headers : [];

        $response = Http::timeout($this->timeoutSeconds)
            ->connectTimeout($this->connectTimeoutSeconds)
            ->withoutRedirecting()
            ->withHeaders($headers)
            ->asJson()
            ->post($message->endpoint->url, $message->payload->data);

        $status = $response->status();

        if ($status < 200 || $status >= 300) {
            throw WebhookDispatchFailed::fromStatus($status, $message->endpoint->host);
        }
    }
}
