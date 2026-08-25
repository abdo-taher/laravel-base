<?php

declare(strict_types=1);

namespace Tests\Unit\Packages\Base\Platform\Notifications;

use Base\Platform\Notifications\Application\DefaultNotificationDispatcher;
use Base\Platform\Notifications\Application\InMemoryChannelRegistry;
use Base\Platform\Notifications\Public\Contracts\NotificationChannel;
use Base\Platform\Notifications\Public\Exceptions\NotificationDispatchFailed;
use Base\Platform\Notifications\Public\Exceptions\UnknownNotificationChannel;
use Base\Platform\Notifications\Public\ValueObjects\ChannelAddress;
use Base\Platform\Notifications\Public\ValueObjects\ChannelName;
use Base\Platform\Notifications\Public\ValueObjects\NotificationMessage;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

final class DispatcherTest extends TestCase
{
    public function test_registry_registers_and_resolves_channel(): void
    {
        $registry = new InMemoryChannelRegistry;

        $channel = $this->createMock(NotificationChannel::class);
        $channel->method('name')->willReturn(new ChannelName('email'));

        $registry->register($channel);

        $resolved = $registry->get(new ChannelName('email'));
        $this->assertSame($channel, $resolved);
    }

    public function test_registry_rejects_duplicate_channel(): void
    {
        $registry = new InMemoryChannelRegistry;

        $channel1 = $this->createMock(NotificationChannel::class);
        $channel1->method('name')->willReturn(new ChannelName('email'));

        $channel2 = $this->createMock(NotificationChannel::class);
        $channel2->method('name')->willReturn(new ChannelName('email'));

        $registry->register($channel1);

        $this->expectException(InvalidArgumentException::class);
        $registry->register($channel2);
    }

    public function test_registry_throws_on_unknown_channel(): void
    {
        $registry = new InMemoryChannelRegistry;

        $this->expectException(UnknownNotificationChannel::class);
        $registry->get(new ChannelName('sms'));
    }

    public function test_dispatcher_resolves_and_delegates_to_channel(): void
    {
        $registry = new InMemoryChannelRegistry;

        $channel = $this->createMock(NotificationChannel::class);
        $channelName = new ChannelName('sms');
        $channel->method('name')->willReturn($channelName);

        $message = new NotificationMessage('Body');

        $channel->expects($this->once())
            ->method('send')
            ->with($this->equalTo($message), $this->equalTo('+1234567890'));

        $registry->register($channel);

        $dispatcher = new DefaultNotificationDispatcher($registry);

        $target = new ChannelAddress($channelName, '+1234567890');

        $dispatcher->dispatch($message, $target);
    }

    public function test_dispatcher_wraps_raw_throwable_from_adapter(): void
    {
        $registry = new InMemoryChannelRegistry;

        $channel = $this->createMock(NotificationChannel::class);
        $channelName = new ChannelName('sms');
        $channel->method('name')->willReturn($channelName);

        $channel->method('send')->willThrowException(new \RuntimeException('Raw backend failure'));

        $registry->register($channel);

        $dispatcher = new DefaultNotificationDispatcher($registry);

        $this->expectException(NotificationDispatchFailed::class);
        $dispatcher->dispatch(new NotificationMessage('Body'), new ChannelAddress($channelName, '+1234567890'));
    }
}
