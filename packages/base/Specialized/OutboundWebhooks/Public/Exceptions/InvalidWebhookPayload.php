<?php

declare(strict_types=1);

namespace Base\Specialized\OutboundWebhooks\Public\Exceptions;

final class InvalidWebhookPayload extends WebhookException
{
    public static function invalidType(string $type): self
    {
        return new self(sprintf('Webhook payload contains invalid type: %s. Only JSON-safe primitives and arrays are allowed.', $type));
    }

    public static function invalidFloat(): self
    {
        return new self('Webhook payload contains an invalid float (NAN or INF).');
    }

    public static function encodingFailed(string $error): self
    {
        return new self(sprintf('Webhook payload failed to encode: %s', $error));
    }
}
