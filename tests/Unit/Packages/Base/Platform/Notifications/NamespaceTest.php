<?php

declare(strict_types=1);

namespace Tests\Unit\Packages\Base\Platform\Notifications;

use Base\Platform\Notifications\NotificationsServiceProvider;
use PHPUnit\Framework\TestCase;

final class NamespaceTest extends TestCase
{
    public function test_notifications_service_provider_class_exists(): void
    {
        $this->assertTrue(class_exists(NotificationsServiceProvider::class));
    }
}
