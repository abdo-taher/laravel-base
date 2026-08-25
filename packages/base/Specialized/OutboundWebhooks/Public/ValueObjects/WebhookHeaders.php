<?php

declare(strict_types=1);

namespace Base\Specialized\OutboundWebhooks\Public\ValueObjects;

use Base\Specialized\OutboundWebhooks\Public\Exceptions\WebhookException;

final readonly class WebhookHeaders
{
    /** @var array<string, string> */
    public array $headers;

    /**
     * @param  array<mixed, mixed>  $headers
     */
    public function __construct(array $headers)
    {
        $normalized = [];
        $forbidden = [
            'host', 'content-length', 'transfer-encoding',
            'connection', 'content-type', 'accept-encoding',
        ];

        foreach ($headers as $name => $value) {
            if (! is_string($name) || $name === '') {
                throw new WebhookException('Header names must be non-empty strings.');
            }
            if (! is_string($value)) {
                throw new WebhookException('Header values must be strings.');
            }

            // Valid token characters (RFC 7230)
            if (preg_match('/^[a-zA-Z0-9\-\.!#$%&\'*+.^_`|~]+$/', $name) !== 1) {
                throw new WebhookException(sprintf('Invalid header name: %s', $name));
            }

            // Reject CR, LF, null in values
            if (preg_match('/[\r\n\x00]/', $value) === 1) {
                throw new WebhookException('Header values cannot contain CR, LF, or null bytes.');
            }

            $lowerName = strtolower($name);

            if (in_array($lowerName, $forbidden, true)) {
                throw new WebhookException(sprintf('Forbidden transport-control header: %s', $name));
            }

            if (array_key_exists($lowerName, $normalized)) {
                throw new WebhookException(sprintf('Duplicate header detected: %s', $name));
            }

            $normalized[$lowerName] = $value;
        }

        $this->headers = $normalized;
    }
}
