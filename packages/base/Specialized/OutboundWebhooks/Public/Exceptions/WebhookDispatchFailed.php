<?php

declare(strict_types=1);

namespace Base\Specialized\OutboundWebhooks\Public\Exceptions;

use Throwable;

final class WebhookDispatchFailed extends WebhookException
{
    public static function fromStatus(int $status, string $host): self
    {
        return new self(sprintf('Webhook dispatch to %s failed with HTTP status %d.', $host, $status));
    }

    public static function fromTransportError(Throwable $previous, string $host): self
    {
        return new self(sprintf('Webhook transport to %s failed due to an underlying network or execution error.', $host), 0, $previous);
    }
}
