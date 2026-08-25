<?php

declare(strict_types=1);

namespace Base\Specialized\OutboundWebhooks\Public\ValueObjects;

use Base\Specialized\OutboundWebhooks\Public\Exceptions\InvalidWebhookPayload;
use JsonException;

final readonly class WebhookPayload
{
    /**
     * @var array<array-key, mixed>
     */
    public array $data;

    /**
     * @param  array<array-key, mixed>  $data
     */
    public function __construct(array $data)
    {
        $this->validate($data);
        $this->data = $data;
    }

    private function validate(mixed $value): void
    {
        if (is_null($value) || is_bool($value) || is_int($value) || is_string($value)) {
            return;
        }

        if (is_float($value)) {
            if (is_nan($value) || is_infinite($value)) {
                throw InvalidWebhookPayload::invalidFloat();
            }

            return;
        }

        if (is_array($value)) {
            foreach ($value as $item) {
                $this->validate($item);
            }

            return;
        }

        throw InvalidWebhookPayload::invalidType(get_debug_type($value));
    }

    public function toJson(): string
    {
        try {
            return json_encode($this->data, JSON_THROW_ON_ERROR | JSON_PRESERVE_ZERO_FRACTION);
        } catch (JsonException $e) {
            throw InvalidWebhookPayload::encodingFailed($e->getMessage());
        }
    }
}
