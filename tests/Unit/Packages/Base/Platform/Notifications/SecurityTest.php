<?php

declare(strict_types=1);

namespace Tests\Unit\Packages\Base\Platform\Notifications;

use Base\Platform\Notifications\Public\Exceptions\InvalidChannelAddress;
use Base\Platform\Notifications\Public\Exceptions\NotificationDispatchFailed;
use Base\Platform\Notifications\Public\Exceptions\UnknownNotificationChannel;
use Base\Platform\Notifications\Public\ValueObjects\ChannelName;
use Exception;
use PHPUnit\Framework\TestCase;

final class SecurityTest extends TestCase
{
    public function test_invalid_channel_address_does_not_leak_address(): void
    {
        $channel = new ChannelName('email');
        $exception = InvalidChannelAddress::genericValidationFailed($channel, 'empty string');

        $this->assertStringNotContainsString('test@example.com', $exception->getMessage());

        $channelException = InvalidChannelAddress::channelSpecificValidationFailed($channel, 'invalid format');
        $this->assertStringNotContainsString('test@example.com', $channelException->getMessage());
    }

    public function test_unknown_channel_does_not_leak_address(): void
    {
        $channel = new ChannelName('secret-channel');
        $exception = UnknownNotificationChannel::forName($channel);

        $this->assertStringNotContainsString('test@example.com', $exception->getMessage());
    }

    public function test_dispatch_failed_does_not_leak_credentials(): void
    {
        $channel = new ChannelName('sms');
        $previous = new Exception('Provider error: Invalid API key secret_12345');

        $exception = NotificationDispatchFailed::backendRejected($channel, $previous);

        $this->assertStringNotContainsString('secret_12345', $exception->getMessage());
    }
}
