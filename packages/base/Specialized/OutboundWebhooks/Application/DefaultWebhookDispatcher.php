<?php

declare(strict_types=1);

namespace Base\Specialized\OutboundWebhooks\Application;

use Base\Specialized\OutboundWebhooks\Application\Contracts\WebhookTransport;
use Base\Specialized\OutboundWebhooks\Public\Contracts\WebhookDispatcher;
use Base\Specialized\OutboundWebhooks\Public\Exceptions\WebhookDispatchFailed;
use Base\Specialized\OutboundWebhooks\Public\ValueObjects\WebhookMessage;
use Throwable;

final class DefaultWebhookDispatcher implements WebhookDispatcher
{
    public function __construct(
        private readonly WebhookTransport $transport
    ) {}

    public function dispatch(WebhookMessage $message): void
    {
        try {
            $this->transport->send($message);
        } catch (WebhookDispatchFailed $e) {
            throw $e;
        } catch (Throwable $e) {
            throw WebhookDispatchFailed::fromTransportError($e, $message->endpoint->host);
        }
    }
}
