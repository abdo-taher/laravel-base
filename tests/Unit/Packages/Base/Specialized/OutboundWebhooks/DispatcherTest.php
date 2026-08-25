<?php

declare(strict_types=1);

namespace Tests\Unit\Packages\Base\Specialized\OutboundWebhooks;

use Base\Specialized\OutboundWebhooks\Application\Contracts\WebhookTransport;
use Base\Specialized\OutboundWebhooks\Application\DefaultWebhookDispatcher;
use Base\Specialized\OutboundWebhooks\Public\Exceptions\WebhookDispatchFailed;
use Base\Specialized\OutboundWebhooks\Public\ValueObjects\WebhookEndpoint;
use Base\Specialized\OutboundWebhooks\Public\ValueObjects\WebhookMessage;
use Base\Specialized\OutboundWebhooks\Public\ValueObjects\WebhookPayload;
use PHPUnit\Framework\TestCase;

final class DispatcherTest extends TestCase
{
    public function test_dispatch_delegates_to_transport(): void
    {
        $message = new WebhookMessage(
            new WebhookEndpoint('https://example.com'),
            new WebhookPayload(['a' => 'b'])
        );

        $transport = $this->createMock(WebhookTransport::class);
        $transport->expects($this->once())
            ->method('send')
            ->with($message);

        $dispatcher = new DefaultWebhookDispatcher($transport);
        $dispatcher->dispatch($message);
    }

    public function test_dispatch_wraps_transport_exceptions_safely(): void
    {
        $message = new WebhookMessage(
            new WebhookEndpoint('https://example.com/hook?api_key=SUPER_SECRET_QUERY'),
            new WebhookPayload(['payload_secret' => 'SECRET_PAYLOAD'])
        );

        $transport = $this->createMock(WebhookTransport::class);
        $transport->method('send')
            ->willThrowException(new \RuntimeException('Connection failed for URL: https://example.com/hook?api_key=SUPER_SECRET_QUERY with payload {"payload_secret":"SECRET_PAYLOAD"}'));

        $dispatcher = new DefaultWebhookDispatcher($transport);

        try {
            $dispatcher->dispatch($message);
            $this->fail('Expected WebhookDispatchFailed to be thrown.');
        } catch (WebhookDispatchFailed $e) {
            $this->assertSame('Webhook transport to example.com failed due to an underlying network or execution error.', $e->getMessage());
            $this->assertStringNotContainsString('SUPER_SECRET_QUERY', $e->getMessage());
            $this->assertStringNotContainsString('SECRET_PAYLOAD', $e->getMessage());
        }
    }

    public function test_dispatch_passes_through_dispatch_failed(): void
    {
        $message = new WebhookMessage(
            new WebhookEndpoint('https://example.com'),
            new WebhookPayload(['a' => 'b'])
        );

        $transport = $this->createMock(WebhookTransport::class);
        $transport->method('send')
            ->willThrowException(WebhookDispatchFailed::fromStatus(500, 'example.com'));

        $dispatcher = new DefaultWebhookDispatcher($transport);

        $this->expectException(WebhookDispatchFailed::class);
        $this->expectExceptionMessage('Webhook dispatch to example.com failed with HTTP status 500.');

        $dispatcher->dispatch($message);
    }
}
