<?php

declare(strict_types=1);

namespace Tests\Unit\Packages\Base\Specialized\OutboundWebhooks;

use Base\Specialized\OutboundWebhooks\Public\Exceptions\InvalidWebhookEndpoint;
use Base\Specialized\OutboundWebhooks\Public\Exceptions\InvalidWebhookPayload;
use Base\Specialized\OutboundWebhooks\Public\Exceptions\WebhookException;
use Base\Specialized\OutboundWebhooks\Public\ValueObjects\WebhookEndpoint;
use Base\Specialized\OutboundWebhooks\Public\ValueObjects\WebhookHeaders;
use Base\Specialized\OutboundWebhooks\Public\ValueObjects\WebhookPayload;
use PHPUnit\Framework\TestCase;

final class ValueObjectsTest extends TestCase
{
    public function test_valid_https_endpoint(): void
    {
        $endpoint = new WebhookEndpoint('https://example.com/hook?query=1');
        $this->assertSame('https://example.com/hook?query=1', $endpoint->url);
        $this->assertSame('example.com', $endpoint->host);
    }

    public function test_valid_http_endpoint(): void
    {
        $endpoint = new WebhookEndpoint('http://example.com/hook');
        $this->assertSame('http://example.com/hook', $endpoint->url);
    }

    public function test_invalid_url(): void
    {
        $this->expectException(InvalidWebhookEndpoint::class);
        $this->expectExceptionMessage('valid URL');
        new WebhookEndpoint('not-a-url');
    }

    public function test_unsupported_scheme(): void
    {
        $this->expectException(InvalidWebhookEndpoint::class);
        $this->expectExceptionMessage('Unsupported webhook endpoint scheme: ftp');
        new WebhookEndpoint('ftp://example.com/hook');
    }

    public function test_user_info_rejected(): void
    {
        $this->expectException(InvalidWebhookEndpoint::class);
        $this->expectExceptionMessage('embedded credentials');
        new WebhookEndpoint('https://user:pass@example.com/hook');
    }

    public function test_fragment_rejected(): void
    {
        $this->expectException(InvalidWebhookEndpoint::class);
        $this->expectExceptionMessage('fragments');
        new WebhookEndpoint('https://example.com/hook#frag');
    }

    public function test_payload_accepts_valid_json_structure(): void
    {
        $data = [
            'string' => 'value',
            'int' => 42,
            'float' => 3.14,
            'bool' => true,
            'null' => null,
            'list' => [1, 2, 3],
            'nested' => ['a' => 'b'],
        ];

        $payload = new WebhookPayload($data);
        $this->assertSame($data, $payload->data);
        $this->assertJson($payload->toJson());
    }

    public function test_payload_rejects_object(): void
    {
        $this->expectException(InvalidWebhookPayload::class);
        $this->expectExceptionMessage('invalid type: stdClass');
        new WebhookPayload(['obj' => new \stdClass]);
    }

    public function test_payload_rejects_nan(): void
    {
        $this->expectException(InvalidWebhookPayload::class);
        $this->expectExceptionMessage('invalid float');
        new WebhookPayload(['nan' => \NAN]);
    }

    public function test_payload_rejects_inf(): void
    {
        $this->expectException(InvalidWebhookPayload::class);
        $this->expectExceptionMessage('invalid float');
        new WebhookPayload(['inf' => \INF]);
    }

    public function test_headers_accepts_valid(): void
    {
        $headers = new WebhookHeaders([
            'Authorization' => 'Bearer token',
            'X-Custom' => 'value',
        ]);

        $this->assertArrayHasKey('authorization', $headers->headers);
        $this->assertSame('Bearer token', $headers->headers['authorization']);
    }

    public function test_headers_rejects_cr(): void
    {
        $this->expectException(WebhookException::class);
        $this->expectExceptionMessage('cannot contain CR, LF, or null');
        new WebhookHeaders(['X-Bad' => "value\r"]);
    }

    public function test_headers_rejects_duplicate_case_variants(): void
    {
        $this->expectException(WebhookException::class);
        $this->expectExceptionMessage('Duplicate header detected');
        new WebhookHeaders([
            'Authorization' => 'token1',
            'authorization' => 'token2',
        ]);
    }

    public function test_headers_rejects_forbidden(): void
    {
        $this->expectException(WebhookException::class);
        $this->expectExceptionMessage('Forbidden transport-control header');
        new WebhookHeaders(['Host' => 'example.com']);
    }
}
