<?php

declare(strict_types=1);

namespace Tests\Unit\Packages\Base\Platform\Notifications;

use Base\Platform\Notifications\Public\Exceptions\InvalidChannelAddress;
use Base\Platform\Notifications\Public\ValueObjects\ChannelAddress;
use Base\Platform\Notifications\Public\ValueObjects\ChannelName;
use Base\Platform\Notifications\Public\ValueObjects\NotificationMessage;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

final class ValueObjectsTest extends TestCase
{
    public function test_channel_name_accepts_valid_string(): void
    {
        $name = new ChannelName('email');
        $this->assertSame('email', $name->value);
    }

    public function test_channel_name_rejects_empty_string(): void
    {
        $this->expectException(InvalidArgumentException::class);
        new ChannelName('   ');
    }

    public function test_channel_address_accepts_valid_string(): void
    {
        $channel = new ChannelName('sms');
        $address = new ChannelAddress($channel, '+1234567890');

        $this->assertSame($channel, $address->channel);
        $this->assertSame('+1234567890', $address->address);
    }

    public function test_channel_address_rejects_empty_string(): void
    {
        $channel = new ChannelName('sms');

        $this->expectException(InvalidChannelAddress::class);
        new ChannelAddress($channel, '   ');
    }

    public function test_channel_address_rejects_control_characters(): void
    {
        $channel = new ChannelName('sms');

        $invalidAddresses = [
            "test\0@example.com",
            "test\n@example.com",
            "test\r@example.com",
            "test\t@example.com",
        ];

        foreach ($invalidAddresses as $address) {
            try {
                new ChannelAddress($channel, $address);
                $this->fail('Expected InvalidChannelAddress exception for address with control characters.');
            } catch (InvalidChannelAddress $e) {
                $this->assertStringContainsString('control characters', $e->getMessage());
            }
        }
    }

    public function test_notification_message_accepts_valid_data(): void
    {
        $message = new NotificationMessage('Hello world', 'Greeting');

        $this->assertSame('Hello world', $message->body);
        $this->assertSame('Greeting', $message->subject);
    }

    public function test_notification_message_rejects_empty_body(): void
    {
        $this->expectException(InvalidArgumentException::class);
        new NotificationMessage('   ');
    }

    public function test_notification_message_rejects_empty_subject(): void
    {
        $this->expectException(InvalidArgumentException::class);
        new NotificationMessage('Valid body', '   ');
    }
}
