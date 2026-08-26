<?php

declare(strict_types=1);

namespace Base\Platform\Devices\Public\ValueObjects;

use Base\Foundation\Identity\Public\ValueObjects\PrincipalId;

final readonly class DeviceRegistration
{
    public function __construct(
        public DeviceId $deviceId,
        public PrincipalId $owner,
        public DevicePlatform $platform,
        public ?PushToken $pushToken = null,
        public ?string $appVersion = null,
        public bool $active = true,
    ) {}
}
