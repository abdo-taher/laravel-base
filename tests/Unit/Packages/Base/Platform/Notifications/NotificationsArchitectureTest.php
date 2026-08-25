<?php

declare(strict_types=1);

namespace Tests\Unit\Packages\Base\Platform\Notifications;

use Base\Platform\Notifications\Public\Contracts\ChannelRegistry;
use Base\Platform\Notifications\Public\Contracts\NotificationChannel;
use Base\Platform\Notifications\Public\Contracts\NotificationDispatcher;
use Base\Platform\Notifications\Public\Exceptions\InvalidChannelAddress;
use Base\Platform\Notifications\Public\Exceptions\NotificationDispatchFailed;
use Base\Platform\Notifications\Public\Exceptions\NotificationException;
use Base\Platform\Notifications\Public\Exceptions\UnknownNotificationChannel;
use Base\Platform\Notifications\Public\ValueObjects\ChannelAddress;
use Base\Platform\Notifications\Public\ValueObjects\ChannelName;
use Base\Platform\Notifications\Public\ValueObjects\NotificationMessage;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

final class NotificationsArchitectureTest extends TestCase
{
    public function test_public_contracts_have_no_framework_dependencies(): void
    {
        $contracts = [
            ChannelRegistry::class,
            NotificationChannel::class,
            NotificationDispatcher::class,
            InvalidChannelAddress::class,
            NotificationDispatchFailed::class,
            NotificationException::class,
            UnknownNotificationChannel::class,
            ChannelAddress::class,
            ChannelName::class,
            NotificationMessage::class,
        ];

        foreach ($contracts as $contract) {
            $reflection = new ReflectionClass($contract);
            $fileName = $reflection->getFileName();

            if ($fileName === false) {
                $this->fail("Could not find file for contract {$contract}");
            }

            $content = file_get_contents($fileName);
            if ($content === false) {
                $this->fail("Could not read file for contract {$contract}");
            }

            $this->assertStringNotContainsString(
                'Illuminate\\',
                $content,
                "Contract {$contract} must not depend on Laravel framework."
            );

            $this->assertStringNotContainsString(
                'Mailable',
                $content,
                "Contract {$contract} must not depend on Laravel Mailables."
            );

            $this->assertStringNotContainsString(
                'Symfony\\Component\\Mime\\',
                $content,
                "Contract {$contract} must not depend on Symfony Mailer types."
            );
        }
    }
}
