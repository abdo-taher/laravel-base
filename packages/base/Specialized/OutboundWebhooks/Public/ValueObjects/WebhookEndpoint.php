<?php

declare(strict_types=1);

namespace Base\Specialized\OutboundWebhooks\Public\ValueObjects;

use Base\Specialized\OutboundWebhooks\Public\Exceptions\InvalidWebhookEndpoint;

final readonly class WebhookEndpoint
{
    public string $url;

    public string $host;

    public function __construct(string $url)
    {
        if (filter_var($url, FILTER_VALIDATE_URL) === false) {
            throw InvalidWebhookEndpoint::invalidUrl();
        }

        $parsed = parse_url($url);

        if ($parsed === false || ! isset($parsed['scheme']) || ! isset($parsed['host'])) {
            throw InvalidWebhookEndpoint::invalidUrl();
        }

        $scheme = strtolower($parsed['scheme']);
        if ($scheme !== 'http' && $scheme !== 'https') {
            throw InvalidWebhookEndpoint::unsupportedScheme($scheme);
        }

        if (isset($parsed['user']) || isset($parsed['pass'])) {
            throw InvalidWebhookEndpoint::credentialsNotAllowed();
        }

        if (isset($parsed['fragment'])) {
            throw InvalidWebhookEndpoint::fragmentsNotAllowed();
        }

        $this->url = $url;
        $this->host = $parsed['host'];
    }
}
