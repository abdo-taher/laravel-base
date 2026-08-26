<?php

declare(strict_types=1);

namespace Tests\Feature\Packages\Base\Platform\Devices;

use Base\Foundation\Identity\Public\ValueObjects\PrincipalId;
use Base\Platform\Devices\Application\DatabaseDeviceRegistry;
use Base\Platform\Devices\DevicesServiceProvider;
use Base\Platform\Devices\Public\Exceptions\DeviceNotFound;
use Base\Platform\Devices\Public\ValueObjects\DeviceId;
use Base\Platform\Devices\Public\ValueObjects\DevicePlatform;
use Base\Platform\Devices\Public\ValueObjects\PushToken;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class DatabaseDeviceRegistryTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->app->register(DevicesServiceProvider::class);
        $this->artisan('migrate');
    }

    public function test_first_registration(): void
    {
        $registry = $this->app->make(DatabaseDeviceRegistry::class);
        $owner = new PrincipalId('user_1');
        $device = new DeviceId('dev_1');
        $platform = new DevicePlatform('ios');

        $registry->register($owner, $device, $platform);

        $devices = $registry->devicesForPrincipal($owner);
        $this->assertCount(1, $devices);
        $this->assertSame('dev_1', $devices[0]->deviceId->value);
    }

    public function test_same_device_updates(): void
    {
        $registry = $this->app->make(DatabaseDeviceRegistry::class);
        $owner = new PrincipalId('user_1');
        $device = new DeviceId('dev_1');
        $platform = new DevicePlatform('ios');

        $registry->register($owner, $device, $platform, new PushToken('old'));
        $registry->register($owner, $device, $platform, new PushToken('new'));

        $devices = $registry->devicesForPrincipal($owner);
        $this->assertCount(1, $devices);
        $this->assertSame('new', $devices[0]->pushToken?->value);
    }

    public function test_principal_handover(): void
    {
        $registry = $this->app->make(DatabaseDeviceRegistry::class);
        $owner1 = new PrincipalId('user_1');
        $owner2 = new PrincipalId('user_2');
        $device = new DeviceId('dev_1');
        $platform = new DevicePlatform('ios');

        $registry->register($owner1, $device, $platform);
        $registry->register($owner2, $device, $platform);

        $this->assertCount(0, $registry->devicesForPrincipal($owner1));
        $this->assertCount(1, $registry->devicesForPrincipal($owner2));
    }

    public function test_push_token_moves(): void
    {
        $registry = $this->app->make(DatabaseDeviceRegistry::class);
        $owner = new PrincipalId('user_1');
        $device1 = new DeviceId('dev_1');
        $device2 = new DeviceId('dev_2');
        $platform = new DevicePlatform('ios');
        $token = new PushToken('shared_token');

        $registry->register($owner, $device1, $platform, $token);
        $registry->register($owner, $device2, $platform, $token);

        $d1 = DB::table('devices')->where('device_id', 'dev_1')->first();
        $this->assertNull($d1?->push_token);

        $d2 = DB::table('devices')->where('device_id', 'dev_2')->first();
        $this->assertSame('shared_token', $d2?->push_token);
    }

    public function test_unregister(): void
    {
        $registry = $this->app->make(DatabaseDeviceRegistry::class);
        $owner = new PrincipalId('user_1');
        $device = new DeviceId('dev_1');

        $registry->register($owner, $device, new DevicePlatform('ios'));
        $registry->unregister($device);

        $this->assertCount(0, $registry->devicesForPrincipal($owner));
    }

    public function test_touch(): void
    {
        $registry = $this->app->make(DatabaseDeviceRegistry::class);
        $owner = new PrincipalId('user_1');
        $device = new DeviceId('dev_1');

        $registry->register($owner, $device, new DevicePlatform('ios'));
        $registry->touch($device);
        $this->expectNotToPerformAssertions();
    }

    public function test_touch_unknown(): void
    {
        $registry = $this->app->make(DatabaseDeviceRegistry::class);

        $this->expectException(DeviceNotFound::class);
        $registry->touch(new DeviceId('unknown'));
    }
}
