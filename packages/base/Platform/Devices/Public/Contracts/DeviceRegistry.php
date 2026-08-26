<?php

declare(strict_types=1);

namespace Base\Platform\Devices\Public\Contracts;

use Base\Foundation\Identity\Public\ValueObjects\PrincipalId;
use Base\Platform\Devices\Public\ValueObjects\DeviceId;
use Base\Platform\Devices\Public\ValueObjects\DevicePlatform;
use Base\Platform\Devices\Public\ValueObjects\DeviceRegistration;
use Base\Platform\Devices\Public\ValueObjects\PushToken;

interface DeviceRegistry
{
    public function register(
        PrincipalId $owner,
        DeviceId $device,
        DevicePlatform $platform,
        ?PushToken $token = null,
        ?string $appVersion = null
    ): void;

    public function unregister(DeviceId $device): void;

    public function touch(DeviceId $device): void;

    /**
     * @return array<DeviceRegistration>
     */
    public function devicesForPrincipal(PrincipalId $owner): array;
}
