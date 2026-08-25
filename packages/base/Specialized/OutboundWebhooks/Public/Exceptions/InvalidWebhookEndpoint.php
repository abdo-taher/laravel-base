<?php

declare(strict_types=1);

namespace Base\Specialized\OutboundWebhooks\Public\Exceptions;

final class InvalidWebhookEndpoint extends WebhookException
{
    public static function invalidUrl(): self
    {
        return new self('Webhook endpoint must be a valid URL.');
    }

    public static function unsupportedScheme(string $scheme): self
    {
        return new self(sprintf('Unsupported webhook endpoint scheme: %s. Only http and https are allowed.', $scheme));
    }

    public static function credentialsNotAllowed(): self
    {
        return new self('Webhook endpoint cannot contain embedded credentials (user-info).');
    }

    public static function fragmentsNotAllowed(): self
    {
        return new self('Webhook endpoint cannot contain fragments.');
    }
}
