<?php

declare(strict_types=1);

namespace Tests\Unit\Packages\Base\Platform\Devices;

use Base\Platform\Devices\Public\ValueObjects\DeviceId;
use Base\Platform\Devices\Public\ValueObjects\DevicePlatform;
use Base\Platform\Devices\Public\ValueObjects\PushToken;
use PHPUnit\Framework\TestCase;

class DeviceValueObjectsTest extends TestCase
{
    public function test_device_id(): void
    {
        $id = new DeviceId('dev_123');
        $this->assertSame('dev_123', $id->value);
    }

    public function test_device_platform(): void
    {
        $platform = new DevicePlatform(' iOS ');
        $this->assertSame('ios', $platform->value);
    }

    public function test_push_token(): void
    {
        $token = new PushToken('fcm_token_123');
        $this->assertSame('fcm_token_123', $token->value);
    }
}
