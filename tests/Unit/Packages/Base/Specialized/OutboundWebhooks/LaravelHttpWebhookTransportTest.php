<?php

declare(strict_types=1);

namespace Tests\Unit\Packages\Base\Specialized\OutboundWebhooks;

use Base\Specialized\OutboundWebhooks\Infrastructure\Adapters\LaravelHttpWebhookTransport;
use Base\Specialized\OutboundWebhooks\Public\Exceptions\WebhookDispatchFailed;
use Base\Specialized\OutboundWebhooks\Public\ValueObjects\WebhookEndpoint;
use Base\Specialized\OutboundWebhooks\Public\ValueObjects\WebhookHeaders;
use Base\Specialized\OutboundWebhooks\Public\ValueObjects\WebhookMessage;
use Base\Specialized\OutboundWebhooks\Public\ValueObjects\WebhookPayload;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

final class LaravelHttpWebhookTransportTest extends TestCase
{
    public function test_send_issues_post_request(): void
    {
        Http::fake([
            'example.com/*' => Http::response('ok', 200),
        ]);

        $message = new WebhookMessage(
            new WebhookEndpoint('https://example.com/hook'),
            new WebhookPayload(['test' => 123]),
            new WebhookHeaders(['Authorization' => 'Bearer token'])
        );

        $transport = new LaravelHttpWebhookTransport(1, 1);
        $transport->send($message);

        Http::assertSent(function (Request $request) {
            return $request->url() === 'https://example.com/hook' &&
                   $request->method() === 'POST' &&
                   $request['test'] === 123 &&
                   $request->hasHeader('authorization') &&
                   $request->header('authorization')[0] === 'Bearer token';
        });
    }

    public function test_send_throws_on_3xx(): void
    {
        Http::fake([
            'example.com/*' => Http::response('moved', 301),
        ]);

        $message = new WebhookMessage(
            new WebhookEndpoint('https://example.com/hook'),
            new WebhookPayload(['test' => 123])
        );

        $transport = new LaravelHttpWebhookTransport(1, 1);

        $this->expectException(WebhookDispatchFailed::class);
        $this->expectExceptionMessage('Webhook dispatch to example.com failed with HTTP status 301.');

        $transport->send($message);
    }

    public function test_send_throws_on_4xx(): void
    {
        Http::fake([
            'example.com/*' => Http::response('forbidden', 403),
        ]);

        $message = new WebhookMessage(
            new WebhookEndpoint('https://example.com/hook'),
            new WebhookPayload(['test' => 123])
        );

        $transport = new LaravelHttpWebhookTransport(1, 1);

        $this->expectException(WebhookDispatchFailed::class);
        $this->expectExceptionMessage('Webhook dispatch to example.com failed with HTTP status 403.');

        $transport->send($message);
    }
}
